<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Delivery extends Model
{
    use Notifiable;

    protected $table = 'delivery';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'status',
        'info',
        'phone_plus',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUSES = [
        'available' => 'Available',
        'busy' => 'Busy',
        'inactive' => 'Inactive',
    ];

    /**
     * Get all orders assigned to this delivery person.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_id');
    }

    /**
     * Get the full name of the delivery person.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}

