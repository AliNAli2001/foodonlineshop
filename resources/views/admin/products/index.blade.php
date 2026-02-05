@extends('layouts.admin')
@push('styles')
    <style>
       

        .table thead th {
            font-weight: 600;
            font-size: 18px;
        }

        .badge {

            padding: 6px 10px;
        }

        .price-text {
            font-size: 16px;
            color: black;
        }

        .quantity-text {
            font-size: 16px;
        }

        .dollar-sign {
            color: green;
            font-weight: bolder;
        }

        .quantity-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            min-width: 55px;
            display: inline-block;
        }
    </style>
@endpush
@section('content')
    <div class="row mb-4 align-items-around shadow-sm border-0">
        <div class="col-md-2">
            <h1 class="mb-0">المنتجات</h1>
        </div>

        <div class="col-md-6">
            <form method="GET" action="{{ route('admin.products.index') }}">
                <div class="row g-2">

                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control"
                            placeholder="ابحث برقم المنتج أو الاسم بالعربي أو الإنجليزي..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                    </div>

                </div>
            </form>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> إضافة منتج
            </a>
        </div>
    </div>





    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">الصورة</th>
                            <th>الاسم</th>
                            <th>السعر</th>
                            <th>المخزون</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="product-row">
                                <td class="text-center fw-semibold text-muted">{{ $product->id }}</td>

                                <td class="text-center">
                                    <img src="{{ $product->primaryImage?->full_url ?? '' }}" class="rounded shadow-sm"
                                        style="height:100px; object-fit:cover;" alt="product image">
                                </td>

                                <td>
                                    {{ $product->name_ar }}
                                    @if ($product->featured)
                                        <span class="badge bg-success ms-1">مميز</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="dollar-sign">$</span> <span
                                        class="price-text">{{ number_format($product->selling_price, 2) }}</span>
                                </td>

                                <td>
                                    <span
                                        class="quantity-badge px-2 py-1 text-center rounded fw-bold {{ $product->isLowStock() ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                        {{ $product->stock_available_quantity ?? 0 }}
                                    </span>

                                
                                </td>


                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        <a href="{{ route('admin.products.show', $product->id) }}"
                                            class="btn btn-sm btn-info d-flex align-items-center justify-content-center"
                                            style="width:34px; height:34px;" title="التفاصيل">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                            class="btn btn-sm btn-warning d-flex align-items-center justify-content-center"
                                            style="width:34px; height:34px;" title="تعديل">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <a href="{{ route('admin.products.tags.index', $product->id) }}"
                                            class="btn btn-sm btn-secondary d-flex align-items-center justify-content-center"
                                            style="width:34px; height:34px;" title="الوسوم">
                                            <i class="fa-solid fa-tags"></i>
                                        </a>

                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center"
                                                style="width:34px; height:34px;" title="حذف"
                                                onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>

                                    </div>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-muted">لا توجد منتجات حالياً</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="row mt-4">
                <div class="col-md-12"> {{ $products->links() }} </div>
            </div>
        </div>
    </div>
@endsection
