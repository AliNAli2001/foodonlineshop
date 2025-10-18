@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Inventory Management</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock Quantity</th>
                        <th>Reserved</th>
                        <th>Available</th>
                        <th>Min Alert</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inventory as $inv)
                        <tr>
                            <td>{{ $inv->product->name_en }}</td>
                            <td>{{ $inv->stock_quantity }}</td>
                            <td>{{ $inv->reserved_quantity }}</td>
                            <td>{{ $inv->getAvailableStock() }}</td>
                            <td>{{ $inv->minimum_alert_quantity }}</td>
                            <td>
                                @if ($inv->isBelowMinimum())
                                    <span class="badge bg-warning">Below Min</span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.inventory.show', $inv->product->id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('admin.inventory.edit', $inv->product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $inventory->links() }}
        </div>
    </div>
</div>
@endsection

