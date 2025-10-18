@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Categories</h1>
    </div>
</div>

<div class="row">
    @forelse ($categories as $category)
        <div class="col-md-4 mb-3">
            <div class="card">
                @if ($category->image)
                    <img src="{{ $category->image->image_url }}" class="card-img-top" alt="{{ $category->name_en }}">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $category->name_en }}</h5>
                    @if ($category->featured)
                        <span class="badge bg-warning">Featured</span>
                    @endif
                    <a href="{{ route('categories.show', $category->id) }}" class="btn btn-sm btn-primary mt-2">View Products</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-md-12">
            <p>No categories available.</p>
        </div>
    @endforelse
</div>

<div class="row mt-4">
    <div class="col-md-12">
        {{ $categories->links() }}
    </div>
</div>
@endsection

