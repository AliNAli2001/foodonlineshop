@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>{{ $category->name_en }}</h1>
        @if ($category->image)
            <img src="{{ $category->image->image_url }}" class="img-fluid mb-3" alt="{{ $category->name_en }}" style="max-height: 300px;">
        @endif
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back to Categories</a>
    </div>
</div>

<div class="row">
    @forelse ($products as $product)
        <div class="col-md-3 mb-3">
            <div class="card product-card">
                @if ($product->primaryImage)
                    <img src="{{ $product->primaryImage->image_url }}" class="card-img-top" alt="{{ $product->name_en }}">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $product->name_en }}</h5>
                    <p class="card-text">Price: ${{ number_format($product->price, 2) }}</p>
                    <p class="card-text">
                        @if ($product->isInStock())
                            <span class="badge bg-success">In Stock</span>
                        @else
                            <span class="badge bg-danger">Out of Stock</span>
                        @endif
                    </p>
                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-primary">View</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-md-12">
            <p>No products in this category.</p>
        </div>
    @endforelse
</div>

<div class="row mt-4">
    <div class="col-md-12">
        {{ $products->links() }}
    </div>
</div>
@endsection

