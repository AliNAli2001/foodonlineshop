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
                        <span class="badge bg-info">إجمالي المخزون: {{ $product->total_stock }}</span>
                        <span class="badge bg-success">المتاح: {{ $product->total_available_stock }}</span>
                        <span class="badge bg-warning">المحجوز: {{ $product->total_reserved_stock }}</span>
                        <a href="{{ route('admin.inventory.product', $product->id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> عرض</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if ($product->inventories->count() > 0)
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الدفعة</th>
                                <th>تاريخ الانتهاء</th>
                                <th>سعر التكلفة</th>
                                <th>المخزون</th>
                                <th>المحجوز</th>
                                <th>المتاح</th>
                                <th>تنبيه الحد الأدنى</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($product->inventories as $inventory)
                                <tr class="@if($inventory->isExpired()) table-danger @elseif($inventory->isExpiringSoon()) table-warning @endif">
                                    <td>
                                        @if ($inventory->batch_number)
                                            <code>{{ $inventory->batch_number }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($inventory->expiry_date)
                                            <strong>{{ $inventory->expiry_date->format('Y-m-d') }}</strong>
                                            @if ($inventory->isExpired())
                                                <span class="badge bg-danger">منتهي الصلاحية</span>
                                            @elseif ($inventory->isExpiringSoon())
                                                <span class="badge bg-warning">ستنتهي قريباً ({{ $inventory->getDaysUntilExpiry() }} أيام)</span>
                                            @endif
                                        @else
                                            <span class="text-muted">لا يوجد تاريخ انتهاء</span>
                                        @endif
                                    </td>
                                    <td>{{ $inventory->cost_price }}</td>
                                    <td>{{ $inventory->stock_quantity }}</td>
                                    <td>{{ $inventory->reserved_quantity }}</td>
                                    <td>
                                        <strong>{{ $inventory->getAvailableStock() }}</strong>
                                    </td>
                                    <td>{{ $inventory->minimum_alert_quantity }}</td>
                                    <td>
                                        @if ($inventory->isBelowMinimum())
                                            <span class="badge bg-warning">أقل من الحد الأدنى</span>
                                        @elseif ($inventory->isExpired())
                                            <span class="badge bg-danger">منتهي الصلاحية</span>
                                        @else
                                            <span class="badge bg-success">سليم</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inventory.show', $inventory->id) }}" class="btn btn-sm btn-info" title="عرض جميع الدفعات">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- <a href="{{ route('admin.inventory.edit', $inventory->id) }}" class="btn btn-sm btn-warning" title="تعديل المخزون">
                                            <i class="fas fa-edit"></i>
                                        </a> --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info mb-0">
                        لا توجد سجلات مخزون لهذا المنتج.
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-center">
        {{ $products->links() }}
    </div>
</div>
@endsection
