@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Returns Management</h2>
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
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($returns as $return)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $return->order->id) }}">Order #{{ $return->order->id }}</a></td>
                            <td>{{ $return->orderItem->product->name_en }}</td>
                            <td>{{ $return->quantity }}</td>
                            <td>{{ $return->reason ?? 'N/A' }}</td>
                            <td>{{ $return->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.returns.show', $return->id) }}" class="btn btn-sm btn-info">View</a>
                                <form action="{{ route('admin.returns.destroy', $return->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection

