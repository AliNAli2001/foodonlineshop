@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Add Product</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.products.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name_ar" class="form-label">Name (Arabic)</label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                            @error('name_ar')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="name_en" class="form-label">Name (English)</label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                                   id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                            @error('name_en')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="description_ar" class="form-label">Description (Arabic)</label>
                            <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                      id="description_ar" name="description_ar" rows="3">{{ old('description_ar') }}</textarea>
                            @error('description_ar')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="description_en" class="form-label">Description (English)</label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                      id="description_en" name="description_en" rows="3">{{ old('description_en') }}</textarea>
                            @error('description_en')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" value="{{ old('price') }}" step="0.01" required>
                            @error('price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="max_order_item" class="form-label">Max Order Item</label>
                            <input type="number" class="form-control @error('max_order_item') is-invalid @enderror" 
                                   id="max_order_item" name="max_order_item" value="{{ old('max_order_item') }}">
                            @error('max_order_item')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                   id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity') }}" required>
                            @error('stock_quantity')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="minimum_alert_quantity" class="form-label">Minimum Alert Quantity</label>
                            <input type="number" class="form-control @error('minimum_alert_quantity') is-invalid @enderror" 
                                   id="minimum_alert_quantity" name="minimum_alert_quantity" value="{{ old('minimum_alert_quantity') }}" required>
                            @error('minimum_alert_quantity')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="categories" class="form-label">Categories</label>
                        <select class="form-control" id="categories" name="categories[]" multiple>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name_en }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1">
                            <label class="form-check-label" for="featured">
                                Featured
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Product</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

