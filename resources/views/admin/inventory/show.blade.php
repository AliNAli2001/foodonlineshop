@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>المخزون: {{ $product->name_en }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.inventory.edit', $inventory->id) }}" class="btn btn-warning">تعديل</a>
            <a href="{{ route('admin.inventory.product',$product->id) }}" class="btn btn-secondary">العودة إلى مخزون المنتج</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>المخزون الحالي</h5>
                </div>
                <div class="card-body">
                    <p><strong>المنتج:</strong> {{ $product->name_en }}</p>
                    <p><strong>كمية المخزون:</strong> {{ $inventory->stock_quantity }}</p>
                    <p><strong>رقم الدفعة:</strong> {{ $inventory->batch_number }}</p>
                    <p><strong>تاريخ الانتهاء:</strong> {{ $inventory->expiry_date->format('Y-m-d') }}</p>
                    <p><strong>سعر التكلفة:</strong> {{ $inventory->cost_price }}</p>
                    <p><strong>الكمية المحجوزة:</strong> {{ $inventory->reserved_quantity }}</p>
                    <p><strong>المتاح:</strong> {{ $inventory->getAvailableStock() }}</p>
                    <p><strong>تنبيه الحد الأدنى:</strong> {{ $inventory->minimum_alert_quantity }}</p>
                    <p><strong>الحالة:</strong> 
                        @if ($inventory->isBelowMinimum())
                            <span class="badge bg-warning">أقل من الحد الأدنى</span>
                        @else
                            <span class="badge bg-success">سليم</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>التحركات الأخيرة</h5>
        </div>
        <div class="card-body">
            @if ($transactions->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>تغيير الكمية</th>
                            <th>تغيير المحجوز</th>
                            <th>السبب</th>
                            <th>سعر التكلفة</th>
                            <th>تاريخ الانتهاء</th>
                            <th>رقم الدفعة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td><span class="badge bg-info">{{ ucfirst($transaction->transaction_type) }}</span></td>
                                <td>{{ $transaction->quantity_change > 0 ? '+' : '' }}{{ $transaction->quantity_change }}</td>
                                <td>{{ $transaction->reserved_change > 0 ? '+' : '' }}{{ $transaction->reserved_change }}</td>
                                <td>{{ $transaction->reason }}</td>
                                <td>{{ $transaction->cost_price }}</td>
                                <td>{{ $transaction->expiry_date }}</td>
                                <td>{{ $transaction->batch_number }}</td>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $transactions->links() }}
            @else
                <p class="text-muted">لا توجد تحركات بعد</p>
            @endif
        </div>
    </div>
</div>
@endsection
