@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1>الطلبات</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">+ إنشاء طلب</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>مصدر الطلب</th>
                    <th>النوع</th>
                    <th>الزبون</th>
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
                        <td>{{ $order::SOURCES[$order->order_source] }}</td>
                        <td>
                            @if ($order->client_id)
                                <span class="badge bg-primary">زبون</span>
                            @else
                                <span class="badge bg-warning text-dark">يدوي</span>
                            @endif
                        </td>
                        <td>
                            @if ($order->client_id)
                                {{ $order->client->first_name }} {{ $order->client->last_name }}
                            @else
                                {{ $order->client_name }}
                            @endif
                        </td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span
                            class="badge
                            @if ($order->status === 'pending') bg-warning
                            @elseif($order->status === 'confirmed') bg-info
                            @elseif($order->status === 'shipped') bg-primary
                            @elseif($order->status === 'delivered') bg-primary
                            @elseif($order->status === 'done') bg-success
                            @elseif($order->status === 'canceled') bg-danger
                            @elseif($order->status === 'returned') bg-secondary @endif">
                            {{ $order::STATUSES[$order->status] }}
                        </span>
                        </td>
                        
                        <td>{{ $order->order_date->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">لا توجد طلبات.</td>
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
