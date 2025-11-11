@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>تعديل المخزون: {{ $product->name_en }}</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">العودة</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.inventory.update', $inventory->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">المنتج</label>
                        <input type="text" class="form-control" value="{{ $product->name_en }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">كمية المخزون</label>
                        <input type="number" name="stock_quantity"
                            class="form-control @error('stock_quantity') is-invalid @enderror"
                            value="{{ old('stock_quantity', $inventory->stock_quantity) }}" required>
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">تنبيه الحد الأدنى للكمية</label>
                        <input type="number" name="minimum_alert_quantity"
                            class="form-control @error('minimum_alert_quantity') is-invalid @enderror"
                            value="{{ old('minimum_alert_quantity', $inventory->minimum_alert_quantity) }}" required>
                        @error('minimum_alert_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">رقم الدفعة</label>
                        <input type="text" name="batch_number"
                            class="form-control @error('batch_number') is-invalid @enderror"
                            value="{{ old('batch_number', $inventory->batch_number) }}" required>
                        @error('batch_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">سعر التكلفة</label>
                        <input type="number" name="cost_price" step="0.001"
                            class="form-control @error('cost_price') is-invalid @enderror"
                            value="{{ old('cost_price', $inventory->cost_price) }}" required>
                        @error('cost_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">تاريخ الانتهاء</label>
                        <input type="date" name="expiry_date"
                            class="form-control @error('expiry_date') is-invalid @enderror"
                            value="{{ old('expiry_date', $inventory->expiry_date->format('Y-m-d')) }}">
                        @error('expiry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">سبب التعديل</label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">تحديث المخزون</button>
                        <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
