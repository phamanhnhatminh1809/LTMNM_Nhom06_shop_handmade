<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiChatService
{
    private const CATALOG_CACHE_KEY = 'ai_chat_catalog_context';
    private const CATALOG_CACHE_TTL_SECONDS = 300;
    private const MAX_RECOMMENDED_PRODUCTS = 4;

    /**
     * Gửi câu hỏi của khách hàng tới Gemini Flash kèm ngữ cảnh dữ liệu hệ thống.
     *
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array{
     *     success: bool,
     *     is_in_scope: bool,
     *     reply: string,
     *     products: array<int, array<string, mixed>>,
     *     model: ?string
     * }
     */
    public function ask(string $userMessage, array $history = []): array
    {
        $aiConfig = $this->resolveAiConfig();
        $apiKey = $aiConfig['api_key'];
        $refusalMessage = $aiConfig['refusal_message'];

        if ($apiKey === '') {
            return [
                'success'     => false,
                'is_in_scope' => false,
                'reply'       => 'Trợ lý AI hiện chưa được cấu hình khóa API (GEMINI_API_KEY trong file .env). Vui lòng kiểm tra tài liệu docs/AI_Instruction/HUONG_DAN_CAU_HINH_AI_GEMINI.md.',
                'products'    => [],
                'model'       => null,
            ];
        }

        $catalogContext = $this->getCatalogContext();
        $systemInstruction = $this->buildSystemInstruction($catalogContext, $refusalMessage);
        $contents = $this->buildContents($userMessage, $history);

        $models = $this->getCandidateModels($aiConfig);
        $lastError = null;

        foreach ($models as $model) {
            $result = $this->callGeminiModel($model, $apiKey, $systemInstruction, $contents, $aiConfig);

            if ($result['ok']) {
                return $this->formatParsedResponse($result['data'], $refusalMessage, $model);
            }

            $lastError = $result['error'];

            // Nếu key không hợp lệ hoặc bị từ chối quyền thì không cần thử model dự phòng
            if (!empty($result['fatal'])) {
                break;
            }
        }

        Log::warning('GeminiChatService không thể phản hồi câu hỏi.', [
            'error'  => $lastError,
            'models' => $models,
        ]);

        return [
            'success'     => false,
            'is_in_scope' => false,
            'reply'       => 'Hiện tại trợ lý AI đang tạm bận hoặc kết nối tới dịch vụ AI bị gián đoạn. Bạn vui lòng thử lại sau ít phút nhé!',
            'products'    => [],
            'model'       => null,
        ];
    }

    /**
     * Đọc cấu hình AI và tự động đồng bộ từ .env nếu cache config (web container) chưa được làm mới.
     *
     * @return array{
     *     api_key: string,
     *     model: string,
     *     fallback_models: array<int, string>,
     *     base_url: string,
     *     timeout: int,
     *     refusal_message: string
     * }
     */
    private function resolveAiConfig(): array
    {
        $config = (array) config('shop.ai', []);
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $model = trim((string) ($config['model'] ?? 'gemini-3.5-flash'));
        $fallbackModels = array_values(array_filter(array_map(
            'trim',
            (array) ($config['fallback_models'] ?? [])
        )));
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $timeout = max(5, (int) ($config['timeout'] ?? 20));
        $refusalMessage = trim((string) ($config['refusal_message'] ?? ''));

        $envPath = base_path('.env');
        $shouldCheckDotenv = app()->configurationIsCached() || PHP_SAPI !== 'cli';

        if ($shouldCheckDotenv && is_readable($envPath)) {
            $parsedEnv = $this->parseDotenvFile($envPath);

            if (array_key_exists('GEMINI_API_KEY', $parsedEnv)) {
                $apiKey = trim((string) $parsedEnv['GEMINI_API_KEY']);
            }
            if (!empty($parsedEnv['GEMINI_MODEL'])) {
                $model = trim((string) $parsedEnv['GEMINI_MODEL']);
            }
            if (array_key_exists('GEMINI_FALLBACK_MODELS', $parsedEnv) && trim((string) $parsedEnv['GEMINI_FALLBACK_MODELS']) !== '') {
                $fallbackModels = array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) $parsedEnv['GEMINI_FALLBACK_MODELS'])
                )));
            }
            if (!empty($parsedEnv['GEMINI_BASE_URL'])) {
                $baseUrl = rtrim((string) $parsedEnv['GEMINI_BASE_URL'], '/');
            }
            if (!empty($parsedEnv['GEMINI_TIMEOUT'])) {
                $timeout = max(5, (int) $parsedEnv['GEMINI_TIMEOUT']);
            }
            if (!empty($parsedEnv['SHOP_AI_REFUSAL_MESSAGE'])) {
                $refusalMessage = trim((string) $parsedEnv['SHOP_AI_REFUSAL_MESSAGE']);
            }
        }

        if ($refusalMessage === '') {
            $refusalMessage = 'Xin lỗi bạn, mình là trợ lý AI của Shop Handmade nên chỉ có thể tư vấn và gợi ý các sản phẩm thủ công hoặc thông tin có sẵn trong hệ thống của cửa hàng. Bạn hãy thử hỏi mình về các món đồ handmade như thú bông len, móc khóa, túi xách, nến thơm hoặc quà tặng nhé!';
        }

        return [
            'api_key'         => $apiKey,
            'model'           => $model !== '' ? $model : 'gemini-3.5-flash',
            'fallback_models' => $fallbackModels,
            'base_url'        => $baseUrl !== '' ? $baseUrl : 'https://generativelanguage.googleapis.com/v1beta',
            'timeout'         => $timeout,
            'refusal_message' => $refusalMessage,
        ];
    }

    /**
     * Phân tích nhanh các biến môi trường trong file .env.
     *
     * @return array<string, string|null>
     */
    private function parseDotenvFile(string $envPath): array
    {
        try {
            $contents = file_get_contents($envPath);
            if ($contents === false || $contents === '') {
                return [];
            }

            return \Dotenv\Dotenv::parse($contents);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Lấy danh sách model ưu tiên: model chính + các model Flash dự phòng.
     *
     * @param  array{model: string, fallback_models: array<int, string>}  $aiConfig
     * @return array<int, string>
     */
    private function getCandidateModels(array $aiConfig): array
    {
        $primary = (string) ($aiConfig['model'] ?? 'gemini-3.5-flash');
        $fallbacks = (array) ($aiConfig['fallback_models'] ?? []);

        return array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));
    }

    /**
     * Gọi REST API generateContent của Google Gemini.
     *
     * @param  array<int, array<string, mixed>>  $contents
     * @param  array{base_url: string, timeout: int}  $aiConfig
     * @return array{ok: bool, data?: array<string, mixed>, error?: string, fatal?: bool}
     */
    private function callGeminiModel(
        string $model,
        string $apiKey,
        string $systemInstruction,
        array $contents,
        array $aiConfig = []
    ): array {
        $baseUrl = rtrim((string) ($aiConfig['base_url'] ?? config('shop.ai.base_url', 'https://generativelanguage.googleapis.com/v1beta')), '/');
        $timeout = max(5, (int) ($aiConfig['timeout'] ?? config('shop.ai.timeout', 20)));
        $endpoint = "{$baseUrl}/models/{$model}:generateContent";

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature'      => 0.2,
                'maxOutputTokens'  => 1500,
                'responseMimeType' => 'application/json',
                'responseSchema'   => [
                    'type'       => 'OBJECT',
                    'properties' => [
                        'is_in_scope' => [
                            'type'        => 'BOOLEAN',
                            'description' => 'true nếu câu hỏi nằm trong phạm vi sản phẩm hoặc dữ liệu có sẵn của Shop Handmade; false nếu nằm ngoài phạm vi.',
                        ],
                        'reply' => [
                            'type'        => 'STRING',
                            'description' => 'Câu trả lời tiếng Việt ngắn gọn, thân thiện dựa trên dữ liệu hệ thống, hoặc câu từ chối mặc định nếu is_in_scope=false.',
                        ],
                        'recommended_product_ids' => [
                            'type'        => 'ARRAY',
                            'description' => 'Tối đa 4 ID sản phẩm thực tế phù hợp nhất với nhu cầu của khách. Để mảng rỗng nếu không gợi ý sản phẩm hoặc is_in_scope=false.',
                            'items'       => [
                                'type' => 'INTEGER',
                            ],
                        ],
                    ],
                    'required' => ['is_in_scope', 'reply', 'recommended_product_ids'],
                ],
            ],
        ];

        try {
            $response = Http::timeout($timeout)
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ])
                ->acceptJson()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($endpoint, $payload);

            if (!$response->successful()) {
                $status = $response->status();
                $bodyError = (string) ($response->json('error.message') ?? $response->body());
                $isRetryable = in_array($status, [404, 429, 500, 502, 503, 504], true);

                return [
                    'ok'    => false,
                    'error' => "HTTP {$status} ({$model}): {$bodyError}",
                    'fatal' => !$isRetryable,
                ];
            }

            $rawText = (string) ($response->json('candidates.0.content.parts.0.text') ?? '');
            $parsed = $this->decodeJsonResponse($rawText);

            if (!is_array($parsed) || !array_key_exists('is_in_scope', $parsed)) {
                return [
                    'ok'    => false,
                    'error' => "Phản hồi JSON từ {$model} không đúng định dạng: " . Str::limit($rawText, 200),
                    'fatal' => false,
                ];
            }

            return [
                'ok'   => true,
                'data' => $parsed,
            ];
        } catch (\Throwable $e) {
            return [
                'ok'    => false,
                'error' => "Exception ({$model}): " . $e->getMessage(),
                'fatal' => false,
            ];
        }
    }

    /**
     * Giải mã JSON do Gemini trả về (xử lý thêm trường hợp có bọc code fence).
     *
     * @return array<string, mixed>|null
     */
    private function decodeJsonResponse(string $rawText): ?array
    {
        $clean = trim($rawText);
        if ($clean === '') {
            return null;
        }

        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $clean) ?? $clean;
            $clean = trim($clean);
        }

        $decoded = json_decode($clean, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    /**
     * Áp dụng kiểm soát bắt buộc: ngoài phạm vi -> câu trả lời từ chối mặc định; trong phạm vi -> map sản phẩm thật từ DB.
     *
     * @param  array<string, mixed>  $parsed
     * @return array{
     *     success: bool,
     *     is_in_scope: bool,
     *     reply: string,
     *     products: array<int, array<string, mixed>>,
     *     model: string
     * }
     */
    private function formatParsedResponse(array $parsed, string $refusalMessage, string $model): array
    {
        $isInScope = filter_var($parsed['is_in_scope'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$isInScope) {
            return [
                'success'     => true,
                'is_in_scope' => false,
                'reply'       => $refusalMessage,
                'products'    => [],
                'model'       => $model,
            ];
        }

        $reply = trim((string) ($parsed['reply'] ?? ''));
        if ($reply === '') {
            $reply = 'Mình đã tìm thấy một số thông tin phù hợp trong cửa hàng dành cho bạn:';
        }

        $productIds = is_array($parsed['recommended_product_ids'] ?? null)
            ? $parsed['recommended_product_ids']
            : [];

        $products = $this->resolveRecommendedProducts($productIds);

        return [
            'success'     => true,
            'is_in_scope' => true,
            'reply'       => $reply,
            'products'    => $products,
            'model'       => $model,
        ];
    }

    /**
     * Đối chiếu danh sách ID được AI gợi ý với bảng products thực tế trong MySQL.
     *
     * @param  array<int, mixed>  $productIds
     * @return array<int, array<string, mixed>>
     */
    private function resolveRecommendedProducts(array $productIds): array
    {
        $cleanIds = [];
        foreach ($productIds as $id) {
            if (is_numeric($id) && (int) $id > 0) {
                $cleanIds[] = (int) $id;
            }
        }
        $cleanIds = array_values(array_slice(array_unique($cleanIds), 0, self::MAX_RECOMMENDED_PRODUCTS));

        if (empty($cleanIds)) {
            return [];
        }

        $productsById = Product::with('category:id,name,slug')
            ->whereIn('id', $cleanIds)
            ->get()
            ->keyBy('id');

        $results = [];
        foreach ($cleanIds as $id) {
            $product = $productsById->get($id);
            if (!$product) {
                continue;
            }

            $rawImage = trim((string) ($product->image ?? ''));
            $imageUrl = $rawImage !== ''
                ? (str_starts_with($rawImage, 'http') ? $rawImage : '/' . ltrim($rawImage, '/'))
                : '/assets/images/placeholder.svg';

            $results[] = [
                'id'              => $product->id,
                'code'            => $product->code,
                'name'            => $product->name,
                'category_name'   => $product->category?->name ?? 'Handmade',
                'price'           => (float) $product->price,
                'price_formatted' => number_format((float) $product->price, 0, ',', '.') . 'đ',
                'in_stock'        => (int) $product->stock_quantity > 0,
                'stock_quantity'  => (int) $product->stock_quantity,
                'rating'          => number_format((float) $product->rating, 1),
                'sold'            => (int) ($product->sold ?? 0),
                'image_url'       => $imageUrl,
                'url'             => route('products.show', $product->id, false),
            ];
        }

        return $results;
    }

    /**
     * Xây dựng lịch sử hội thoại theo cấu trúc `contents` của Gemini API.
     *
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array<int, array<string, mixed>>
     */
    private function buildContents(string $userMessage, array $history): array
    {
        $contents = [];

        foreach (array_slice($history, -8) as $turn) {
            $role = ($turn['role'] ?? '') === 'model' ? 'model' : 'user';
            $text = trim((string) ($turn['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $text]],
            ];
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => trim($userMessage)]],
        ];

        return $contents;
    }

    /**
     * Tạo System Prompt kèm toàn bộ dữ liệu sản phẩm và chính sách thực tế của hệ thống.
     */
    private function buildSystemInstruction(string $catalogContext, string $refusalMessage): string
    {
        return <<<PROMPT
Bạn là trợ lý tư vấn bán hàng chính thức của cửa hàng đồ thủ công "Shop Handmade".

NHIỆM VỤ:
1. Gợi ý, tìm kiếm và giải đáp thắc mắc về các sản phẩm thủ công hiện có trong DỮ LIỆU HỆ THỐNG bên dưới (theo tên, loại, mức giá, tình trạng còn hàng, lượt bán, đánh giá, mô tả, cách bảo quản).
2. Giải đáp các thông tin về cửa hàng có sẵn trong DỮ LIỆU HỆ THỐNG (phí vận chuyển, miễn phí ship, liên hệ, địa chỉ, giờ mở cửa, thanh toán, đổi trả).

QUY TẮC KIỂM SOÁT PHẠM VI (BẮT BUỘC TUÂN THỦ):
- CHỈ sử dụng thông tin có trong DỮ LIỆU HỆ THỐNG bên dưới. TUYỆT ĐỐI KHÔNG tự bịa sản phẩm, mã SKU, giá tiền, số lượng tồn kho, hay chính sách không tồn tại.
- NẾU câu hỏi của khách hàng nằm NGOÀI PHẠM VI các sản phẩm hoặc dữ liệu không có trong hệ thống (ví dụ:
  + Hỏi kiến thức chung bên ngoài: toán học, lập trình, thời tiết, tin tức, lịch sử, nấu ăn, y tế, dịch thuật, viết bài...
  + Hỏi mua hoặc hỏi thông tin về sản phẩm không có trong danh mục/sản phẩm của Shop Handmade như điện thoại, laptop, xe cộ, quần áo công nghiệp, mỹ phẩm...
  + Hỏi dữ liệu nội bộ/riêng tư không được cung cấp như tra cứu một mã đơn hàng cụ thể của cá nhân, doanh thu cửa hàng...):
  BẮT BUỘC trả về:
  - "is_in_scope": false
  - "recommended_product_ids": []
  - "reply": "{$refusalMessage}"
- NẾU câu hỏi thuộc phạm vi sản phẩm/dữ liệu của Shop Handmade:
  - Đặt "is_in_scope": true.
  - Trả lời ngắn gọn, lịch sự, hữu ích bằng tiếng Việt trong "reply" (nêu rõ tên sản phẩm, giá VNĐ và tình trạng còn/hết hàng nếu có).
  - Đưa tối đa 4 ID sản phẩm thực tế phù hợp nhất vào "recommended_product_ids" (ưu tiên sản phẩm còn hàng `in_stock: true` trừ khi khách hỏi đích danh sản phẩm đã hết hàng). Nếu khách chỉ hỏi chính sách/liên hệ mà không cần gợi ý sản phẩm cụ thể, để "recommended_product_ids": [].

DỮ LIỆU HỆ THỐNG (JSON):
{$catalogContext}
PROMPT;
    }

    /**
     * Lấy và cache dữ liệu danh mục, sản phẩm và thông tin cửa hàng hiện có trong hệ thống.
     */
    private function getCatalogContext(): string
    {
        return Cache::remember(self::CATALOG_CACHE_KEY, self::CATALOG_CACHE_TTL_SECONDS, function () {
            $categories = Category::select('id', 'name', 'slug')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $cat) => [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ])
                ->values()
                ->all();

            $products = Product::with('category:id,name,slug')
                ->select([
                    'id',
                    'code',
                    'category_id',
                    'name',
                    'price',
                    'old_price',
                    'stock_quantity',
                    'rating',
                    'sold',
                    'is_featured',
                    'description',
                    'instruction_preserve',
                ])
                ->orderByDesc('is_featured')
                ->orderByDesc('sold')
                ->orderByDesc('id')
                ->get()
                ->map(function (Product $product) {
                    return [
                        'id'                   => $product->id,
                        'code'                 => $product->code,
                        'name'                 => $product->name,
                        'category'             => $product->category?->name ?? 'Handmade',
                        'price'                => (int) $product->price,
                        'old_price'            => $product->old_price ? (int) $product->old_price : null,
                        'stock_quantity'       => (int) $product->stock_quantity,
                        'in_stock'             => (int) $product->stock_quantity > 0,
                        'rating'               => (float) $product->rating,
                        'sold'                 => (int) ($product->sold ?? 0),
                        'is_featured'          => (bool) $product->is_featured,
                        'description'          => Str::limit(trim(strip_tags((string) $product->description)), 160),
                        'instruction_preserve' => Str::limit(trim(strip_tags((string) $product->instruction_preserve)), 120),
                    ];
                })
                ->values()
                ->all();

            $storeInfo = [
                'name'           => 'Shop Handmade - Handmade House',
                'free_shipping'  => 'Miễn phí vận chuyển cho đơn từ ' . number_format((float) config('shop.shipping.free_threshold', 300000), 0, ',', '.') . 'đ',
                'shipping_fee'   => number_format((float) config('shop.shipping.default_fee', 30000), 0, ',', '.') . 'đ',
                'return_policy'  => 'Chính sách đổi trả trong 7 ngày',
                'payment_methods'=> ['Thanh toán khi nhận hàng (COD)', 'Thanh toán chuyển khoản mã QR (PayOS)'],
                'contact'        => [
                    'phone'   => config('shop.contact.phone'),
                    'email'   => config('shop.contact.email'),
                    'address' => config('shop.contact.address'),
                    'hours'   => config('shop.contact.hours'),
                ],
            ];

            return (string) json_encode([
                'store_info' => $storeInfo,
                'categories' => $categories,
                'products'   => $products,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });
    }
}
