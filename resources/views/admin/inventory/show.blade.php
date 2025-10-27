@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Inventory: {{ $product->name_en }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('admin.inventory.edit', $inventory->id) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Current Inventory</h5>
                </div>
                <div class="card-body">
                    <p><strong>Product:</strong> {{ $product->name_en }}</p>
                    <p><strong>Stock Quantity:</strong> {{ $inventory->stock_quantity }}</p>
                    <p><strong>Reserved Quantity:</strong> {{ $inventory->reserved_quantity }}</p>
                    <p><strong>Available:</strong> {{ $inventory->getAvailableStock() }}</p>
                    <p><strong>Minimum Alert:</strong> {{ $inventory->minimum_alert_quantity }}</p>
                    <p><strong>Status:</strong> 
                        @if ($inventory->isBelowMinimum())
                            <span class="badge bg-warning">Below Minimum</span>
                        @else
                            <span class="badge bg-success">OK</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Recent Transactions</h5>
        </div>
        <div class="card-body">
            @if ($transactions->count() > 0)
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
                <p class="text-muted">No transactions yet</p>
            @endif
        </div>
    </div>
</div>
@endsection

