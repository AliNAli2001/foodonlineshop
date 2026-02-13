@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">تعديل</h2>
        <a href="{{ route('admin.adjustments.index') }}" class="btn btn-secondary">العودة</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.adjustments.update', $adjustment->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">الكمية </label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $adjustment->quantity) }}" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="adjustment_type" class="form-label">نوع التعديل</label>
                        <select id="adjustment_type" name="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                            @php
                                $types = [
                                    'gain' => 'ربح',
                                    'loss' => 'خسارة',
                                ];
                            @endphp
                            <option value="">— اختر —</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('adjustment_type', $adjustment->adjustment_type)===$value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('adjustment_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="date" class="form-label">التاريخ </label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date',  optional($adjustment->date)->format('Y-m-d')) ?? now()->toDateString() }}" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="reason" class="form-label">السبب</label>
                        <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason', $adjustment->reason) }}</textarea>
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
