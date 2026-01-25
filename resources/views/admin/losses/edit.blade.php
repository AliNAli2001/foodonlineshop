@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">تعديل خسارة</h2>
        <a href="{{ route('admin.losses.index') }}" class="btn btn-secondary">العودة</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.losses.update', $loss->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">الكمية (اختياري)</label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $loss->quantity) }}">
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label">النوع</label>
                        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                            @php
                                $types = [
                                    'shipping_costs' => 'تكاليف الشحن',
                                    'general_costs' => 'تكاليف عامة',
                                    'delivery_costs' => 'تكاليف التوصيل',
                                    'other' => 'أخرى',
                                ];
                            @endphp
                            <option value="">— اختر —</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $loss->type)===$value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="reason" class="form-label">السبب</label>
                        <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason', $loss->reason) }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
