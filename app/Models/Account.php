<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Account extends Model
{
    protected $fillable = [
        'name',
        'access_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function maskedAccessToken(): string
    {
        if (! $this->access_token) {
            return 'Not set';
        }

        $length = strlen($this->access_token);

        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($this->access_token, 0, 4)
            .str_repeat('*', $length - 8)
            .substr($this->access_token, -4);
    }
}
