@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $product->name_en }} - Product Details</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
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
                    <p><strong>Description (EN):</strong></p>
                    <p>{{ $product->description_en }}</p>
                    <p><strong>Description (AR):</strong></p>
                    <p>{{ $product->description_ar }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
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

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Categories</h5>
                </div>
                <div class="card-body">
                    @if ($product->categories->count() > 0)
                        <div class="row">
                            @foreach ($product->categories as $category)
                                <div class="col-md-3 mb-2">
                                    <span class="badge bg-info">{{ $category->name_en }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No categories assigned</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Product Images</h5>
                </div>
                <div class="card-body">
                    @if ($product->images->count() > 0)
                        <div class="row">
                            @foreach ($product->images as $image)
                                <div class="col-md-3 mb-3">
                                    <img src="{{ asset('storage/' . $image->image_url) }}" alt="Product Image" style="max-width: 100%; max-height: 200px;">
                                    @if ($image->is_primary)
                                        <div><span class="badge bg-primary">Primary</span></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No images yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.inventory.show', $product->id) }}" class="btn btn-info">View Inventory</a>
                <a href="{{ route('admin.products.categories.index', $product->id) }}" class="btn btn-secondary">Manage Categories</a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back to Products</a>
            </div>
        </div>
    </div>
</div>
@endsection

