@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>تفاصيل البضائع التالفة</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.damaged-goods.index') }}" class="btn btn-secondary">العودة</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>معلومات البضائع التالفة</h5>
        </div>
        <div class="card-body">
            <p><strong>المنتج:</strong> {{ $damagedGoods->product->name_ar }}</p>
            <p><strong>الكمية:</strong> {{ $damagedGoods->quantity }}</p>
            <p><strong>المصدر:</strong> <span class="badge bg-info">{{ ucfirst($damagedGoods->source) }}</span></p>
            <p><strong>السبب:</strong> {{ $damagedGoods->reason }}</p>
            @if ($damagedGoods->returnItem)
                <p><strong>مرتبط بالإرجاع:</strong> <a href="{{ route('admin.returns.show', $damagedGoods->returnItem->id) }}">إرجاع #{{ $damagedGoods->returnItem->id }}</a></p>
            @endif
            @if ($damagedGoods->inventoryTransaction)
                <p><strong>حركة المخزون:</strong>
                    <span class="badge bg-success">{{ $damagedGoods->inventoryTransaction->transaction_type }}</span>
                    (المعرف: {{ $damagedGoods->inventoryTransaction->id }})
                </p>
            @endif
            <p><strong>التاريخ:</strong> {{ $damagedGoods->created_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</div>
@endsection
