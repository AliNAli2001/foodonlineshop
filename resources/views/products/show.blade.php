@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        @if ($product->primaryImage)
            <img src="{{ $product->primaryImage->image_url }}" class="img-fluid" alt="{{ $product->name_en }}">
        @endif
        <div class="row mt-3">
            @foreach ($product->images as $image)
                <div class="col-md-3 mb-2">
                    <img src="{{ $image->image_url }}" class="img-fluid" alt="{{ $image->caption }}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-6">
        <h1>{{ $product->name_en }}</h1>
        <p class="text-muted">{{ $product->description_en }}</p>

        <h3 class="mb-3">${{ number_format($product->price, 2) }}</h3>

        <p>
            @if ($product->isInStock())
                <span class="badge bg-success">In Stock ({{ $product->getAvailableStock() }} available)</span>
            @else
                <span class="badge bg-danger">Out of Stock</span>
            @endif
        </p>

        @if ($product->categories->count() > 0)
            <p>
                <strong>Categories:</strong>
                @foreach ($product->categories as $category)
                    <a href="{{ route('categories.show', $category->id) }}" class="badge bg-info">{{ $category->name_en }}</a>
                @endforeach
            </p>
        @endif

        @if ($product->isInStock())
            <form id="addToCartForm" class="mt-4">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
            </form>
        @endif

        <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Back to Products</a>
    </div>
</div>

@section('scripts')
<script>
    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const quantity = document.getElementById('quantity').value;
        
        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: {{ $product->id }},
                quantity: parseInt(quantity)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = '{{ route("cart.index") }}';
            } else {
                alert(data.message);
            }
        });
    });
</script>
@endsection
@endsection

