@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="h4 mb-0">تفاصيل الخسارة</h2>
        </div>
        <div class="col-md-4 text-start text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.losses.index') }}" class="btn btn-secondary">العودة</a>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">بيانات الخسارة</h5>
        </div>
        <div class="card-body">
            @php
                $types = [
                    'shipping_costs' => 'تكاليف الشحن',
                    'general_costs' => 'تكاليف عامة',
                    'delivery_costs' => 'تكاليف التوصيل',
                    'other' => 'أخرى',
                ];
            @endphp
            <div class="row g-3">
                <div class="col-md-3">
                    <div><strong>المعرف:</strong> {{ $loss->id }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>الكمية:</strong> {{ $loss->quantity ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>النوع:</strong> <span class="badge bg-secondary">{{ $types[$loss->type] ?? $loss->type }}</span></div>
                </div>
                <div class="col-12">
                    <div><strong>السبب:</strong> {{ $loss->reason }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>أنشئ في:</strong> {{ optional($loss->created_at)->format('Y-m-d H:i') }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>آخر تحديث:</strong> {{ optional($loss->updated_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
