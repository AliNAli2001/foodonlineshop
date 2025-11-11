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
                    <th>العميل</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>التوصيل</th>
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
                                <span class="badge bg-warning">طلب إداري</span>
                            @endif
                        </td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                        <td>{{ $order->delivery ? $order->delivery->first_name . ' ' . $order->delivery->last_name : 'لم يتم التعيين' }}</td>
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
