@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Return Details</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.returns.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Return Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Order ID:</strong> <a href="{{ route('admin.orders.show', $return->order->id) }}">Order #{{ $return->order->id }}</a></p>
                    <p><strong>Product:</strong> {{ $return->orderItem->product->name_en }}</p>
                    <p><strong>Return Quantity:</strong> {{ $return->quantity }}</p>
                    <p><strong>Original Quantity:</strong> {{ $return->orderItem->quantity }}</p>
                    <p><strong>Unit Price:</strong> ${{ number_format($return->orderItem->unit_price, 2) }}</p>
                    <p><strong>Return Value:</strong> ${{ number_format($return->quantity * $return->orderItem->unit_price, 2) }}</p>
                    <p><strong>Reason:</strong> {{ $return->reason ?? 'N/A' }}</p>
                    <p><strong>Date:</strong> {{ $return->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>

        @if ($return->damagedGoods->count() > 0)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Damaged Goods from This Return</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Quantity</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($return->damagedGoods as $damaged)
                                    <tr>
                                        <td>{{ $damaged->quantity }}</td>
                                        <td>{{ $damaged->reason }}</td>
                                        <td>{{ $damaged->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

