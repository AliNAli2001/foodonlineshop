@extends('layouts.admin')

@push('styles')
    <style>
        .top-card {
            box-shadow: 0px 0px 2px rgb(96, 92, 92);
        }

        .card-border-primary {
            border-bottom: 4px solid #0d6efd;
        }

        .card-border-success {
            border-bottom: 4px solid #198754;
        }

        .card-border-warning {
            border-bottom: 4px solid #ffc107;
        }

        .card-border-danger {
            border-bottom: 4px solid #dc3545;
        }

        .card-border-info {
            border-bottom: 4px solid #0dcaf0;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #0d6efd;
            background-color: #e7f1ff;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .action-link i {
            font-size: 12px;
        }

        .action-link:hover {
            background-color: #0d6efd;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(13, 110, 253, 0.25);
        }



        td,
        th {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <h1>لوحة التحكم</h1>

    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card top-card card-border-primary">
                <div class="card-body">
                    <h5 class="card-title">الطلبات الجديدة</h5>
                    <p class="card-text display-4">{{ $pendingOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card top-card card-border-success">
                <div class="card-body">
                    <h5 class="card-title">الطلبات المؤكدة</h5>
                    <p class="card-text display-4">{{ $confirmedOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card top-card card-border-danger">
                <div class="card-body">
                    <h5 class="card-title">منتجات منخفضة المخزون</h5>
                    <p class="card-text display-4">{{ $lowStockProductsCount }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card top-card card-border-info">
                <div class="card-body">
                    <h5 class="card-title">إجمالي الزبائن</h5>
                    <p class="card-text display-4">{{ $totalClients }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>المنتجات منخفضة المخزون</h5>
                </div>
                <div class="card-body">
                    @if ($lowStockProducts->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>كمية المخزن</th>
                                    <th>حد التحذير</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockProducts as $product)
                                    <tr>
                                        <td>{{ $product->name_en }}</td>
                                        <td>{{ $product->stock_available_quantity }}</td>
                                        <td>{{ $product->minimum_alert_quantity }}</td>
                                        <td><a href="{{ route('admin.inventory.product', $product->id) }}"
                                                class="action-link">عرض<i class="fa fa-eye"></i></a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>لا توجد منتجات منخفضة المخزون.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>الطلبات الأخيرة</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>الزبون</th>
                                <th>الإجمالي</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    @if ($order->client)
                                        <td>{{ $order->client->first_name }} {{ $order->client->last_name }}</td>
                                    @else
                                        <td>عن طريق المدير</td>
                                    @endif

                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td><span
                                            class="badge bg-info">{{ \App\Models\Order::STATUSES[$order->status] ?? 'غير معروفة' }}
                                        </span></td>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}" class="action-link">عرض<i
                                                class="fa fa-eye"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5>الدفعات القريبة من تاريخ الانتهاء</h5>
                </div>

                <div class="card-body">
                    @if ($expiredSoonInventories->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>رقم الدفعة</th>
                                    <th>الكمية المتوفرة</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>الأيام المتبقية</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiredSoonInventories as $batch)
                                    <tr>
                                        <td>{{ $batch->product->name_en ?? '-' }}</td>
                                        <td>{{ $batch->batch_number }}</td>
                                        <td>{{ $batch->available_quantity }}</td>
                                        <td>{{ $batch->expiry_date->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $batch->getDaysUntilExpiry() }} يوم
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.inventory.product', $batch->product_id) }}"
                                                class="action-link">
                                                عرض <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>لا توجد دفعات قريبة من تاريخ الانتهاء.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
