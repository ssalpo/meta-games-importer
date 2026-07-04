<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GenerationPrompt;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class DeepSeekProductCopyService
{
    public function generate(GenerationPrompt $prompt, string $productTitle, ?string $instructions): array
    {
        $apiKey = (string) config('services.deepseek.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('Не задан DEEPSEEK_API_KEY.');
        }

        $response = Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->timeout(60)
            ->retry(2, 1000)
            ->post(rtrim((string) config('services.deepseek.base_url'), '/').'/chat/completions', [
                'model' => (string) config('services.deepseek.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt->system_prompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->userPrompt($prompt, $productTitle, $instructions),
                    ],
                ],
                'response_format' => [
                    'type' => 'json_object',
                ],
                'temperature' => 0.4,
                'max_tokens' => 4000,
                'stream' => false,
            ]);

        if (! $response->successful()) {
            $message = data_get($response->json(), 'error.message')
                ?: data_get($response->json(), 'message')
                ?: $response->body();

            throw new RuntimeException('DeepSeek вернул ошибку '.$response->status().': '.str($message)->limit(300));
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('DeepSeek вернул пустой ответ.');
        }

        $generated = $this->decodeJsonContent($content);

        if (! is_array($generated)) {
            throw new RuntimeException('DeepSeek вернул некорректный JSON.');
        }

        return [
            'title_ru' => (string) str(trim((string) ($generated['title_ru'] ?? '')))->limit(Product::TITLE_MAX_LENGTH, ''),
            'description_ru' => trim((string) ($generated['description_ru'] ?? '')),
            'title_en' => (string) str(trim((string) ($generated['title_en'] ?? '')))->limit(Product::TITLE_MAX_LENGTH, ''),
            'description_en' => trim((string) ($generated['description_en'] ?? '')),
        ];
    }

    private function decodeJsonContent(string $content): ?array
    {
        $generated = json_decode($content, true);

        if (is_array($generated)) {
            return $generated;
        }

        $normalized = trim($content);
        $normalized = preg_replace('/^```(?:json)?\s*/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s*```$/', '', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        $generated = json_decode($normalized, true);

        if (is_array($generated)) {
            return $generated;
        }

        $start = strpos($normalized, '{');
        $end = strrpos($normalized, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $generated = json_decode(substr($normalized, $start, $end - $start + 1), true);

        return is_array($generated) ? $generated : null;
    }

    private function userPrompt(GenerationPrompt $prompt, string $productTitle, ?string $instructions): string
    {
        return strtr($prompt->user_prompt_template, [
            '{product_title}' => $productTitle,
            '{game_title}' => $productTitle,
            '{instructions}' => $instructions ?: 'Не указаны.',
            '{description_template}' => $prompt->description_template ?: 'Не задан.',
        ]);
    }
}
