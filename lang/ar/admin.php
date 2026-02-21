<?php

return [
    'inventory' => [
        'batch_created' => 'تمت إضافة دفعة مستودع جديدة بنجاح.',
        'batch_updated' => 'تم تحديث بيانات دفعة المستودع بنجاح.',
        'bulk_created' => 'تم إنشاء دفعات المستودع بنجاح.',
    ],
    'orders' => [
        'created' => 'تم إنشاء طلب يدوي بنجاح.',
        'confirmed' => 'تم تأكيد الطلب وإنقاص الكمية من المستودع بنجاح.',
        'rejected' => 'تم رفض الطلب وتحرير البضاعة المحجوزة.',
        'status_updated' => 'تم تحديث حالة الطلب إلى :status.',
        'delivery_assigned' => 'تم إسناد الطلب لعامل التوصيل بنجاح.',
        'delivery_method_updated' => 'تم تحديث طريقة التوصيل بنجاح.',
    ],
    'order_statuses' => [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'مؤكد',
        'rejected' => 'مرفوض',
        'shipped' => 'تم الشحن',
        'delivered' => 'قيد التوصيل',
        'done' => 'مكتمل',
        'canceled' => 'ملغي',
        'returned' => 'مرتجع',
    ],
];

