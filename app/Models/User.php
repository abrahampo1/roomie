<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'api_token', 'api_token_created_at'])]
#[Hidden(['password', 'remember_token', 'api_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'api_token_created_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function brandSetting(): HasOne
    {
        return $this->hasOne(BrandSetting::class);
    }

    public function bankImages(): HasMany
    {
        return $this->hasMany(BankImage::class);
    }

    /**
     * Generate a fresh API token for this user, store its SHA-256 hash on
     * the model, and return the plain token so the caller can show it to the
     * user exactly once.
     */
    public function generateApiToken(): string
    {
        $plain = 'sk-roomie-'.bin2hex(random_bytes(32));

        $this->forceFill([
            'api_token' => hash('sha256', $plain),
            'api_token_created_at' => now(),
        ])->save();

        return $plain;
    }

    public function revokeApiToken(): void
    {
        $this->forceFill([
            'api_token' => null,
            'api_token_created_at' => null,
        ])->save();
    }

    /**
     * Look up a user by the plain token they presented in the Authorization
     * header. Returns null if no active token matches.
     */
    public static function findByApiToken(string $plain): ?self
    {
        if (trim($plain) === '') {
            return null;
        }

        return static::query()
            ->where('api_token', hash('sha256', $plain))
            ->first();
    }
}
