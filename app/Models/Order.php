<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'client_id',
        'created_by_admin_id',
        'client_name',
        'client_phone_number',
        'order_date',
        'total_amount',
        'status',
        'order_source',
        'delivery_method',
        'shipping_notes',
        'latitude',
        'longitude',
        'address_details',
        'general_notes',
        'admin_order_client_notes',
        'delivery_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:3',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUSES = [
        'pending' => 'معلق',
        'confirmed' => 'مقبول',
        'shipped' => 'قيد الشحن',
        'delivered' => 'قيد التوصيل',
        'done' => 'مكتمل',
        'canceled' => 'ملغى',
        'returned' => 'مرجع',
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

    /**
     * Prepare a copiable message based on the order's status.
     *
     * @param string|null $status Optional status to use (defaults to current status).
     * @return string The formatted message.
     */
    public function prepareCopiableMessage(?string $status = null): string
    {
        $status = $status ?? $this->status;

        // Common order details
        $orderDetails = "رقم الطلب: {$this->id}\n";
        $orderDetails .= "اسم العميل: " . ($this->client_name ?? 'غير محدد') . "\n";
        $orderDetails .= "رقم الهاتف: " . ($this->client_phone_number ?? 'غير محدد') . "\n";
        $orderDetails .= "-------------\n";
        $orderDetails .= "المصدر: " . (self::SOURCES[$this->order_source] ?? 'غير محدد') . "\n";
        $orderDetails .= "طريقة التوصيل: " . (self::DELIVERY_METHODS[$this->delivery_method] ?? 'غير محدد') . "\n";
        $orderDetails .= "العنوان: " . ($this->address_details ?? 'غير محدد') . "\n";
        $orderDetails .= "الإحداثيات: (" . ($this->latitude ?? 'غير محدد') . ", " . ($this->longitude ?? 'غير محدد') . ")\n";
        $orderDetails .= "الملاحظات: " . ($this->general_notes ?? 'لا توجد') . "\n";
        $orderDetails .= "-------------\n";
        // Item details
        $itemsList = "العناصر:\n";
        foreach ($this->items as $item) {
            $itemsList .= "- " . ($item->product->name ?? 'منتج غير معروف') . " (كمية: {$item->quantity}, سعر الوحدة: {$item->unit_price})\n";
        }
        $orderDetails .= $itemsList;
        $orderDetails .= "الإجمالي: {$this->total_amount}\n";

        // Delivery info (if applicable)
        $deliveryInfo = "";
        if ($this->delivery) {
            $deliveryInfo = "-------------\nمعلومات التوصيل:\n";
            $deliveryInfo .= "اسم عامل التوصيل: " . ($this->delivery->full_name ?? 'غير محدد') . "\n";
            $deliveryInfo .= "رقم هاتف عامل التوصيل: " . ($this->delivery->phone ?? 'غير محدد') . "\n";
            // Add more delivery model fields as needed (assuming Delivery model has 'name', etc.)
            $deliveryInfo .= "ملاحظات الشحن: " . ($this->shipping_notes ?? 'لا توجد') . "\n-------------\n";
        }

        // Status-specific message
        switch ($status) {
            case 'confirmed':
                $message = "تم قبول طلبك رقم {$this->id}\n";
                $message .= "تفاصيل الطلب:\n{$orderDetails}";
                break;
            case 'shipped':
                $message = "طلبك رقم {$this->id} قيد الشحن\n";
                $message .= "معلومات الشحن: " . ($this->shipping_notes ?? 'لا توجد') . "\n";
                $message .= "تفاصيل الطلب:\n{$orderDetails}";
                break;
            case 'delivered':
                $message = "طلبك رقم {$this->id} قيد التوصيل\n";
                $message .= $deliveryInfo;
                $message .= "تفاصيل الطلب:\n{$orderDetails}";
                break;
            case 'returned':
                $message = "طلبك رقم {$this->id} تم إرجاعه\n";
                $message .= "تفاصيل الطلب:\n{$orderDetails}";
                break;
            case 'canceled':
                $message = "طلبك رقم {$this->id} تم إلغاؤه\n";
                $message .= "تفاصيل الطلب:\n{$orderDetails}";
                break;
            // Add more cases for other statuses as needed (e.g., 'done', 'pending')
            default:
                $message = "حالة الطلب: " . (self::STATUSES[$status] ?? 'غير معروفة') . "\n";
                $message .= "تفاصيل الطلب:\n{$orderDetails}{$deliveryInfo}";
                break;
        }

        return $message;
    }
}