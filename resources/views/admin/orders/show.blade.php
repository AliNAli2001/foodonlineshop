@extends('layouts.admin')

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
                <p><strong>Client:</strong> {{ $order->client->first_name }} {{ $order->client->last_name }}</p>
                <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($order->status) }}</span></p>
                <p><strong>Order Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>Order Source:</strong> {{ ucfirst(str_replace('_', ' ', $order->order_source)) }}</p>
                <p><strong>Delivery Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</p>
                <p><strong>Address:</strong> {{ $order->address_details }}</p>
                @if ($order->latitude && $order->longitude)
                    <p><strong>Location:</strong> {{ $order->latitude }}, {{ $order->longitude }}</p>
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

        @if ($order->status === 'pending')
            <div class="card">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.confirm', $order->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success">Confirm Order</button>
                    </form>
                    <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Reject Order</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($order->status === 'confirmed' && !$order->delivery_id)
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Assign Delivery</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.assign-delivery', $order->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="delivery_id" class="form-label">Delivery Person</label>
                            <select class="form-control" id="delivery_id" name="delivery_id" required>
                                <option value="">Select...</option>
                                @foreach ($deliveryPersons as $delivery)
                                    <option value="{{ $delivery->id }}">{{ $delivery->first_name }} {{ $delivery->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($order->delivery_id)
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Delivery Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Delivery Person:</strong> {{ $order->delivery->first_name }} {{ $order->delivery->last_name }}</p>
                    <p><strong>Phone:</strong> {{ $order->delivery->phone }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($order->delivery->status) }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order Summary</h5>
                <p>Total: ${{ number_format($order->total_amount, 2) }}</p>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary w-100">Back to Orders</a>
            </div>
        </div>
    </div>
</div>
@endsection

