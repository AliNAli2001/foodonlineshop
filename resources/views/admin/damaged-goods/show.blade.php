@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Damaged Goods Details</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.damaged-goods.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Damaged Goods Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Product:</strong> {{ $damagedGoods->product->name_en }}</p>
            <p><strong>Quantity:</strong> {{ $damagedGoods->quantity }}</p>
            <p><strong>Source:</strong> <span class="badge bg-info">{{ ucfirst($damagedGoods->source) }}</span></p>
            <p><strong>Reason:</strong> {{ $damagedGoods->reason }}</p>
            @if ($damagedGoods->returnItem)
                <p><strong>Related Return:</strong> <a href="{{ route('admin.returns.show', $damagedGoods->returnItem->id) }}">Return #{{ $damagedGoods->returnItem->id }}</a></p>
            @endif
            @if ($damagedGoods->inventoryTransaction)
                <p><strong>Inventory Transaction:</strong>
                    <span class="badge bg-success">{{ $damagedGoods->inventoryTransaction->transaction_type }}</span>
                    (ID: {{ $damagedGoods->inventoryTransaction->id }})
                </p>
            @endif
            <p><strong>Date:</strong> {{ $damagedGoods->created_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</div>
@endsection

