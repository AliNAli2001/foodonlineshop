@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $category->name_en }} - Category Details</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Category Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name (EN):</strong> {{ $category->name_en }}</p>
                    <p><strong>Name (AR):</strong> {{ $category->name_ar }}</p>
                    <p><strong>Type:</strong> <span class="badge bg-info">{{ ucfirst($category->type) }}</span></p>
                    <p><strong>Featured:</strong> <span class="badge {{ $category->featured ? 'bg-success' : 'bg-secondary' }}">{{ $category->featured ? 'Yes' : 'No' }}</span></p>
                    @if ($category->category_image)
                        <p><strong>Image:</strong></p>
                        <img src="{{ asset('storage/' . $category->category_image) }}" alt="{{ $category->name_en }}" style="max-width: 200px; max-height: 200px;">
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h5>Products in This Category ({{ $products->total() }})</h5>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary">Add Product</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($products->count() > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->name_en }}</td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>{{ $product->inventory->stock_quantity ?? 0 }}</td>
                                <td>
                                    <span class="badge {{ $product->featured ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->featured ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="{{ route('admin.products.categories.index', $product->id) }}" class="btn btn-sm btn-secondary">Categories</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $products->links() }}
            @else
                <p class="text-muted">No products in this category yet</p>
            @endif
        </div>
    </div>
</div>
@endsection

