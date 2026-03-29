<?php

namespace App\Http\Controllers;

use App\Services\AiProductChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function __construct(private readonly AiProductChatService $chatService) {}

    public function chat(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array'],
        ]);

        $result = $this->chatService->chat(
            $payload['message'],
            is_array($payload['history'] ?? null) ? $payload['history'] : []
        );

        return response()->json([
            'ok' => true,
            'answer' => $result['answer'],
            'products' => $result['products'],
            'filters' => $result['filters'],
            'suggested_questions' => $result['suggested_questions'] ?? [],
        ]);
    }
}
