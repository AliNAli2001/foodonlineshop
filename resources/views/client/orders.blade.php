@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>My Orders</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('client.order-details', $order->id) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No orders found.</td>
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

