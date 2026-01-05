@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>إدارة الوسوم للمنتج: {{ $product->name_ar ?? $product->name_en }}</h2>
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
            <form action="{{ route('admin.products.tags.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">اختر الوسوم</label>
                    <div class="border p-3" style="max-height: 400px; overflow-y: auto;">
                        @foreach ($allTags as $tag)
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="tags[]" 
                                    value="{{ $tag->id }}"
                                    id="tag_{{ $tag->id }}"
                                    {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="tag_{{ $tag->id }}">
                                    {{ $tag->name_ar }} ({{ $tag->name_en }})
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">حفظ الوسوم</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
