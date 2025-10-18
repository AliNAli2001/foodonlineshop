@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Damaged Goods Management</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.damaged-goods.create') }}" class="btn btn-primary">Add Damaged Goods</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back</a>
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
                        <th>Quantity</th>
                        <th>Source</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($damagedGoods as $damaged)
                        <tr>
                            <td>{{ $damaged->product->name_en }}</td>
                            <td>{{ $damaged->quantity }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($damaged->source) }}</span></td>
                            <td>{{ $damaged->reason }}</td>
                            <td>{{ $damaged->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.damaged-goods.show', $damaged->id) }}" class="btn btn-sm btn-info">View</a>
                                <form action="{{ route('admin.damaged-goods.destroy', $damaged->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $damagedGoods->links() }}
        </div>
    </div>
</div>
@endsection

