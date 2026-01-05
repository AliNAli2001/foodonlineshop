@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $tag->name_ar }} - تفاصيل الوسم</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('admin.tags.edit', $tag->id) }}" class="btn btn-warning">تعديل</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>معلومات الوسم</h5>
                </div>
                <div class="card-body">
                    <p><strong>الاسم (إنجليزي):</strong> {{ $tag->name_en }}</p>
                    <p><strong>الاسم (عربي):</strong> {{ $tag->name_ar }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h5>المنتجات في هذا الوسم ({{ $products->total() }})</h5>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary">إضافة منتج</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($products->count() > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>السعر</th>
                            <th>المخزون</th>
                            <th>مميز</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->name_en }}</td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>{{ $product->inventory->stock_quantity ?? 0 }}</td>
                                <td>
                                    <span class="badge {{ $product->featured ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->featured ? 'نعم' : 'لا' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info">عرض</a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                                    <a href="{{ route('admin.products.tags.index', $product->id) }}" class="btn btn-sm btn-secondary">الوسوم</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $products->links() }}
            @else
                <p class="text-muted">لا توجد منتجات في هذا الوسم حتى الآن</p>
            @endif
        </div>
    </div>
</div>
@endsection
