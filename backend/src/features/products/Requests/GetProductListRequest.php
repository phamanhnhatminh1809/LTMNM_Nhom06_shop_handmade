<?php

declare(strict_types=1);

namespace App\Features\Products\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class GetProductListRequest
 *
 * Form Request validate các query parameters cho API lấy danh sách sản phẩm.
 * Hỗ trợ: phân trang, lọc theo category/giá/tồn kho, tìm kiếm theo tên, sắp xếp.
 */
class GetProductListRequest extends FormRequest
{
    /**
     * API này là public, không cần xác thực.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validate cho các query parameters.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Phân trang
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:20'],

            // Bộ lọc
            'category_id'    => ['nullable', 'integer', 'exists:categories,id'],
            'search'         => ['nullable', 'string', 'max:100'],
            'price_min'      => ['nullable', 'numeric', 'min:0'],
            'price_max'      => ['nullable', 'numeric', 'min:0', 'gte:price_min'],
            'in_stock'       => ['nullable', 'boolean'],

            // Sắp xếp
            'sort_by'        => ['nullable', 'string', 'in:name,price,created_at,stock_quantity'],
            'sort_direction'  => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh (tiếng Việt).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.integer'      => 'Số lượng mỗi trang phải là số nguyên.',
            'per_page.min'          => 'Số lượng mỗi trang tối thiểu là 1.',
            'per_page.max'          => 'Số lượng mỗi trang tối đa là 20.',
            'category_id.integer'   => 'Mã danh mục phải là số nguyên.',
            'category_id.exists'    => 'Danh mục không tồn tại.',
            'search.max'            => 'Từ khóa tìm kiếm không được vượt quá 100 ký tự.',
            'price_min.numeric'     => 'Giá tối thiểu phải là số.',
            'price_min.min'         => 'Giá tối thiểu không được âm.',
            'price_max.numeric'     => 'Giá tối đa phải là số.',
            'price_max.min'         => 'Giá tối đa không được âm.',
            'price_max.gte'         => 'Giá tối đa phải lớn hơn hoặc bằng giá tối thiểu.',
            'in_stock.boolean'      => 'Giá trị lọc tồn kho phải là true hoặc false.',
            'sort_by.in'            => 'Trường sắp xếp phải là: name, price, created_at, hoặc stock_quantity.',
            'sort_direction.in'     => 'Chiều sắp xếp phải là: asc hoặc desc.',
        ];
    }

    /**
     * Xử lý khi validate thất bại — trả về JSON chuẩn thay vì redirect.
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'data'    => $validator->errors(),
                'meta'    => null,
            ], 422)
        );
    }
}
