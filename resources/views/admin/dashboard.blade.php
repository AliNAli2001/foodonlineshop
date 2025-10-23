@extends('layouts.admin')

@section('content')
<h1>Dashboard</h1>

<div class="row mt-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text display-4">{{ $totalOrders }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pending Orders</h5>
                <p class="card-text display-4">{{ $pendingOrders }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Products</h5>
                <p class="card-text display-4">{{ $totalProducts }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Clients</h5>
                <p class="card-text display-4">{{ $totalClients }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Low Stock Products</h5>
            </div>
            <div class="card-body">
                @if ($lowStockProducts->count() > 0)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Alert Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStockProducts as $inventory)
                                <tr>
                                    <td>{{ $inventory->product->name_en }}</td>
                                    <td>{{ $inventory->stock_quantity }}</td>
                                    <td>{{ $inventory->minimum_alert_quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No low stock products.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Orders</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                @if ($order->client)
                                <td>{{ $order->client->first_name }} {{ $order->client->last_name }}</td>    
                                @else
                                    <td>By Admin</td>
                                @endif
                                
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

