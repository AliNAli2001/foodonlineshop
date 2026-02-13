@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="h4 mb-0">تفاصيل التعديل</h2>
        </div>
        <div class="col-md-4 text-start text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.adjustments.index') }}" class="btn btn-secondary">العودة</a>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">بيانات التعديل</h5>
        </div>
        <div class="card-body">
            @php
                $types = [
                    'gain' => 'ربح',
                    'loss' => 'خسارة',
                ];
            @endphp
            <div class="row g-3">
                <div class="col-md-3">
                    <div><strong>المعرف:</strong> {{ $adjustment->id }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>المبلغ المالي:</strong> {{ $adjustment->quantity ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>النوع:</strong> <span class="badge {{ $adjustment->adjustment_type === 'gain' ? 'bg-success' : 'bg-danger' }}">{{ $types[$adjustment->adjustment_type] ?? $adjustment->adjustment_type }}</span></div>
                </div>
                <div class="col-md-3">
                    <div><strong>التاريخ:</strong> {{ optional($adjustment->date)->format('Y-m-d H:i') }}</div>
                </div>
                <div class="col-12">
                    <div><strong>السبب:</strong> {{ $adjustment->reason }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>أنشئ في:</strong> {{ optional($adjustment->created_at)->format('Y-m-d H:i') }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>آخر تحديث:</strong> {{ optional($adjustment->updated_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
