@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Welcome to Food Online Shop</h1>
        <p>Browse our delicious products and place your order today!</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <h3>Featured Categories</h3>
    </div>
</div>

<div class="row mb-4">
    @forelse ($featuredCategories as $category)
        <div class="col-md-3 mb-3">
            <div class="card">
                @if ($category->image)
                    <img src="{{ $category->image->image_url }}" class="card-img-top" alt="{{ $category->name_en }}">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $category->name_en }}</h5>
                    <a href="{{ route('categories.show', $category->id) }}" class="btn btn-sm btn-primary">View</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-md-12">
            <p>No featured categories available.</p>
        </div>
    @endforelse
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <h3>Featured Products</h3>
    </div>
</div>

<div class="row">
    @forelse ($featuredProducts as $product)
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
            <p>No featured products available.</p>
        </div>
    @endforelse
</div>
@endsection

