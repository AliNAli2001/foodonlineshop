@extends('layouts.admin')

@section('content')
<div class="container mt-4">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Product Inventory: {{ $product->name_en }}</h2>
            <p class="text-muted">{{ $product->name_ar }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Back to Inventory List</a>
            <a href="{{ route('admin.inventory.edit', $product->id) }}" class="btn btn-warning">Edit Inventory</a>
        </div>
    </div>

    <!-- Product Summary -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Product Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Stock:</strong>
                    <p>{{ $product->total_stock }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Available:</strong>
                    <p>{{ $product->total_available_stock }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Reserved:</strong>
                    <p>{{ $product->total_reserved_stock }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Minimum Alert:</strong>
                    <p>
                        @php
                            $minAlert = $inventories->min('minimum_alert_quantity');
                        @endphp
                        {{ $minAlert ?? '—' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Inventory Batches -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Inventory Batches</h5>
        </div>
        <div class="card-body">
            @if ($inventories->count() > 0)
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Batch Number</th>
                            <th>Expiry Date</th>
                            <th>Stock</th>
                            <th>Reserved</th>
                            <th>Available</th>
                            <th>Min Alert</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventories as $inventory)
                            <tr class="@if($inventory->isExpired()) table-danger @elseif($inventory->isExpiringSoon()) table-warning @endif">
                                <td>
                                    @if ($inventory->batch_number)
                                        <code>{{ $inventory->batch_number }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($inventory->expiry_date)
                                        <strong>{{ $inventory->expiry_date->format('Y-m-d') }}</strong>
                                        @if ($inventory->isExpired())
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif ($inventory->isExpiringSoon())
                                            <span class="badge bg-warning">Expiring Soon ({{ $inventory->getDaysUntilExpiry() }} days)</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No expiry</span>
                                    @endif
                                </td>
                                <td>{{ $inventory->stock_quantity }}</td>
                                <td>{{ $inventory->reserved_quantity }}</td>
                                <td><strong>{{ $inventory->getAvailableStock() }}</strong></td>
                                <td>{{ $inventory->minimum_alert_quantity }}</td>
                                <td>
                                    
                                    @if ($inventory->isBelowMinimum())
                                        <span class="badge bg-warning">Below Min</span>
                                    @elseif ($inventory->isExpired())
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                                <td>
                                    
                                    <a href="{{ route('admin.inventory.show', $inventory->id) }}" class="btn btn-sm btn-info" title="View Batch">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.inventory.edit', $product->id) }}" class="btn btn-sm btn-warning" title="Edit Inventory">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info mb-0">No inventory records found for this product.</div>
            @endif
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h5>Recent Transactions</h5>
        </div>
        <div class="card-body">
            @if ($transactions->count() > 0)
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Quantity Change</th>
                            <th>Reserved Change</th>
                            <th>Reason</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
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
                {{ $transactions->links() }}
            @else
                <p class="text-muted mb-0">No transactions yet for this product.</p>
            @endif
        </div>
    </div>
</div>
@endsection
