<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MetaGame;
use App\Models\Product;
use App\Services\CentralBankExchangeRateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class MetaGameController extends Controller
{
    public function __construct(
        private readonly CentralBankExchangeRateService $exchangeRateService,
    ) {}

    public function index(Request $request): View
    {
        $query = MetaGame::query()
            ->with(['products.account'])
            ->withCount('products')
            ->latest('source_updated_at')
            ->latest();

        if (filled($request->string('search')->toString())) {
            $search = $request->string('search')->toString();

            $query->where(function ($query) use ($search): void {
                $query
                    ->where('full_title', 'like', '%'.$search.'%')
                    ->orWhere('external_id', 'like', '%'.$search.'%')
                    ->orWhere('parent_title', 'like', '%'.$search.'%');
            });
        }

        if (in_array($request->string('type')->toString(), ['game', 'addon'], true)) {
            $query->where('is_addon', $request->string('type')->toString() === 'addon');
        }

        $metaGames = $query
            ->paginate(25)
            ->withQueryString();

        return view('meta-games.index', [
            'metaGames' => $metaGames,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function createProduct(MetaGame $metaGame): RedirectResponse
    {
        $imageWarning = false;

        try {
            $priceRub = $this->rublePrice($metaGame->effectivePrice());
        } catch (Throwable $exception) {
            Log::warning('Не удалось получить официальный курс USD для создания продукта.', [
                'meta_game_id' => $metaGame->id,
                'external_id' => $metaGame->external_id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('status', 'Продукт не создан: не удалось получить официальный курс доллара.');
        }

        $product = DB::transaction(function () use ($metaGame, $priceRub, &$imageWarning): Product {
            $product = Product::create([
                'account_id' => session('selected_account_id'),
                'meta_game_id' => $metaGame->id,
                'placement_category' => $metaGame->is_addon ? 'Дополнение' : 'Игра',
                'external_reference' => $metaGame->external_id,
                'price' => $priceRub,
                'title_ru' => $metaGame->productTitle(),
                'title_en' => $metaGame->productTitle(),
                'description_ru' => null,
                'description_en' => null,
                'instruction_ru' => null,
                'instruction_en' => null,
                'additional_info_ru' => null,
                'additional_info_en' => null,
            ]);

            if (! $metaGame->product_id) {
                $metaGame->update(['product_id' => $product->id]);
            }

            $imageWarning = ! $this->attachSquareImage($product, $metaGame);

            return $product;
        });

        return redirect()
            ->route('products.edit', $product)
            ->with('status', $imageWarning
                ? 'Продукт создан, но изображение не удалось скачать.'
                : 'Продукт создан из импортированной игры.');
    }

    private function rublePrice(?string $usdPrice): string
    {
        if ($usdPrice === null || (float) $usdPrice <= 0) {
            return '0.00';
        }

        return number_format(
            $this->exchangeRateService->usdToRub((float) $usdPrice),
            2,
            '.',
            '',
        );
    }

    private function attachSquareImage(Product $product, MetaGame $metaGame): bool
    {
        $url = $metaGame->imageSquareUrl();

        if (! $url) {
            return false;
        }

        try {
            $response = Http::timeout(30)
                ->retry(2, 500)
                ->get($url);

            if (! $response->successful() || $response->body() === '') {
                return false;
            }

            $fileName = basename(parse_url($url, PHP_URL_PATH) ?: 'image_square.webp');

            foreach ([Product::IMAGE_RU, Product::IMAGE_EN] as $collection) {
                $product
                    ->addMediaFromString($response->body())
                    ->usingFileName($fileName)
                    ->toMediaCollection($collection);
            }

            return true;
        } catch (Throwable $exception) {
            Log::warning('Не удалось скачать изображение Meta Games.', [
                'meta_game_id' => $metaGame->id,
                'url' => $url,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
