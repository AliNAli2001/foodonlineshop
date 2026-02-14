@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>المخزون: {{ $product->name_en }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.inventory.edit', $batch->id) }}" class="btn btn-warning">تعديل</a>
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
                    <p><strong>كمية المخزون:</strong> {{ $batch->available_quantity }}</p>
                    <p><strong>رقم الدفعة:</strong> {{ $batch->batch_number }}</p>
                    <p><strong>تاريخ الانتهاء:</strong> {{ $batch->expiry_date->format('Y-m-d') }}</p>
                    <p><strong>سعر التكلفة:</strong> {{ $batch->cost_price }}</p>
                    <p><strong>المتاح:</strong> {{ $batch->available_quantity }}</p>
                  
                
                    <p><strong>الحالة:</strong> 
                        @if ($batch->isExpired())
                            <span class="badge bg-warning">منتهي</span>
                        @else
                            <span class="badge bg-success">متاح</span>
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
            @if ($movements->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>تغيير الكمية</th>
                            <th>السبب</th>
                            <th>سعر التكلفة</th>
                            <th>تاريخ الانتهاء</th>
                            <th>رقم الدفعة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $transaction)
                            <tr>
                                <td><span class="badge bg-info">{{ \App\Models\InventoryMovement::TYPES[$transaction->transaction_type] }}</span></td>
                                <td>{{ $transaction->available_change > 0 ? '+' : '' }}{{ $transaction->available_change }}</td>
                                <td>{{ $transaction->reason }}</td>
                                <td>{{ $transaction->cost_price }}</td>
                                <td>{{ $transaction->expiry_date }}</td>
                                <td>{{ $transaction->batch_number }}</td>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $movements->links() }}
            @else
                <p class="text-muted">لا توجد تحركات بعد</p>
            @endif
        </div>
    </div>
</div>
@endsection
