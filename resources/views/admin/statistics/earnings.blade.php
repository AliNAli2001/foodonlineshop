@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">الأرباح والخسائر</h2>
        <a href="{{ route('admin.statistics.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة للإحصائيات
        </a>
    </div>

    <!-- Date Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.statistics.earnings') }}" class="row g-3">
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

    <!-- Earnings Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">الربح من المبيعات</h6>
                    <h2 class="mb-0 text-success">{{ number_format($earningsStats['profit_from_sales'], 2) }}</h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">الأرباح من التسويات</h6>
                    <h2 class="mb-0 text-primary">{{ number_format($earningsStats['gains_from_adjustments'], 2) }}</h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">الخسائر من التسويات</h6>
                    <h2 class="mb-0 text-danger">{{ number_format($earningsStats['losses_from_adjustments'], 2) }}</h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">صافي الأرباح</h6>
                    <h2 class="mb-0 {{ $earningsStats['net_earnings'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($earningsStats['net_earnings'], 2) }}
                    </h2>
                    <small class="text-muted">دولار</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">تفاصيل المبيعات</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>عدد الطلبات المكتملة:</strong></td>
                                <td class="text-end">{{ $salesStats['total_orders'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>إجمالي الإيرادات:</strong></td>
                                <td class="text-end text-success">{{ number_format($salesStats['total_revenue'], 2) }} دولار</td>
                            </tr>
                            <tr>
                                <td><strong>إجمالي التكلفة:</strong></td>
                                <td class="text-end text-danger">{{ number_format($salesStats['total_cost'], 2) }} دولار</td>
                            </tr>
                            <tr class="table-light">
                                <td><strong>الربح من المبيعات:</strong></td>
                                <td class="text-end text-success fw-bold">{{ number_format($salesStats['total_profit'], 2) }} دولار</td>
                            </tr>
                            <tr>
                                <td><strong>هامش الربح:</strong></td>
                                <td class="text-end">{{ number_format($salesStats['profit_margin'], 2) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">تفاصيل التسويات المالية</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>عدد الأرباح:</strong></td>
                                <td class="text-end">{{ $adjustmentsStats['gains_count'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>إجمالي الأرباح:</strong></td>
                                <td class="text-end text-success">{{ number_format($adjustmentsStats['total_gains'], 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>عدد الخسائر:</strong></td>
                                <td class="text-end">{{ $adjustmentsStats['losses_count'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>إجمالي الخسائر:</strong></td>
                                <td class="text-end text-danger">{{ number_format($adjustmentsStats['total_losses'], 2) }}</td>
                            </tr>
                            <tr class="table-light">
                                <td><strong>صافي التسويات:</strong></td>
                                <td class="text-end {{ $adjustmentsStats['net_adjustment'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ number_format($adjustmentsStats['net_adjustment'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Final Summary -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">الملخص النهائي</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">الحسابات:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="text-muted">الربح من المبيعات:</span>
                            <span class="float-end text-success fw-bold">+ {{ number_format($earningsStats['profit_from_sales'], 2) }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">الأرباح من التسويات:</span>
                            <span class="float-end text-success fw-bold">+ {{ number_format($earningsStats['gains_from_adjustments'], 2) }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">الخسائر من التسويات:</span>
                            <span class="float-end text-danger fw-bold">- {{ number_format($earningsStats['losses_from_adjustments'], 2) }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <h6 class="text-muted mb-2">صافي الأرباح</h6>
                        <h1 class="display-4 {{ $earningsStats['net_earnings'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($earningsStats['net_earnings'], 2) }}
                        </h1>
                        <p class="text-muted">دولار</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

