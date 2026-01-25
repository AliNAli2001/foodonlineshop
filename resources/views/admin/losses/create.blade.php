@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">إضافة خسارة</h2>
        <a href="{{ route('admin.losses.index') }}" class="btn btn-secondary">العودة</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.losses.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">الكمية (اختياري)</label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}">
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label">النوع</label>
                        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">— اختر —</option>
                            <option value="shipping_costs" @selected(old('type')==='shipping_costs')>تكاليف الشحن</option>
                            <option value="general_costs" @selected(old('type')==='general_costs')>تكاليف عامة</option>
                            <option value="delivery_costs" @selected(old('type')==='delivery_costs')>تكاليف التوصيل</option>
                            <option value="other" @selected(old('type')==='other')>أخرى</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="reason" class="form-label">السبب</label>
                        <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
