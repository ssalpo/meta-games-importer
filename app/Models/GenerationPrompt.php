<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class GenerationPrompt extends Model
{
    protected $fillable = [
        'name',
        'system_prompt',
        'user_prompt_template',
        'description_template',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
