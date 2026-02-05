@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>تعديل المنتج</h1>
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
                                <label for="name_ar" class="form-label">الاسم (بالعربية)</label>
                                <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                    id="name_ar" name="name_ar" value="{{ $product->name_ar }}" required>
                                @error('name_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name_en" class="form-label">الاسم (بالإنجليزية)</label>
                                <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                    id="name_en" name="name_en" value="{{ $product->name_en }}" required>
                                @error('name_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="description_ar" class="form-label">الوصف (بالعربية)</label>
                                <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar"
                                    rows="3">{{ $product->description_ar }}</textarea>
                                @error('description_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="description_en" class="form-label">الوصف (بالإنجليزية)</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en"
                                    rows="3">{{ $product->description_en }}</textarea>
                                @error('description_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="selling_price" class="form-label">السعر</label>
                                <input type="text" class="form-control @error('selling_price') is-invalid @enderror"
                                    id="selling_price" name="selling_price" value="{{ $product->selling_price }}" required>
                                @error('selling_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="max_order_item" class="form-label">الحد الأقصى للطلب</label>
                                <input type="number" class="form-control @error('max_order_item') is-invalid @enderror"
                                    id="max_order_item" name="max_order_item" value="{{ $product->max_order_item }}">
                                @error('max_order_item')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">

                            <div class="col-md-6">
                                <label for="minimum_alert_quantity" class="form-label">الحد الأدنى للتنبيه</label>
                                <input type="number"
                                    class="form-control @error('minimum_alert_quantity') is-invalid @enderror"
                                    id="minimum_alert_quantity" name="minimum_alert_quantity"
                                    value="{{ $product->minimum_alert_quantity }}">
                                @error('minimum_alert_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="company_id" class="form-label">الشركة</label>
                                <select name="company_id" id="company_id"
                                    class="form-control @error('company_id') is-invalid @enderror">
                                    <option value="">-- اختر الشركة --</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('company_id', $product->company_id) == $company->id ? 'selected' : '' }}>
                                            {{ $company->name_ar }} - {{ $company->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">التصنيف</label>
                            <select name="category_id" id="category_id"
                                class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">-- اختر التصنيف --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name_ar }} - {{ $category->name_en }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label for="tags" class="form-label">الوسوم</label>
                            <div>
                                @foreach ($tags as $tag)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tags[]"
                                            id="tag_{{ $tag->id }}" value="{{ $tag->id }}"
                                            {{ $product->tags->contains($tag->id) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="tag_{{ $tag->id }}">{{ $tag->name_en }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الصور الحالية</label>
                            @if ($product->images->count() > 0)
                                <div class="row">
                                    @foreach ($product->images as $image)
                                        <div class="col-md-3 mb-2 border p-2 rounded">
                                            <img src="{{ asset('storage/' . $image->image_url) }}" alt="صورة المنتج"
                                                style="max-width: 100%; max-height: 150px;">

                                            {{-- اختيار الصورة الرئيسية --}}
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="primary_image_id"
                                                    id="primary_image_{{ $image->id }}" value="{{ $image->id }}"
                                                    {{ $image->is_primary ? 'checked' : '' }}>
                                                <label class="form-check-label" for="primary_image_{{ $image->id }}">
                                                    تعيين كصورة رئيسية
                                                </label>
                                            </div>

                                            {{-- حذف الصورة --}}
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox"
                                                    name="image_ids_to_delete[]" id="delete_image_{{ $image->id }}"
                                                    value="{{ $image->id }}">
                                                <label class="form-check-label" for="delete_image_{{ $image->id }}">
                                                    حذف هذه الصورة
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">لا توجد صور حالياً</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">إضافة صور جديدة للمنتج</label>
                            <input type="file" class="form-control @error('images') is-invalid @enderror"
                                id="images" name="images[]" accept="image/*" multiple>
                            <small class="form-text text-muted">يمكنك اختيار عدة صور لإضافتها لهذا المنتج.</small>
                            @error('images')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured"
                                    value="1" {{ $product->featured ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">
                                    مميز
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">تحديث المنتج</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
