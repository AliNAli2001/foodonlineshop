@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Create Return for Order #{{ $order->id }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.returns.index') }}" class="btn btn-secondary">Back</a>
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
            <form action="{{ route('admin.returns.store') }}" method="POST">
                @csrf

                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <div class="mb-3">
                    <label class="form-label">Select Item to Return</label>
                    <select name="order_item_id" class="form-control @error('order_item_id') is-invalid @enderror" required>
                        <option value="">-- Select Item --</option>
                        @foreach ($order->items as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->product->name_en }} - Qty: {{ $item->quantity }} @ ${{ number_format($item->unit_price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('order_item_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Return Quantity</label>
                    <input 
                        type="number" 
                        name="quantity" 
                        class="form-control @error('quantity') is-invalid @enderror"
                        value="{{ old('quantity') }}"
                        min="1"
                        required
                    >
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason for Return</label>
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
                    <button type="submit" class="btn btn-primary">Create Return</button>
                    <a href="{{ route('admin.returns.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

