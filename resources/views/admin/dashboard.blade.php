@extends('layouts.admin')

@section('content')
    <h1>لوحة التحكم</h1>

    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">إجمالي الطلبات</h5>
                    <p class="card-text display-4">{{ $totalOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">الطلبات المعلقة</h5>
                    <p class="card-text display-4">{{ $pendingOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">إجمالي المنتجات</h5>
                    <p class="card-text display-4">{{ $totalProducts }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockProducts as $inventory)
                                    <tr>
                                        <td>{{ $inventory->product->name_en }}</td>
                                        <td>{{ $inventory->stock_quantity }}</td>
                                        <td>{{ $inventory->minimum_alert_quantity }}</td>
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
