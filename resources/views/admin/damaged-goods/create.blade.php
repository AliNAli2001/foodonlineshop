@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Record Damaged Goods</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.damaged-goods.index') }}" class="btn btn-secondary">Back</a>
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
            <form action="{{ route('admin.damaged-goods.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Product</label>
                    <select name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                        <option value="">-- Select Product --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity</label>
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
                    <label class="form-label">Source</label>
                    <select name="source" class="form-control @error('source') is-invalid @enderror" required>
                        <option value="">-- Select Source --</option>
                        <option value="inventory" {{ old('source') == 'inventory' ? 'selected' : '' }}>Inventory (will create transaction)</option>
                        <option value="external" {{ old('source') == 'external' ? 'selected' : '' }}>External</option>
                        <option value="returned" {{ old('source') == 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                    @error('source')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Selecting "Inventory" will automatically create an inventory transaction record and deduct from stock.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Return Item (Optional)</label>
                    <select name="return_item_id" class="form-control @error('return_item_id') is-invalid @enderror">
                        <option value="">-- Select Return Item --</option>
                        @foreach ($returnItems as $returnItem)
                            <option value="{{ $returnItem->id }}" {{ old('return_item_id') == $returnItem->id ? 'selected' : '' }}>
                                Return #{{ $returnItem->id }} - {{ $returnItem->orderItem->product->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('return_item_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <textarea 
                        name="reason" 
                        class="form-control @error('reason') is-invalid @enderror"
                        rows="3"
                        required
                    >{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Record Damaged Goods</button>
                    <a href="{{ route('admin.damaged-goods.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

