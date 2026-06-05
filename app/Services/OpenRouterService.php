<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class OpenRouterService
{
    protected string $apiKey;
    protected string $defaultModel;
    protected string $fallbackModel;

    public function __construct()
    {
        $this->apiKey = Setting::getValue('openrouter_api_key') ?: config('services.openrouter.api_key', '');
        $this->defaultModel = Setting::getValue('openrouter_default_model') ?: config('services.openrouter.default_model', 'anthropic/claude-3.5-sonnet');
        $this->fallbackModel = Setting::getValue('openrouter_fallback_model') ?: config('services.openrouter.fallback_model', 'openai/gpt-4o');
    }

    /**
     * Generate research draft using OpenRouter.
     * Tries default model first, if it fails, falls back to fallback model.
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
            Log::warning("OpenRouter primary model ({$model}) failed: " . $e->getMessage());
            
            // Attempt fallback if we weren't already using it
            if ($model !== $this->fallbackModel) {
                Log::info("Attempting OpenRouter fallback model: {$this->fallbackModel}");
                try {
                    return $this->callApi($this->fallbackModel, $systemPrompt, $userPrompt);
                } catch (\Exception $e2) {
                    Log::error("OpenRouter fallback model ({$this->fallbackModel}) failed: " . $e2->getMessage());
                }
            }
        }

        return null;
    }

    protected function callApi(string $model, string $systemPrompt, string $userPrompt): array
    {
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => 'Avenir Research',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("OpenRouter API error: " . $response->body());
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
