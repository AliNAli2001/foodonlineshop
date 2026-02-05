@extends('layouts.admin')
@push('styles')
    <style>
        .english-direction>* {
            direction: ltr !important;
        }
    </style>
@endpush
@section('content')
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>{{ $product->name_ar }} {{ $product->featured ? '<span class="badge bg-success">مميز</span>' : '' }} -
                    تفاصيل المنتج</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.inventory.product', $product->id) }}" class="btn btn-info">عرض المخزون</a>
                <a href="{{ route('admin.products.tags.index', $product->id) }}" class="btn btn-secondary">إدارة
                    الوسوم</a>
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning">تعديل</a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">رجوع</a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>معلومات المنتج بالعربية</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>الاسم:</strong> {{ $product->name_ar }}</p>
                        <p><strong>الوصف:</strong></p>
                        <p>{{ $product->description_ar }}</p>
                        <p><strong>الشركة:</strong> {{ $product->company?->name_ar ?? 'غير محددة' }}</p>
                        <p><strong>الفئة:</strong> {{ $product->category?->name_ar ?? 'غير محددة' }}</p>

                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>معلومات المنتج الإنكليزية</h5>
                    </div>
                    <div class="card-body english-direction">
                        <p><strong>Name:</strong> {{ $product->name_en }}</p>

                        <p><strong>Description:</strong></p>
                        <p>{{ $product->description_en }}</p>

                        <p><strong>Company:</strong> {{ $product->company?->name_en ?? 'undefined' }}</p>
                        <p><strong>Category:</strong> {{ $product->category?->name_en ?? 'undefined' }}</p>
                    </div>
                </div>


            </div>

            <div class="col-md-6">
                <div class="row">

                </div>
                <div class="card">
                    <div class="card-header">
                        <h5>معلومات السعر</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>السعر بالدولار:</strong> {{ $product->selling_price }}</p>
                        <p><strong>السعر بالسوري:</strong> {{ $product->selling_price }}</p>


                    </div>


                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>معلومات المخزون</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>إجمالي الكمية:</strong> {{ $product->stock_available_quantity }}</p>
                        <p><strong>الحد التنبيه الأدنى:</strong> {{ $product->minimum_alert_quantity ?? 'غير محدود' }}</p>

                        <p><strong>الحالة:</strong>
                            @if ($product->isLowStock())
                                <span class="badge bg-warning">أقل من الحد الأدنى</span>
                            @else
                                <span class="badge bg-success">جيد</span>
                            @endif
                        </p>
                        <p><strong>الحد الأقصى للطلب:</strong> {{ $product->max_order_item ?? 'غير محدود' }}</p>

                    </div>

                </div>


                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light text-center fw-bold">
                        <h5 class="mb-0">التنقل بين المنتجات</h5>
                    </div>
                    <div class="card-body d-flex justify-content-between gap-3">
                        @if ($previousProduct)
                            <a href="{{ route('admin.products.show', $previousProduct->id) }}"
                                class="btn btn-outline-primary flex-fill">
                                <i class="bi bi-arrow-right me-2"></i> المنتج السابق
                            </a>
                        @else
                            <span class="btn btn-outline-secondary disabled flex-fill">لا يوجد منتج سابق</span>
                        @endif

                        @if ($nextProduct)
                            <a href="{{ route('admin.products.show', $nextProduct->id) }}"
                                class="btn btn-outline-primary flex-fill">
                                المنتج التالي <i class="bi bi-arrow-left ms-2"></i>
                            </a>
                        @else
                            <span class="btn btn-outline-secondary disabled flex-fill">لا يوجد منتج تالي</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>



        <div class="card mt-4">
            <div class="card-header">
                <h5>الوسوم</h5>
            </div>
            <div class="card-body">
                @if ($product->tags->count() > 0)
                    <div class="row">
                        @foreach ($product->tags as $tag)
                            <div class="col-md-3 mb-2">
                                <span class="badge bg-info">{{ $tag->name_ar }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">لا توجد وسوم مضافة</p>
                @endif
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
                                        <img src="{{ asset('storage/' . $image->image_url) }}" alt="صورة المنتج"
                                            style="max-width: 100%; max-height: 200px;">
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


    </div>
@endsection
