<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MetaGame extends Model
{
    protected $fillable = [
        'product_id',
        'external_id',
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
    ];

    protected function casts(): array
    {
        return [
            'is_addon' => 'boolean',
            'price' => 'decimal:2',
            'price_discount' => 'decimal:2',
            'discount_ended' => 'boolean',
            'rating' => 'decimal:2',
            'is_files_downloaded' => 'boolean',
            'source_created_at' => 'datetime',
            'source_updated_at' => 'datetime',
            'raw_payload' => 'array',
            'last_imported_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function effectivePrice(): ?string
    {
        if ($this->price_discount !== null && ! $this->discount_ended) {
            return $this->price_discount;
        }

        return $this->price;
    }

    public function productTitle(): string
    {
        if ($this->is_addon && filled($this->parent_title)) {
            return trim($this->parent_title.' DLC '.$this->title);
        }

        return $this->full_title;
    }

    public function imageSquareUrl(): ?string
    {
        if (! $this->image_square) {
            return null;
        }

        return rtrim((string) config('services.meta_games.image_base_url'), '/')
            .'/'
            .ltrim($this->image_square, '/');
    }
}
