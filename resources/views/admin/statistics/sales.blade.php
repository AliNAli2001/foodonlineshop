@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">إحصائيات المبيعات</h2>
        <a href="{{ route('admin.statistics.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة للإحصائيات
        </a>
    </div>

    <!-- Date Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.statistics.sales') }}" class="row g-3">
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

    <!-- Sales Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">عدد الطلبات المكتملة</h6>
                    <h2 class="mb-0 text-primary">{{ $salesStats['total_orders'] }}</h2>
                    <small class="text-muted">طلب</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">إجمالي الإيرادات</h6>
                    <h2 class="mb-0 text-success">{{ number_format($salesStats['total_revenue'], 2) }}</h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">متوسط قيمة الطلب</h6>
                    <h2 class="mb-0 text-info">{{ number_format($salesStats['average_order_value'], 2) }}</h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Analysis -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">إجمالي التكلفة</h6>
                    <h2 class="mb-0 text-danger">{{ number_format($salesStats['total_cost'], 2) }}</h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">إجمالي الربح</h6>
                    <h2 class="mb-0 text-success">{{ number_format($salesStats['total_profit'], 2) }}</h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">هامش الربح</h6>
                    <h2 class="mb-0 text-primary">{{ number_format($salesStats['profit_margin'], 2) }}%</h2>
                    <small class="text-muted">نسبة مئوية</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">أكثر المنتجات مبيعاً (أعلى 20)</h5>
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
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $product['total_quantity'] }}</span>
                                    </td>
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

    <!-- Daily Sales -->
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
                    </table>
                </div>
            @else
                <p class="text-muted text-center mb-0">لا توجد بيانات للفترة المحددة</p>
            @endif
        </div>
    </div>
</div>
@endsection

