@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>إضافة مخزون للمنتج {{ $product->name }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.inventory.store', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="stock_quantity" class="form-label">كمية المخزون</label>
                                <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                    id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity') }}" required>
                                @error('stock_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="minimum_alert_quantity" class="form-label">تنبيه الحد الأدنى للكمية</label>
                                <input type="number"
                                    class="form-control @error('minimum_alert_quantity') is-invalid @enderror"
                                    id="minimum_alert_quantity" name="minimum_alert_quantity"
                                    value="{{ old('minimum_alert_quantity', $generalMinimumAlertQuantity) }}" required>
                                @error('minimum_alert_quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label">
                                    تاريخ الانتهاء
                                </label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror"
                                    id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}" required>
                                @error('expiry_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="batch_number" class="form-label">
                                    رقم الدفعة
                                </label>
                                <input type="text" class="form-control @error('batch_number') is-invalid @enderror"
                                    id="batch_number" name="batch_number" value="{{ old('batch_number') }}" required>
                                @error('batch_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="cost_price" class="form-label">سعر التكلفة</label>
                                <input type="number" class="form-control @error('cost_price') is-invalid @enderror"
                                    id="cost_price" name="cost_price" value="{{ old('cost_price') }}" step="0.001"
                                    required>
                                @error('cost_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary">إنشاء المخزون</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
