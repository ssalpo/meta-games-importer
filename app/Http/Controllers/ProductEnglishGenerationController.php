<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GenerationPrompt;
use App\Models\Product;
use App\Services\DeepSeekProductCopyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class ProductEnglishGenerationController extends Controller
{
    public function __invoke(Request $request, DeepSeekProductCopyService $generator): JsonResponse
    {
        $data = $request->validate([
            'generation_prompt_id' => ['required', 'integer', 'exists:generation_prompts,id'],
            'product_title' => ['required_without:game_title', 'string', 'max:'.Product::TITLE_MAX_LENGTH],
            'game_title' => ['required_without:product_title', 'string', 'max:'.Product::TITLE_MAX_LENGTH],
            'instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $prompt = GenerationPrompt::query()
                ->where('is_active', true)
                ->findOrFail($data['generation_prompt_id']);

            $request->session()->put('selected_generation_prompt_id', $prompt->id);

            return response()->json(
                $generator->generate(
                    prompt: $prompt,
                    productTitle: $data['product_title'] ?? $data['game_title'],
                    instructions: $data['instructions'] ?? null,
                ),
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        }
    }
}
