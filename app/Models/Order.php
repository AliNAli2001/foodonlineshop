<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'client_id',
        'created_by_admin_id',
        'total_amount',
        'status',
        'order_source',
        'delivery_method',
        'shipping_notes',
        'latitude',
        'longitude',
        'address_details',
        'general_notes',
        'delivery_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'canceled' => 'Canceled',
        'returned' => 'Returned',
    ];

    const SOURCES = [
        'inside_city' => 'Inside City',
        'outside_city' => 'Outside City',
    ];

    const DELIVERY_METHODS = [
        'delivery' => 'Delivery',
        'shipping' => 'Shipping',
        'hand_delivered' => 'Hand Delivered',
    ];

    /**
     * Get the client who placed this order.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the admin who created this order.
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Get the delivery person assigned to this order.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    /**
     * Get all items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Get all returned items for this order.
     */
    public function returnedItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'order_id');
    }
}

