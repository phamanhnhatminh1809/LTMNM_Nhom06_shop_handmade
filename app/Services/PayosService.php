<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayosService
{
    public function isConfigured(): bool
    {
        $cfg = config('shop.payos', []);

        return !empty($cfg['client_id'])
            && !empty($cfg['api_key'])
            && !empty($cfg['checksum_key']);
    }

    private function clientId(): string
    {
        return (string) config('shop.payos.client_id', '');
    }

    private function apiKey(): string
    {
        return (string) config('shop.payos.api_key', '');
    }

    private function checksumKey(): string
    {
        return (string) config('shop.payos.checksum_key', '');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('shop.payos.base_url', 'https://api-merchant.payos.vn'), '/');
    }

    private function httpClient(): PendingRequest
    {
        return Http::withHeaders([
            'x-client-id' => $this->clientId(),
            'x-api-key'   => $this->apiKey(),
            'Accept'      => 'application/json',
        ])->timeout(15);
    }

    /**
     * Sinh mã orderCode số nguyên dương duy nhất cho PayOS (an toàn trong MAX_SAFE_INTEGER).
     */
    public function generateUniqueOrderCode(Order $order): int
    {
        $basePrefix = now()->format('ymdHis');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $suffix = str_pad((string) (($order->id + random_int(10, 99)) % 1000), 3, '0', STR_PAD_LEFT);
            $candidate = (int) ($basePrefix . $suffix);

            if ($candidate > 0 && !Order::where('payment_reference', $candidate)->exists()) {
                return $candidate;
            }
        }

        return (int) (now()->format('ymdHis') . random_int(100, 999));
    }

    /**
     * Tạo nội dung chuyển khoản ngắn gọn <= 25 ký tự tương thích đa số ngân hàng VietQR.
     */
    public function buildPaymentDescription(Order $order): string
    {
        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', (string) $order->code);

        return substr('TT ' . $cleanCode, 0, 25);
    }

    /**
     * Tạo chữ ký HMAC-SHA256 chuẩn PayOS cho yêu cầu tạo link thanh toán.
     */
    public function createPaymentSignature(array $data): string
    {
        $raw = implode('&', [
            'amount=' . (int) ($data['amount'] ?? 0),
            'cancelUrl=' . (string) ($data['cancelUrl'] ?? ''),
            'description=' . (string) ($data['description'] ?? ''),
            'orderCode=' . (int) ($data['orderCode'] ?? 0),
            'returnUrl=' . (string) ($data['returnUrl'] ?? ''),
        ]);

        return hash_hmac('sha256', $raw, $this->checksumKey());
    }

    /**
     * Gọi PayOS API v2 tạo link & mã QR thanh toán cho đơn hàng.
     */
    public function createPaymentLink(Order $order, int $orderCode, string $returnUrl, string $cancelUrl): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('PayOS chưa được cấu hình đầy đủ trong .env.');
        }

        $amount = (int) round((float) $order->total_amount);
        if ($amount <= 0) {
            throw new RuntimeException('Số tiền thanh toán PayOS phải lớn hơn 0đ.');
        }

        $description = $this->buildPaymentDescription($order);
        $signData = [
            'orderCode'   => $orderCode,
            'amount'      => $amount,
            'description' => $description,
            'returnUrl'   => $returnUrl,
            'cancelUrl'   => $cancelUrl,
        ];

        $payload = [
            'orderCode'   => $orderCode,
            'amount'      => $amount,
            'description' => $description,
            'cancelUrl'   => $cancelUrl,
            'returnUrl'   => $returnUrl,
            'signature'   => $this->createPaymentSignature($signData),
        ];

        $response = $this->httpClient()->post($this->baseUrl() . '/v2/payment-requests', $payload);
        $body = $response->json() ?? [];

        if (!$response->successful() || ($body['code'] ?? '') !== '00' || empty($body['data']['checkoutUrl'])) {
            Log::error('PayOS createPaymentLink failed', [
                'order_id'  => $order->id,
                'orderCode' => $orderCode,
                'status'    => $response->status(),
                'body'      => $body,
            ]);

            $detail = $body['desc'] ?? 'Không thể kết nối cổng thanh toán PayOS.';
            throw new RuntimeException('Tạo giao dịch QR PayOS thất bại: ' . $detail);
        }

        return $body['data'];
    }

    /**
     * Truy vấn trực tiếp thông tin & trạng thái giao dịch từ server PayOS.
     */
    public function getPaymentRequestInfo(int|string $idOrOrderCode): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('PayOS chưa được cấu hình đầy đủ trong .env.');
        }

        $response = $this->httpClient()->get($this->baseUrl() . '/v2/payment-requests/' . $idOrOrderCode);
        $body = $response->json() ?? [];

        if (!$response->successful() || ($body['code'] ?? '') !== '00' || empty($body['data'])) {
            Log::warning('PayOS getPaymentRequestInfo failed', [
                'reference' => $idOrOrderCode,
                'status'    => $response->status(),
                'body'      => $body,
            ]);

            throw new RuntimeException('Không thể truy vấn trạng thái giao dịch từ PayOS.');
        }

        return $body['data'];
    }

    /**
     * Chủ động hủy link thanh toán trên PayOS (best-effort, không chặn luồng hủy đơn nội bộ).
     */
    public function cancelPaymentLink(int|string $idOrOrderCode, string $reason = 'Khách hàng hủy đơn hàng'): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->httpClient()->post(
                $this->baseUrl() . '/v2/payment-requests/' . $idOrOrderCode . '/cancel',
                ['cancellationReason' => $reason]
            );
            $body = $response->json() ?? [];

            if ($response->successful() && ($body['code'] ?? '') === '00') {
                return $body['data'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::warning('PayOS cancelPaymentLink skipped', [
                'reference' => $idOrOrderCode,
                'error'     => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Xác thực chữ ký Webhook PayOS theo chuẩn ksort + HMAC-SHA256 + hash_equals.
     */
    public function verifyWebhookSignature(array $payload): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $signature = $payload['signature'] ?? '';
        $data = $payload['data'] ?? null;

        if (!is_string($signature) || $signature === '' || !is_array($data)) {
            return false;
        }

        ksort($data);

        $pairs = [];
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $strValue = '';
            } elseif (is_bool($value)) {
                $strValue = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $strValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $strValue = (string) $value;
            }

            $pairs[] = $key . '=' . $strValue;
        }

        $expected = hash_hmac('sha256', implode('&', $pairs), $this->checksumKey());

        return hash_equals($expected, $signature);
    }
}
