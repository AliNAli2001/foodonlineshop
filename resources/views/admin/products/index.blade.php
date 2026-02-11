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
    <div class="row mb-4 align-items-around shadow-sm border-0 g-2">
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
        <div class="col-md-4">
            {{-- adding sort by stock amount link --}}
            <a href="{{ route('admin.products.index', ['sort' => 'stock', 'order' => 'desc']) }}" class="btn btn-primary">
                <i class="fa-solid fa-sort-amount-down me-1"></i> ترتيب حسب الكمية تنازلي
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> إضافة منتج
            </a>
        </div>
    </div>



    <!-- ==================== FILTERS SECTION ==================== -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 align-items-end">

                <!-- Search -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label fw-semibold">بحث</label>
                    <input type="text" name="search" class="form-control" placeholder="اسم المنتج أو رقم المنتج..."
                        value="{{ request('search') }}">
                </div>

                <!-- Sort By -->
                <div class="col-lg-2 col-md-3">
                    <label class="form-label fw-semibold">ترتيب حسب</label>
                    <select name="sort" class="form-select">
                        <option value="">بدون ترتيب</option>
                        <option value="stock" {{ request('sort') == 'stock' ? 'selected' : '' }}>الكمية (المخزون)</option>
                        <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>السعر</option>
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>تاريخ الإضافة
                        </option>
                    </select>
                </div>

                <!-- Order (ASC / DESC) -->
                <div class="col-lg-2 col-md-3">
                    <label class="form-label fw-semibold">الاتجاه</label>
                    <select name="order" class="form-select">
                        <option value="desc" {{ request('order') == 'desc' || !request('order') ? 'selected' : '' }}>
                            تنازلي (الأكبر أولاً)</option>
                        <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>تصاعدي (الأصغر أولاً)
                        </option>
                    </select>
                </div>

                <!-- Price Range -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label fw-semibold">نطاق السعر ($)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="min_price" step="0.01" class="form-control" placeholder="من"
                            value="{{ request('min_price') }}">
                        <input type="number" name="max_price" step="0.01" class="form-control" placeholder="إلى"
                            value="{{ request('max_price') }}">
                    </div>
                </div>

                <!-- Stock Range -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label fw-semibold">نطاق المخزون</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="min_stock" class="form-control" placeholder="من"
                            value="{{ request('min_stock') }}">
                        <input type="number" name="max_stock" class="form-control" placeholder="إلى"
                            value="{{ request('max_stock') }}">
                    </div>
                </div>

                <!-- Low Stock Only -->
                <div class="col-lg-3 col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock"
                            {{ request('low_stock') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="low_stock">
                            فقط المنتجات ذات المخزون المنخفض
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-2"></i>تطبيق الفلاتر
                    </button>

                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-rotate-right me-2"></i>إعادة تعيين
                    </a>
                </div>
            </form>
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
                <div class="d-flex justify-content-center"> {{ $products->links() }} </div>
            </div>
        </div>
    </div>
@endsection
