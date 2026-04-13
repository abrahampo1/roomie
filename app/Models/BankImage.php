<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'title',
    'alt_text',
    'category',
    'tags',
    'disk_path',
    'mime_type',
    'file_size',
    'width',
    'height',
])]
class BankImage extends Model
{
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (BankImage $image) {
            Storage::disk('public')->delete($image->disk_path);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->disk_path);
    }
}
