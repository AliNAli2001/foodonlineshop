@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Products</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Filter</h5>
                <form action="{{ route('products.index') }}" method="GET">
                    <div class="mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search products...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categories</label>
                        @foreach ($categories as $category)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cat_{{ $category->id }}" 
                                       name="categories[]" value="{{ $category->id }}"
                                       {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="cat_{{ $category->id }}">
                                    {{ $category->name_en }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                   value="1" {{ request('featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">
                                Featured Only
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="row">
            @forelse ($products as $product)
                <div class="col-md-4 mb-3">
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
                    <p>No products found.</p>
                </div>
            @endforelse
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

