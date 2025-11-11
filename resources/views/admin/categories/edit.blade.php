@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>تعديل التصنيف</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
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

                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name_ar" class="form-label">الاسم (عربي)</label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                               id="name_ar" name="name_ar" value="{{ $category->name_ar }}" required>
                        @error('name_ar')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">الاسم (إنجليزي)</label>
                        <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                               id="name_en" name="name_en" value="{{ $category->name_en }}" required>
                        @error('name_en')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">النوع</label>
                        <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">-- اختر النوع --</option>
                            <option value="company" {{ $category->type == 'company' ? 'selected' : '' }}>شركة</option>
                            <option value="class" {{ $category->type == 'class' ? 'selected' : '' }}>صف / فصل</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_image" class="form-label">صورة التصنيف</label>
                        @if ($category->category_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $category->category_image) }}" alt="{{ $category->name_en }}" style="max-width: 150px; max-height: 150px;">
                            </div>
                        @endif
                        <input type="file" class="form-control @error('category_image') is-invalid @enderror"
                               id="category_image" name="category_image" accept="image/*">
                        @error('category_image')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" {{ $category->featured ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">
                                مميز
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">تحديث التصنيف</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">إلغاء</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
