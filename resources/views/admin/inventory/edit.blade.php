@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Edit Inventory: {{ $product->name_en }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.inventory.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Product</label>
                    <input type="text" class="form-control" value="{{ $product->name_en }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stock Quantity</label>
                    <input 
                        type="number" 
                        name="stock_quantity" 
                        class="form-control @error('stock_quantity') is-invalid @enderror"
                        value="{{ old('stock_quantity', $inventory->stock_quantity) }}"
                        required
                    >
                    @error('stock_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Minimum Alert Quantity</label>
                    <input 
                        type="number" 
                        name="minimum_alert_quantity" 
                        class="form-control @error('minimum_alert_quantity') is-invalid @enderror"
                        value="{{ old('minimum_alert_quantity', $inventory->minimum_alert_quantity) }}"
                        required
                    >
                    @error('minimum_alert_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason for Adjustment</label>
                    <textarea 
                        name="reason" 
                        class="form-control @error('reason') is-invalid @enderror"
                        rows="3"
                    >{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Inventory</button>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

