@extends('layouts.admin')

@section('content')
    <div class="container mt-4">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>المنتج: {{ $product->name_ar }}</h2>
                <p class="text-muted">{{ $product->name_en }}</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.inventory.create', $product->id) }}" class="btn btn-warning">+ إضافة مخزون</a>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">العودة لقائمة المخزون</a>
            </div>
        </div>

        <!-- Product Summary -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">ملخص المنتج</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>إجمالي المخزون:</strong>
                        <p>{{ $product->stock_available_quantity }}</p>
                    </div>

                    <div class="col-md-3">
                        <strong>تنبيه الحد الأدنى:</strong>
                        <p>

                            {{ $product->minimum_alert_quantity ?? '—' }}
                        </p>

                    </div>
                    <div class="col-md-3">
                        <strong>الحالة:</strong>

                        <p>
                            @if ($product->isLowStock())
                                <span class="badge bg-warning text-dark">أقل من الحد الأدنى <i class="fa fa-exclamation-triangle"></i></span>
                            @else
                                <span class="badge bg-success">جيد</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Inventory Batches -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>دفعات المخزون</h5>
            </div>
            <div class="card-body">
                @if ($batches->count() > 0)
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الدفعة</th>
                                <th>تاريخ الانتهاء</th>
                                <th>سعر التكلفة</th>
                                <th>المخزون</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batches as $inventory)
                                <tr
                                    class="@if ($inventory->isExpired()) table-danger @elseif($inventory->isExpiringSoon()) table-warning @endif">
                                    <td>
                                        @if ($inventory->batch_number)
                                            <code>{{ $inventory->batch_number }}</code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($inventory->expiry_date)
                                            <strong>{{ $inventory->expiry_date->format('Y-m-d') }}</strong>
                                            @if ($inventory->isExpired())
                                                <span class="badge bg-danger">منتهي</span>
                                            @elseif ($inventory->isExpiringSoon())
                                                <span class="badge bg-warning">سينتهي قريباً
                                                    ({{ $inventory->getDaysUntilExpiry() }} أيام)
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">لا يوجد تاريخ انتهاء</span>
                                        @endif
                                    </td>
                                    <td>{{ $inventory->cost_price }}</td>
                                    <td>{{ $inventory->available_quantity }}</td>


                                    <td>
                                        @if ($inventory->isExpired())
                                            <span class="badge bg-danger">منتهي</span>
                                        @else
                                            <span class="badge bg-success">متاح</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.inventory.show', $inventory->id) }}"
                                            class="btn btn-sm btn-info" title="عرض الدفعة">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $inventory->id) }}"
                                            class="btn btn-sm btn-warning" title="تعديل المخزون">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info mb-0">لا توجد سجلات مخزون لهذا المنتج.</div>
                @endif
            </div>
        </div>

        <!-- Recent Movements -->
        <div class="card">
            <div class="card-header">
                <h5>المعاملات الأخيرة</h5>
            </div>
            <div class="card-body">
                @if ($movements->count() > 0)
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>النوع</th>
                                <th>تغيير الكمية</th>
                                
                                <th>السبب</th>
                                <th>التاريخ</th>
                                <th>رقم الدفعة</th>
                                <th>تاريخ الانتهاء</th>
                                <th>سعر التكلفة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $transaction)
                                <tr>
                                    <td><span class="badge bg-info">{{ ucfirst($transaction->transaction_type) }}</span>
                                    </td>
                                    <td>{{ $transaction->available_change > 0 ? '+' : '' }}{{ $transaction->available_change }}
                                    </td>
                                    
                                    <td>{{ $transaction->reason }}</td>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                    <td><a class="btn btn-sm btn-info"
                                            href="{{ route('admin.inventory.show', $transaction->inventoryBatch->id) }}">
                                            <i class="fa fa-eye"></i> {{ $inventory->batch_number }}</a></td>
                                    <td>{{ $transaction->expiry_date }}</td>
                                    <td>{{ $transaction->cost_price }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $movements->links() }}
                @else
                    <p class="text-muted mb-0">لا توجد معاملات بعد لهذا المنتج.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
