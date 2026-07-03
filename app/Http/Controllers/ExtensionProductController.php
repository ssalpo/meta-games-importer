<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ExtensionProductController extends Controller
{
    public function accounts(): JsonResponse
    {
        $accounts = Account::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Account $account): array => [
                'id' => $account->id,
                'name' => $account->name,
            ]);

        return response()->json([
            'data' => $accounts,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $accountId = $request->integer('account_id') ?: null;

        $products = Product::query()
            ->with('account')
            ->when($accountId, fn ($query) => $query->where('account_id', $accountId))
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'account_id' => $product->account_id,
                'title_ru' => $product->title_ru,
                'title_en' => $product->title_en,
                'price' => $product->price,
                'external_reference' => $product->external_reference,
                'ggsel_offer_id' => $product->ggsel_offer_id,
                'account_name' => $product->account?->name,
            ]);

        return response()->json([
            'data' => $products,
        ]);
    }

    public function publish(Product $product): JsonResponse
    {
        return response()->json([
            'message' => 'Публикация будет добавлена позже.',
            'product_id' => $product->id,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['account', 'media']);

        $imageRu = $product->imageRu();
        $imageEn = $product->imageEn();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'account_id' => $product->account_id,
                'title_ru' => $product->title_ru,
                'title_en' => $product->title_en,
                'description_ru' => $product->description_ru,
                'description_en' => $product->description_en,
                'instruction_ru' => $product->instruction_ru,
                'instruction_en' => $product->instruction_en,
                'price' => $product->price,
                'external_reference' => $product->external_reference,
                'ggsel_offer_id' => $product->ggsel_offer_id,
                'account_name' => $product->account?->name,
                'image_ru_data_uri' => $this->mediaDataUri($imageRu),
                'image_en_data_uri' => $this->mediaDataUri($imageEn) ?: $this->mediaDataUri($imageRu),
            ],
        ]);
    }

    public function updateGgselOfferId(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'ggsel_offer_id' => ['required', 'integer'],
        ]);

        $product->update([
            'ggsel_offer_id' => $data['ggsel_offer_id'],
        ]);

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'ggsel_offer_id' => $product->ggsel_offer_id,
        ]);
    }

    private function mediaDataUri(?Media $media): ?string
    {
        if (! $media || ! is_file($media->getPath())) {
            return null;
        }

        return sprintf(
            'data:%s;base64,%s',
            $media->mime_type ?: 'application/octet-stream',
            base64_encode((string) file_get_contents($media->getPath())),
        );
    }
}
