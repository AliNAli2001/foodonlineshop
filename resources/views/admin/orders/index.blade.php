@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1>Orders</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">+ Create Order</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Delivery</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>
                            @if ($order->client_id)
                                {{ $order->client->first_name }} {{ $order->client->last_name }}
                            @else
                                <span class="badge bg-warning">Admin Order</span>
                            @endif
                        </td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                        <td>{{ $order->delivery ? $order->delivery->first_name . ' ' . $order->delivery->last_name : 'Not Assigned' }}</td>
                        <td>{{ $order->order_date->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

