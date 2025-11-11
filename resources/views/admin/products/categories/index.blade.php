@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>إدارة التصنيفات للمنتج: {{ $product->name_ar ?? $product->name_en }}</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">الرجوع إلى قائمة المنتجات</a>
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
            <form action="{{ route('admin.products.categories.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">اختر التصنيفات</label>
                    <div class="border p-3" style="max-height: 400px; overflow-y: auto;">
                        @foreach ($allCategories as $category)
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="categories[]" 
                                    value="{{ $category->id }}"
                                    id="category_{{ $category->id }}"
                                    {{ in_array($category->id, $selectedCategoryIds) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="category_{{ $category->id }}">
                                    {{ $category->name_ar }} ({{ $category->name_en }})
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">حفظ التصنيفات</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
