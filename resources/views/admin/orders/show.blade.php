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
                    @if ($order->client_id)
                        <p><strong>Client:</strong> {{ $order->client->first_name }} {{ $order->client->last_name }}</p>
                        <p><strong>Client Phone:</strong> {{ $order->client->phone }}</p>
                    @else
                        <p><strong>Order Type:</strong> <span class="badge bg-warning">Admin Created</span></p>
                        @if ($order->createdByAdmin)
                            <p><strong>Created By:</strong> {{ $order->createdByAdmin->first_name }}
                                {{ $order->createdByAdmin->last_name }}</p>
                        @endif
                    @endif
                    <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($order->status) }}</span></p>
                    <p><strong>Order Date:</strong> {{ $order->order_date->format('Y-m-d H:i') }}</p>
                    <p><strong>Order Source:</strong> {{ ucfirst(str_replace('_', ' ', $order->order_source)) }}</p>
                    <p><strong>Delivery Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</p>
                    <p><strong>Address:</strong> {{ $order->address_details }}</p>
                    @if ($order->latitude && $order->longitude)
                        <p><strong>Location:</strong> {{ $order->latitude }}, {{ $order->longitude }}</p>
                    @endif
                    @if ($order->shipping_notes)
                        <p><strong>Shipping Notes:</strong> {{ $order->shipping_notes }}</p>
                    @endif
                    @if ($order->admin_order_client_notes)
                        <p><strong>Admin Notes:</strong> {{ $order->admin_order_client_notes }}</p>
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
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name_en }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $item->status === 'normal' ? 'bg-success' : 'bg-warning' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status Management Section -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Order Status Management</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3"><strong>Current Status:</strong> <span
                            class="badge bg-info">{{ ucfirst($order->status) }}</span></p>

                    @if ($order->status === 'pending')
                        <div class="btn-group" role="group">
                            <form action="{{ route('admin.orders.confirm', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">Confirm Order</button>
                            </form>
                            <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <textarea name="reason"></textarea>
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure?')">Reject Order</button>
                            </form>
                        </div>
                    @elseif ($order->status === 'confirmed')
                        <div class="btn-group" role="group">
                            @if ($order->order_source == 'inside_city')
                                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="delivered">
                                    @if ($order->delivery_method === 'delivery' && $order->order_source === 'inside_city' && !$order->delivery_id)
                                        <div class="mb-3">
                                            <label for="delivery_id" class="form-label">Select Delivery Person (Required)</label>
                                            <select class="form-control" id="delivery_id" name="delivery_id" required>
                                                <option value="">-- Select Delivery Person --</option>
                                                @foreach ($deliveryPersons as $delivery)
                                                    <option value="{{ $delivery->id }}">{{ $delivery->first_name }} {{ $delivery->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <button type="submit" class="btn btn-primary">
                                        @if ($order->delivery_method === 'delivery' && $order->order_source === 'inside_city' && !$order->delivery_id)
                                            Assign Delivery & Mark as Delivered
                                        @else
                                            Mark as Delivered
                                        @endif
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="shipped">
                                    @if ($order->delivery_method === 'delivery' && $order->order_source === 'inside_city' && !$order->delivery_id)
                                        <div class="mb-3">
                                            <label for="delivery_id" class="form-label">Select Delivery Person (Required)</label>
                                            <select class="form-control" id="delivery_id" name="delivery_id" required>
                                                <option value="">-- Select Delivery Person --</option>
                                                @foreach ($deliveryPersons as $delivery)
                                                    <option value="{{ $delivery->id }}">{{ $delivery->first_name }} {{ $delivery->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <button type="submit" class="btn btn-primary">
                                        @if ($order->delivery_method === 'delivery' && $order->order_source === 'inside_city' && !$order->delivery_id)
                                            Assign Delivery & Mark as Shipped
                                        @else
                                            Mark as Shipped
                                        @endif
                                    </button>
                                </form>
                            @endif


                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="canceled">
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure?')">Cancel Order</button>
                            </form>
                        </div>
                    @elseif ($order->status === 'shipped' || $order->status === 'delivered')
                        <div class="btn-group" role="group">

                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="done">
                                <button type="submit" class="btn btn-success">Mark as Done</button>
                            </form>
                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="returned">
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure?')">Order returned</button>
                            </form>
                        </div>
                    @elseif ($order->status === 'delivered')
                        <div class="btn-group" role="group">
                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="done">
                                <button type="submit" class="btn btn-success">Mark as Done</button>
                            </form>
                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="canceled">
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure?')">Cancel Order</button>
                            </form>
                        </div>
                    @elseif ($order->status === 'done' || $order->status === 'canceled')
                        <p class="text-muted">This order is {{ $order->status }}. No further actions available.</p>
                    @endif
                </div>
            </div>

            

            @if ($order->delivery_id)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Delivery Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Delivery Person:</strong> {{ $order->delivery->first_name }}
                            {{ $order->delivery->last_name }}</p>
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