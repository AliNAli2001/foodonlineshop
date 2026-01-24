@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="h4 mb-0">تفاصيل البضائع التالفة</h2>
        </div>
        <div class="col-md-4 text-start text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.damaged-goods.index') }}" class="btn btn-secondary">العودة</a>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات البضائع التالفة</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div><strong>المنتج:</strong> {{ $damagedGoods->product->name_ar }}</div>
                </div>
                <div class="col-md-3">
                    <div><strong>الكمية:</strong> {{ $damagedGoods->quantity }}</div>
                </div>
                <div class="col-md-3">
                    <div>
                        <strong>المصدر:</strong>
                        <span class="badge bg-info">
                            {{ \App\Models\DamagedGoods::SOURCES[$damagedGoods->source] ?? $damagedGoods->source }}
                        </span>
                    </div>
                </div>

                <div class="col-12">
                    <div><strong>السبب:</strong> {{ $damagedGoods->reason }}</div>
                </div>

                @if($damagedGoods->source === 'inventory')
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <strong>الدفعة المرتبطة:</strong>
                            @if($damagedGoods->inventoryBatch)
                                <a href="{{ route('admin.inventory.show', $damagedGoods->inventoryBatch) }}" class="link-primary">
                                    دفعة رقم {{ $damagedGoods->inventoryBatch->batch_number }}
                                </a>
                                <span class="text-muted small">(المعرف: {{ $damagedGoods->inventoryBatch->id }})</span>
                            @else
                                <span class="text-muted">غير متوفر</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if($damagedGoods->inventoryBatch)
                            <div>
                                <strong>تاريخ الانتهاء:</strong>
                                {{ $damagedGoods->inventoryBatch->expiry_date ?? '—' }}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="col-md-6">
                    <div><strong>التاريخ:</strong> {{ $damagedGoods->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>آخر تحديث:</strong> {{ $damagedGoods->updated_at->format('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
