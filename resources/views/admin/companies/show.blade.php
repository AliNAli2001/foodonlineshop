@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>{{ $company->name_ar }} - تفاصيل الشركة</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">رجوع</a>
            <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-warning">تعديل</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>معلومات الشركة</h5>
                </div>
                <div class="card-body">
                    <p><strong>الاسم (إنجليزي):</strong> {{ $company->name_en }}</p>
                    <p><strong>الاسم (عربي):</strong> {{ $company->name_ar }}</p>
                    @if ($company->logo)
                        <p><strong>الصورة:</strong></p>
                        <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name_en }}" style="max-width: 200px; max-height: 200px;">
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h5>المنتجات في هذه الشركة ({{ $products->total() }})</h5>
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
                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $products->links() }}
            @else
                <p class="text-muted">لا توجد منتجات في هذا الشركة حتى الآن</p>
            @endif
        </div>
    </div>
</div>
@endsection
