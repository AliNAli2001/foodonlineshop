@extends('layouts.admin')

@push('styles')
    <style>
        .product-card {
            transition: all 0.2s ease-in-out;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }
    </style>
@endpush
@section('content')
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>إدارة المخزون</h2>
            </div>
            <div class="col-md-4 text-end">
                @if (request()->routeIs('admin.inventory.index'))
                    <a href="{{ route('admin.inventory.index.low-stock') }}" class="btn btn-warning">المنتجات منخفضة
                        المخزون</a>
                @else
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-primary">جميع المنتجات</a>
                @endif


                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
            </div>
        </div>


        <div class="row g-4">
            @foreach ($products as $product)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="card-body d-flex flex-column">

                            {{-- Product Names --}}
                            <div class="mb-3">
                                <h6 class="fw-bold mb-1 text-dark">
                                    {{ $product->name_ar }}
                                </h6>
                                <small class="text-muted">
                                    {{ $product->name_en }}
                                </small>
                            </div>

                            {{-- Stock --}}
                            <div class="mb-4">
                                <span class="badge bg-light text-dark border">
                                    📦 المخزون: {{ $product->stock_available_quantity }}
                                </span>
                            </div>

                            {{-- Action --}}
                            <div class="mt-auto">
                                <a href="{{ route('admin.inventory.product', $product->id) }}"
                                    class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fa fa-eye me-1"></i> عرض التفاصيل
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>


        <div class="d-flex justify-content-center mt-3">
            {{ $products->links() }}
        </div>
    </div>
@endsection
