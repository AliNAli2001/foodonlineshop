@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $product->name_en }} - تفاصيل المنتج</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning">تعديل</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>معلومات المنتج</h5>
                </div>
                <div class="card-body">
                    <p><strong>الاسم (بالإنجليزية):</strong> {{ $product->name_en }}</p>
                    <p><strong>الاسم (بالعربية):</strong> {{ $product->name_ar }}</p>
                    <p><strong>السعر:</strong> ${{ number_format($product->selling_price, 2) }}</p>
                    <p><strong>الحد الأقصى للطلب:</strong> {{ $product->max_order_item ?? 'غير محدود' }}</p>
                    <p><strong>مميز:</strong> 
                        <span class="badge {{ $product->featured ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->featured ? 'نعم' : 'لا' }}
                        </span>
                    </p>
                    <p><strong>الوصف (بالإنجليزية):</strong></p>
                    <p>{{ $product->description_en }}</p>
                    <p><strong>الوصف (بالعربية):</strong></p>
                    <p>{{ $product->description_ar }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>معلومات المخزون</h5>
                </div>
                <div class="card-body">
                    <p><strong>إجمالي الكمية:</strong> {{ $product->total_stock }}</p>
                    <p><strong>الكمية المحجوزة:</strong> {{ $product->total_reserved_stock }}</p>
                    <p><strong>المتوفر:</strong> {{ $product->total_available_stock }}</p>
                    {{-- <p><strong>حد التنبيه الأدنى:</strong> {{ $product->inventory->minimum_alert_quantity }}</p>
                    <p><strong>الحالة:</strong> 
                        @if ($product->inventory->isBelowMinimum())
                            <span class="badge bg-warning">أقل من الحد الأدنى</span>
                        @else
                            <span class="badge bg-success">جيد</span>
                        @endif
                    </p> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>الفئات</h5>
                </div>
                <div class="card-body">
                    @if ($product->categories->count() > 0)
                        <div class="row">
                            @foreach ($product->categories as $category)
                                <div class="col-md-3 mb-2">
                                    <span class="badge bg-info">{{ $category->name_ar }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">لا توجد فئات مضافة</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>صور المنتج</h5>
                </div>
                <div class="card-body">
                    @if ($product->images->count() > 0)
                        <div class="row">
                            @foreach ($product->images as $image)
                                <div class="col-md-3 mb-3">
                                    <img src="{{ asset('storage/' . $image->image_url) }}" alt="صورة المنتج" style="max-width: 100%; max-height: 200px;">
                                    @if ($image->is_primary)
                                        <div><span class="badge bg-primary">رئيسية</span></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">لا توجد صور حتى الآن</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.inventory.product', $product->id) }}" class="btn btn-info">عرض المخزون</a>
                <a href="{{ route('admin.products.categories.index', $product->id) }}" class="btn btn-secondary">إدارة الفئات</a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">العودة إلى المنتجات</a>
            </div>
        </div>
    </div>
</div>
@endsection
