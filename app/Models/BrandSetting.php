<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'brand_name',
    'logo_path',
    'primary_color',
    'secondary_color',
    'voice_description',
    'contact_email',
    'contact_phone',
    'contact_website',
    'social_links',
])]
class BrandSetting extends Model
{
    protected function casts(): array
    {
        return [
            'social_links' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }
}
