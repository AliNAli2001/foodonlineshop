@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1>موظف التوصيل: {{ $delivery->full_name }}</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('admin.delivery.index') }}" class="btn btn-secondary">العودة إلى قائمة موظفي التوصيل</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>تفاصيل التوصيل</h5>
            </div>
            <div class="card-body">
                <p><strong>الهاتف:</strong> {{ $delivery->phone }}</p>
                @if ($delivery->phone_plus)
                    <p><strong>هاتف إضافي:</strong> {{ $delivery->phone_plus }}</p>
                @endif
                @if ($delivery->email)
                    <p><strong>البريد الإلكتروني:</strong> {{ $delivery->email }}</p>
                @endif
                <p><strong>الحالة:</strong> <span class="badge bg-info">{{ ucfirst($delivery->status) }}</span></p>
                @if ($delivery->info)
                    <p><strong>معلومات إضافية:</strong> {{ $delivery->info }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h2>الطلبات المعينة</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>
                            @if ($order->client_id)
                                {{ $order->client->first_name }} {{ $order->client->last_name }}
                            @else
                                <span class="badge bg-warning">طلب من المسؤول</span>
                            @endif
                        </td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                        <td>{{ $order->order_date->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد طلبات معينة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
