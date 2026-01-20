@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>إدارة المخزون</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @foreach ($products as $product)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">{{ $product->name_en }}</h5>
                            <small>{{ $product->name_ar }}</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-info">إجمالي المخزون: {{ $product->stock_available_quantity }}</span>
                            <a href="{{ route('admin.inventory.product', $product->id) }}" class="btn btn-sm btn-info"><i
                                    class="fa fa-eye"></i> عرض</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
@endsection
