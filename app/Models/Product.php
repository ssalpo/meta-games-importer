<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const IMAGE_RU = 'image_ru';

    public const IMAGE_EN = 'image_en';

    protected $fillable = [
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
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE_RU)->singleFile();
        $this->addMediaCollection(self::IMAGE_EN)->singleFile();
    }

    public function imageRu(): ?Media
    {
        return $this->getFirstMedia(self::IMAGE_RU);
    }

    public function imageEn(): ?Media
    {
        return $this->getFirstMedia(self::IMAGE_EN);
    }
}
