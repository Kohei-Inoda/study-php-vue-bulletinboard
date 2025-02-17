<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function generateComment($postContent)
    {
        $apiKey = env('GEMINI_API_KEY'); // `.env` からAPIキーを取得
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        // **リクエスト送信**
        $response = Http::post($url, [
            "contents" => [
                [
                    "parts" => [
                        ["text" => "以下の投稿に対して適切なコメントを一つ生成してください。\n\n投稿内容:\n{$postContent}"]
                    ]
                ]
                    ],
                    "generationConfig" => [
                        "temperature" => 0.7,
                        "maxOutputTokens" => 100
                    ]
        ]);

        // **APIのレスポンスを取得**
        $result = $response->json();

        // **レスポンスをログ出力**
        Log::info("Gemini API Full Response:", $result);

        // 🔹 エラー処理
        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            Log::error("Gemini API Error: " . json_encode($result));
            return "いい投稿ですね！"; // デフォルトのコメント
        }

        // 🔹 生成されたコメントを取得
        return $result['candidates'][0]['content']['parts'][0]['text'];

        return $generatedComment;
    }
}