<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
class Client extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'clients';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'phone',
        'email_verified',
        'phone_verified',
        'address_details',
        'promo_consent',
        'language_preference',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'promo_consent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all orders for this client.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    /**
     * Get all verification codes for this client.
     */
    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class, 'client_id');
    }

    /**
     * Get the full name of the client.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if client is fully verified.
     */
    public function isFullyVerified(): bool
    {
        return $this->email_verified && $this->phone_verified;
    }

    /**
     * Get the password attribute for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}

