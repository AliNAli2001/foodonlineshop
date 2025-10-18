@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $product->name_en }} - Product Details</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <!-- Product Info -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Product Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name (EN):</strong> {{ $product->name_en }}</p>
                    <p><strong>Name (AR):</strong> {{ $product->name_ar }}</p>
                    <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                    <p><strong>Max Order Item:</strong> {{ $product->max_order_item ?? 'Unlimited' }}</p>
                    <p><strong>Featured:</strong> <span class="badge {{ $product->featured ? 'bg-success' : 'bg-secondary' }}">{{ $product->featured ? 'Yes' : 'No' }}</span></p>
                    <p><strong>Categories:</strong> 
                        @foreach ($product->categories as $category)
                            <span class="badge bg-info">{{ $category->name_en }}</span>
                        @endforeach
                    </p>
                </div>
            </div>
        </div>

        <!-- Inventory Info -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Inventory Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Stock Quantity:</strong> {{ $product->inventory->stock_quantity }}</p>
                    <p><strong>Reserved Quantity:</strong> {{ $product->inventory->reserved_quantity }}</p>
                    <p><strong>Available:</strong> {{ $product->inventory->getAvailableStock() }}</p>
                    <p><strong>Minimum Alert:</strong> {{ $product->inventory->minimum_alert_quantity }}</p>
                    <p><strong>Status:</strong> 
                        @if ($product->inventory->isBelowMinimum())
                            <span class="badge bg-warning">Below Minimum</span>
                        @else
                            <span class="badge bg-success">OK</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Sales Statistics</h5>
                </div>
                <div class="card-body">
                    <p><strong>Total Sold:</strong> {{ $totalSold }} units</p>
                    <p><strong>Total Revenue:</strong> ${{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sellings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Recent Sellings (Last 10)</h5>
        </div>
        <div class="card-body">
            @if ($recentSellings->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSellings as $selling)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $selling->order->id) }}">Order #{{ $selling->order->id }}</a></td>
                                <td>{{ $selling->quantity }}</td>
                                <td>${{ number_format($selling->unit_price, 2) }}</td>
                                <td>${{ number_format($selling->getSubtotal(), 2) }}</td>
                                <td>{{ $selling->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No sales yet</p>
            @endif
        </div>
    </div>

    <!-- Recent Inventory Transactions -->
    <div class="card">
        <div class="card-header">
            <h5>Recent Inventory Transactions (Last 10)</h5>
        </div>
        <div class="card-body">
            @if ($recentInventory->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Quantity Change</th>
                            <th>Reserved Change</th>
                            <th>Reason</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentInventory as $transaction)
                            <tr>
                                <td><span class="badge bg-info">{{ ucfirst($transaction->transaction_type) }}</span></td>
                                <td>{{ $transaction->quantity_change > 0 ? '+' : '' }}{{ $transaction->quantity_change }}</td>
                                <td>{{ $transaction->reserved_change > 0 ? '+' : '' }}{{ $transaction->reserved_change }}</td>
                                <td>{{ $transaction->reason }}</td>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No transactions yet</p>
            @endif
        </div>
    </div>
</div>
@endsection

