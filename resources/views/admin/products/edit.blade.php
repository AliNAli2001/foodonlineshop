@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Edit Product</h1>
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

                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name_ar" class="form-label">Name (Arabic)</label>
                                <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                    id="name_ar" name="name_ar" value="{{ $product->name_ar }}" required>
                                @error('name_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name_en" class="form-label">Name (English)</label>
                                <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                    id="name_en" name="name_en" value="{{ $product->name_en }}" required>
                                @error('name_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="description_ar" class="form-label">Description (Arabic)</label>
                                <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar"
                                    rows="3">{{ $product->description_ar }}</textarea>
                                @error('description_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="description_en" class="form-label">Description (English)</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en"
                                    rows="3">{{ $product->description_en }}</textarea>
                                @error('description_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror"
                                    id="price" name="price" value="{{ $product->price }}" step="0.01" required>
                                @error('price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="max_order_item" class="form-label">Max Order Item</label>
                                <input type="number" class="form-control @error('max_order_item') is-invalid @enderror"
                                    id="max_order_item" name="max_order_item" value="{{ $product->max_order_item }}">
                                @error('max_order_item')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        

                        <div class="mb-3">
                            <label for="categories" class="form-label">Categories</label>
                            <div>
                                @foreach ($categories as $category)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]"
                                            id="category_{{ $category->id }}" value="{{ $category->id }}"
                                            {{ $product->categories->contains($category->id) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="category_{{ $category->id }}">{{ $category->name_en }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Images</label>
                            @if ($product->images->count() > 0)
                                <div class="row">
                                    @foreach ($product->images as $image)
                                        <div class="col-md-3 mb-2">
                                            <img src="{{ asset('storage/' . $image->image_url) }}" alt="Product Image"
                                                style="max-width: 100%; max-height: 150px;">
                                            @if ($image->is_primary)
                                                <span class="badge bg-primary">Primary</span>
                                            @endif
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="image_ids_to_delete[]"
                                                    id="image_{{ $image->id }}" value="{{ $image->id }}">
                                                <label class="form-check-label" for="image_{{ $image->id }}">Delete this image</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No images yet</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Add More Product Images</label>
                            <input type="file" class="form-control @error('images') is-invalid @enderror" id="images"
                                name="images[]" accept="image/*" multiple>
                            <small class="form-text text-muted">You can select multiple images to add to this
                                product.</small>
                            @error('images')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured"
                                    value="1" {{ $product->featured ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">
                                    Featured
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
