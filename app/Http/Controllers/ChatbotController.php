<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function publicConfig(): JsonResponse
    {
        $isProduction = (bool) config('services.midtrans.is_production', false);

        return response()->json([
            'midtrans' => [
                'client_key' => config('services.midtrans.client_key'),
                'snap_url' => $isProduction
                    ? 'https://app.midtrans.com/snap/snap.js'
                    : 'https://app.sandbox.midtrans.com/snap/snap.js',
            ],
            'payment_methods' => PaymentMethod::query()
                ->where('is_active', true)
                ->ordered()
                ->get(['id', 'code', 'name', 'description']),
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'system_prompt' => 'required|string',
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|string|in:user,assistant',
            'messages.*.content' => 'required|string',
        ]);

        $apiKey = config('services.groq.api_key');
        if (!$apiKey) {
            return response()->json([
                'message' => 'Chatbot belum dikonfigurasi di server.',
            ], 500);
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post(rtrim(config('services.groq.base_url'), '/') . '/chat/completions', [
                'model' => config('services.groq.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $validated['system_prompt'],
                    ],
                    ...$validated['messages'],
                ],
                'max_tokens' => 512,
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Gagal menghubungi layanan chatbot.',
            ], 502);
        }

        return response()->json([
            'reply' => data_get($response->json(), 'choices.0.message.content', 'Maaf, terjadi kesalahan. Coba lagi ya!'),
        ]);
    }
}
