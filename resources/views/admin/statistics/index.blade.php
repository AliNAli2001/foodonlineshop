@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">الإحصائيات</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.statistics.sales') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i> إحصائيات المبيعات
            </a>
            <a href="{{ route('admin.statistics.earnings') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-dollar-sign me-1"></i> الأرباح والخسائر
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.statistics.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> تصفية
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">إجمالي الإيرادات</h6>
                            <h3 class="mb-0 text-primary">{{ number_format($statistics['summary']['total_revenue'], 2) }}</h3>
                            <small class="text-muted">دولار</small>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">إجمالي التكلفة</h6>
                            <h3 class="mb-0 text-danger">{{ number_format($statistics['summary']['total_cost'], 2) }}</h3>
                            <small class="text-muted">دولار</small>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">الربح الإجمالي</h6>
                            <h3 class="mb-0 text-success">{{ number_format($statistics['summary']['gross_profit'], 2) }}</h3>
                            <small class="text-muted">دولار</small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">صافي الربح</h6>
                            <h3 class="mb-0 {{ $statistics['summary']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($statistics['summary']['net_profit'], 2) }}
                            </h3>
                            <small class="text-muted">دولار</small>
                        </div>
                        <div class="{{ $statistics['summary']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">إحصائيات المبيعات</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>عدد الطلبات المكتملة:</strong></td>
                                <td class="text-end">{{ $statistics['sales']['total_orders'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>متوسط قيمة الطلب:</strong></td>
                                <td class="text-end">{{ number_format($statistics['sales']['average_order_value'], 2) }} دولار</td>
                            </tr>
                            <tr>
                                <td><strong>هامش الربح:</strong></td>
                                <td class="text-end">{{ number_format($statistics['sales']['profit_margin'], 2) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">التسويات المالية</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>إجمالي الأرباح:</strong></td>
                                <td class="text-end text-success">{{ number_format($statistics['adjustments']['total_gains'], 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>إجمالي الخسائر:</strong></td>
                                <td class="text-end text-danger">{{ number_format($statistics['adjustments']['total_losses'], 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>صافي التسويات:</strong></td>
                                <td class="text-end {{ $statistics['adjustments']['net_adjustment'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($statistics['adjustments']['net_adjustment'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">أكثر المنتجات مبيعاً</h5>
        </div>
        <div class="card-body">
            @if(count($topProducts) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th class="text-center">الكمية المباعة</th>
                                <th class="text-end">إجمالي الإيرادات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $index => $product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product['product_name'] }}</td>
                                    <td class="text-center">{{ $product['total_quantity'] }}</td>
                                    <td class="text-end">{{ number_format($product['total_revenue'], 2) }} دولار</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center mb-0">لا توجد بيانات للفترة المحددة</p>
            @endif
        </div>
    </div>

    <!-- Daily Sales Chart -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">المبيعات اليومية</h5>
        </div>
        <div class="card-body">
            @if(count($dailySales) > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>التاريخ</th>
                                <th class="text-center">عدد الطلبات</th>
                                <th class="text-end">الإيرادات</th>
                                <th class="text-end">التكلفة</th>
                                <th class="text-end">الربح</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailySales as $day)
                                <tr>
                                    <td>{{ $day['date'] }}</td>
                                    <td class="text-center">{{ $day['orders'] }}</td>
                                    <td class="text-end">{{ number_format($day['revenue'], 2) }}</td>
                                    <td class="text-end">{{ number_format($day['cost'], 2) }}</td>
                                    <td class="text-end {{ $day['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($day['profit'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>الإجمالي</th>
                                <th class="text-center">{{ array_sum(array_column($dailySales, 'orders')) }}</th>
                                <th class="text-end">{{ number_format(array_sum(array_column($dailySales, 'revenue')), 2) }}</th>
                                <th class="text-end">{{ number_format(array_sum(array_column($dailySales, 'cost')), 2) }}</th>
                                <th class="text-end {{ array_sum(array_column($dailySales, 'profit')) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format(array_sum(array_column($dailySales, 'profit')), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="text-muted text-center mb-0">لا توجد بيانات للفترة المحددة</p>
            @endif
        </div>
    </div>
</div>
@endsection
