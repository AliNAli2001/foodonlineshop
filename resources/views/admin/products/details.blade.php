@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $product->name_ar }} - تفاصيل المنتج</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">الرجوع</a>
        </div>
    </div>

    <div class="row">
        <!-- معلومات المنتج -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>معلومات المنتج</h5>
                </div>
                <div class="card-body">
                    <p><strong>الاسم (بالعربية):</strong> {{ $product->name_ar }}</p>
                    <p><strong>الاسم (بالإنجليزية):</strong> {{ $product->name_en }}</p>
                    <p><strong>السعر:</strong> ${{ number_format($product->price, 2) }}</p>
                    <p><strong>الحد الأقصى للطلب:</strong> {{ $product->max_order_item ?? 'غير محدود' }}</p>
                    <p><strong>مميز:</strong> 
                        <span class="badge {{ $product->featured ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->featured ? 'نعم' : 'لا' }}
                        </span>
                    </p>
                    <p><strong>التصنيفات:</strong> 
                        @foreach ($product->categories as $category)
                            <span class="badge bg-info">{{ $category->name_ar }}</span>
                        @endforeach
                    </p>
                </div>
            </div>
        </div>

        <!-- معلومات المخزون -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>معلومات المخزون</h5>
                </div>
                <div class="card-body">
                    <p><strong>الكمية الإجمالية:</strong> {{ $product->inventory->stock_quantity }}</p>
                    <p><strong>الكمية المحجوزة:</strong> {{ $product->inventory->reserved_quantity }}</p>
                    <p><strong>المتوفر:</strong> {{ $product->inventory->getAvailableStock() }}</p>
                    <p><strong>حد التنبيه الأدنى:</strong> {{ $product->inventory->minimum_alert_quantity }}</p>
                    <p><strong>الحالة:</strong> 
                        @if ($product->inventory->isBelowMinimum())
                            <span class="badge bg-warning">أقل من الحد الأدنى</span>
                        @else
                            <span class="badge bg-success">جيد</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- الإحصائيات -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>إحصائيات المبيعات</h5>
                </div>
                <div class="card-body">
                    <p><strong>إجمالي المبيعات:</strong> {{ $totalSold }} وحدة</p>
                    <p><strong>إجمالي الإيرادات:</strong> ${{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- آخر المبيعات -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>آخر المبيعات (آخر 10)</h5>
        </div>
        <div class="card-body">
            @if ($recentSellings->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي الفرعي</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSellings as $selling)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $selling->order->id) }}">
                                        طلب رقم #{{ $selling->order->id }}
                                    </a>
                                </td>
                                <td>{{ $selling->quantity }}</td>
                                <td>${{ number_format($selling->unit_price, 2) }}</td>
                                <td>${{ number_format($selling->getSubtotal(), 2) }}</td>
                                <td>{{ $selling->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">لا توجد مبيعات بعد</p>
            @endif
        </div>
    </div>

    <!-- آخر حركات المخزون -->
    <div class="card">
        <div class="card-header">
            <h5>آخر حركات المخزون (آخر 10)</h5>
        </div>
        <div class="card-body">
            @if ($recentInventory->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>تغيير الكمية</th>
                            <th>تغيير الحجز</th>
                            <th>السبب</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentInventory as $transaction)
                            <tr>
                                <td><span class="badge bg-info">{{ ucfirst($transaction->transaction_type) }}</span></td>
                                <td>{{ $transaction->available_change > 0 ? '+' : '' }}{{ $transaction->available_change }}</td>
                                <td>{{ $transaction->reserved_change > 0 ? '+' : '' }}{{ $transaction->reserved_change }}</td>
                                <td>{{ $transaction->reason }}</td>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">لا توجد حركات بعد</p>
            @endif
        </div>
    </div>
</div>
@endsection
