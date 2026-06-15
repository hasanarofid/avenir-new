<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class ChatGptService
{
    protected string $apiKey;
    protected string $defaultModel;

    public function __construct()
    {
        $this->apiKey = (string) (Setting::getValue('chatgpt_api_key') ?: config('services.chatgpt.api_key', ''));
        $this->defaultModel = (string) (Setting::getValue('chatgpt_default_model') ?: config('services.chatgpt.default_model', 'gpt-5.5'));
    }

    /**
     * Generate structured JSON using ChatGPT.
     * 
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param array $options
     * @return array|null
     */
    public function generateStructuredJson(string $systemPrompt, string $userPrompt, array $options = []): ?array
    {
        $model = $options['model'] ?? $this->defaultModel;

        try {
            return $this->callApi($model, $systemPrompt, $userPrompt);
        } catch (\Exception $e) {
            Log::error("ChatGPT model ({$model}) failed: " . $e->getMessage());
        }

        return null;
    }

    protected function callApi(string $model, string $systemPrompt, string $userPrompt): array
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("ChatGPT API error: " . $response->body());
        }

        $result = $response->json();
        
        $content = $result['choices'][0]['message']['content'] ?? '{}';
        
        return [
            'model_used' => $model,
            'structured_json' => json_decode($content, true) ?: [],
            'raw_response' => $result
        ];
    }
}
