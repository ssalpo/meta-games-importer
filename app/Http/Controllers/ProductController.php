<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GenerationPrompt;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('media')
            ->latest()
            ->paginate(10);

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product,
            ...$this->generationPromptViewData(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validatedData($request);

        $product = Product::create($this->productData($request));

        $this->syncMedia($request, $product);

        return redirect()
            ->route('products.index')
            ->with('status', 'Продукт создан.');
    }

    public function show(Product $product): View
    {
        $product->load('media');

        return view('products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load('media');

        return view('products.edit', [
            'product' => $product,
            ...$this->generationPromptViewData(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->validatedData($request);

        $product->update($this->productData($request));
        $this->syncMedia($request, $product);

        return redirect()
            ->route('products.index')
            ->with('status', 'Продукт обновлен.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Продукт удален.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'placement_category' => ['required', 'string', 'max:255'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'title_ru' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'description_ru' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'instruction_ru' => ['nullable', 'string'],
            'instruction_en' => ['nullable', 'string'],
            'additional_info_ru' => ['nullable', 'string'],
            'additional_info_en' => ['nullable', 'string'],
            'image_ru' => ['nullable', 'image', 'max:10240'],
            'image_en' => ['nullable', 'image', 'max:10240'],
            'remove_image_ru' => ['nullable', 'boolean'],
            'remove_image_en' => ['nullable', 'boolean'],
        ]);
    }

    private function syncMedia(Request $request, Product $product): void
    {
        foreach ([Product::IMAGE_RU, Product::IMAGE_EN] as $collection) {
            $removeField = 'remove_'.$collection;

            if ($request->boolean($removeField)) {
                $product->clearMediaCollection($collection);
            }

            if ($request->hasFile($collection)) {
                $product
                    ->addMediaFromRequest($collection)
                    ->toMediaCollection($collection);
            }
        }
    }

    private function productData(Request $request): array
    {
        return $request->only([
            'placement_category',
            'external_reference',
            'price',
            'title_ru',
            'title_en',
            'description_ru',
            'description_en',
            'instruction_ru',
            'instruction_en',
            'additional_info_ru',
            'additional_info_en',
        ]);
    }

    private function generationPromptViewData(): array
    {
        $prompts = GenerationPrompt::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedPromptId = session('selected_generation_prompt_id');

        if (! $prompts->contains('id', $selectedPromptId)) {
            $selectedPromptId = $prompts->first()?->id;
        }

        return [
            'generationPrompts' => $prompts,
            'selectedGenerationPromptId' => $selectedPromptId,
        ];
    }
}
