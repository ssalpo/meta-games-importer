<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class MetaGamesImportService
{
    private const CHUNK_SIZE = 1000;

    public function import(?Command $command = null): int
    {
        $response = Http::acceptJson()
            ->timeout(120)
            ->retry(3, 1000)
            ->get((string) config('services.meta_games.api_url'));

        if (! $response->successful()) {
            throw new RuntimeException('Meta Games API вернул статус '.$response->status().'.');
        }

        $payload = $response->json();
        $items = Arr::get($payload, 'data');

        if (! is_array($items)) {
            throw new RuntimeException('Meta Games API вернул неожиданный формат ответа.');
        }

        $now = now();
        $total = 0;

        foreach (array_chunk($items, self::CHUNK_SIZE) as $chunk) {
            $rows = array_map(
                fn (array $item): array => $this->mapItem($item, $now),
                $chunk,
            );

            DB::table('meta_games')->upsert(
                $rows,
                ['external_id'],
                [
                    'source_id',
                    'title',
                    'parent_title',
                    'full_title',
                    'is_addon',
                    'price',
                    'price_discount',
                    'discount_ended',
                    'rating',
                    'image_landscape',
                    'image_square',
                    'is_files_downloaded',
                    'source_created_at',
                    'source_updated_at',
                    'raw_payload',
                    'last_imported_at',
                    'updated_at',
                ],
            );

            $total += count($rows);
            $command?->info('Импортировано записей: '.$total);
        }

        return $total;
    }

    private function mapItem(array $item, Carbon $now): array
    {
        $title = trim((string) ($item['title'] ?? ''));
        $parentTitle = filled($item['parent_title'] ?? null)
            ? trim((string) $item['parent_title'])
            : null;

        return [
            'external_id' => (string) $item['external_id'],
            'source_id' => $item['id'] ?? null,
            'title' => $title,
            'parent_title' => $parentTitle,
            'full_title' => $this->fullTitle($parentTitle, $title),
            'is_addon' => (bool) ($item['is_addon'] ?? false),
            'price' => $item['price'] ?? null,
            'price_discount' => $item['price_discount'] ?? null,
            'discount_ended' => (bool) ($item['discount_ended'] ?? false),
            'rating' => $item['rating'] ?? null,
            'image_landscape' => $item['image_landscape'] ?? null,
            'image_square' => $item['image_square'] ?? null,
            'is_files_downloaded' => (bool) ($item['is_files_downloaded'] ?? false),
            'source_created_at' => $this->parseDate($item['created_at'] ?? null),
            'source_updated_at' => $this->parseDate($item['updated_at'] ?? null),
            'raw_payload' => json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'last_imported_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function fullTitle(?string $parentTitle, string $title): string
    {
        return trim(implode(' ', array_filter(
            [$parentTitle, $title],
            static fn (?string $value): bool => filled($value),
        )));
    }

    private function parseDate(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return Carbon::parse($value)->toDateTimeString();
    }
}
