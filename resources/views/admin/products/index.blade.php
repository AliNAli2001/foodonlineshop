@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <h1>المنتجات</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">إضافة منتج</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>رقم المنتج</th>
                    <th>الاسم (بالإنجليزية)</th>
                    <th>السعر</th>
                    <th>المخزون</th>
                    <th>مميز</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name_en }}</td>
                        <td>${{ number_format($product->selling_price, 2) }}</td>
                        <td>{{ $product->total_available_stock }}</td>
                        <td>
                            @if ($product->featured)
                                <span class="badge bg-success">نعم</span>
                            @else
                                <span class="badge bg-secondary">لا</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info">التفاصيل</a>
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <a href="{{ route('admin.products.tags.index', $product->id) }}" class="btn btn-sm btn-secondary">الوسوم</a>
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد منتجات.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection