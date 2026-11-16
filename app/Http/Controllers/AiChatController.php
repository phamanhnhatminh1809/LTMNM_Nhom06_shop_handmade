<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    /**
     * Tiếp nhận câu hỏi của khách hàng và trả về gợi ý sản phẩm từ Gemini AI.
     */
    public function ask(Request $request, GeminiChatService $geminiChatService): JsonResponse
    {
        $validated = $request->validate([
            'message'        => 'required|string|max:500',
            'history'        => 'nullable|array|max:10',
            'history.*.role' => 'required_with:history|string|in:user,model',
            'history.*.text' => 'required_with:history|string|max:1000',
        ], [
            'message.required' => 'Vui lòng nhập nội dung câu hỏi.',
            'message.max'      => 'Câu hỏi tối đa 500 ký tự.',
        ]);

        $result = $geminiChatService->ask(
            $validated['message'],
            $validated['history'] ?? []
        );

        $statusCode = $result['success'] ? 200 : 503;

        return response()->json([
            'success'     => $result['success'],
            'is_in_scope' => $result['is_in_scope'],
            'reply'       => $result['reply'],
            'products'    => $result['products'],
        ], $statusCode);
    }
}
