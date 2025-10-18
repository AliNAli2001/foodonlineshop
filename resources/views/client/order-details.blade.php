@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Order #{{ $order->id }}</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Order Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($order->status) }}</span></p>
                <p><strong>Order Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>Order Source:</strong> {{ ucfirst(str_replace('_', ' ', $order->order_source)) }}</p>
                <p><strong>Delivery Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</p>
                <p><strong>Address:</strong> {{ $order->address_details }}</p>
                @if ($order->latitude && $order->longitude)
                    <p><strong>Location:</strong> {{ $order->latitude }}, {{ $order->longitude }}</p>
                @endif
                @if ($order->shipping_notes)
                    <p><strong>Shipping Notes:</strong> {{ $order->shipping_notes }}</p>
                @endif
                @if ($order->general_notes)
                    <p><strong>Notes:</strong> {{ $order->general_notes }}</p>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5>Order Items</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->product->name_en }}</td>
                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($returnedItems->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5>Returned Items</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($returnedItems as $returnedItem)
                                <tr>
                                    <td>{{ $returnedItem->orderItem->product->name_en }}</td>
                                    <td>{{ $returnedItem->quantity }}</td>
                                    <td>{{ $returnedItem->reason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order Summary</h5>
                <p>Total: ${{ number_format($order->total_amount, 2) }}</p>
                <a href="{{ route('client.orders') }}" class="btn btn-secondary w-100">Back to Orders</a>
            </div>
        </div>
    </div>
</div>
@endsection

