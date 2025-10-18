<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    protected $table = 'verification_codes';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'code',
        'type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    const TYPES = [
        'email' => 'Email',
        'phone' => 'Phone',
    ];

    /**
     * Get the client this verification code belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Check if the verification code is expired.
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if the verification code is valid.
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }
}

