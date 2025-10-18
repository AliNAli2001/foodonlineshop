<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasRoles;

    protected $table = 'admins';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'phone',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the super admin record associated with this admin.
     */
    public function superAdmin(): HasOne
    {
        return $this->hasOne(SuperAdmin::class, 'admin_id');
    }

    /**
     * Get all orders created by this admin.
     */
    public function createdOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by_admin_id');
    }

    /**
     * Check if this admin is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->superAdmin()->exists();
    }

    /**
     * Get the full name of the admin.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the password attribute for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}

