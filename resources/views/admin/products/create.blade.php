@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>إضافة منتج جديد</h1>
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

                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name_ar" class="form-label">الاسم (بالعربية)</label>
                                <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                    id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                                @error('name_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name_en" class="form-label">الاسم (بالإنجليزية)</label>
                                <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                    id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                                @error('name_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="description_ar" class="form-label">الوصف (بالعربية)</label>
                                <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar"
                                    rows="3">{{ old('description_ar') }}</textarea>
                                @error('description_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="description_en" class="form-label">الوصف (بالإنجليزية)</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en"
                                    rows="3">{{ old('description_en') }}</textarea>
                                @error('description_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="selling_price" class="form-label">السعر</label>
                                <input type="number" class="form-control @error('selling_price') is-invalid @enderror"
                                    id="selling_price" name="selling_price" value="{{ old('selling_price') }}" step="0.001" required>
                                @error('selling_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="max_order_item" class="form-label">الحد الأقصى للطلب</label>
                                <input type="number" class="form-control @error('max_order_item') is-invalid @enderror"
                                    id="max_order_item" name="max_order_item"
                                    value="{{ old('max_order_item', $maxOrderItems) }}">
                                @error('max_order_item')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- تفعيل حقول المخزون -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_inventory"
                                    name="enable_inventory" value="1" {{ old('enable_inventory', 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_inventory">
                                    تفعيل حقول المخزون
                                </label>
                            </div>
                        </div>

                        <!-- حقول المخزون -->
                        <div id="inventory_fields">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="stock_quantity" class="form-label">كمية المخزون</label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                        id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity') }}"
                                        required>
                                    @error('stock_quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="minimum_alert_quantity" class="form-label">حد التنبيه الأدنى</label>
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
                                        id="cost_price" name="cost_price" value="{{ old('cost_price') }}"
                                        step="0.001" required>
                                    @error('cost_price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="categories" class="form-label">التصنيفات</label>
                            <div>
                                @foreach ($categories as $category)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]"
                                            id="category_{{ $category->id }}" value="{{ $category->id }}">
                                        <label class="form-check-label"
                                            for="category_{{ $category->id }}">{{ $category->name_ar }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">صور المنتج</label>
                            <input type="file" class="form-control @error('images') is-invalid @enderror"
                                id="images" name="images[]" accept="image/*" multiple>
                            <small class="form-text text-muted">يمكنك اختيار عدة صور، وستُعتبر الصورة الأولى هي الصورة
                                الرئيسية.</small>
                            @error('images')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured"
                                    value="1">
                                <label class="form-check-label" for="featured">
                                    منتج مميز
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">إنشاء المنتج</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- جافاسكريبت لتبديل ظهور حقول المخزون -->
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const enableInventoryCheckbox = document.getElementById('enable_inventory');
            const inventoryFieldsContainer = document.getElementById('inventory_fields');
            const inventoryInputs = inventoryFieldsContainer.querySelectorAll('input');

            function toggleInventoryFields() {
                const isEnabled = enableInventoryCheckbox.checked;
                inventoryInputs.forEach(input => {
                    input.disabled = !isEnabled;
                });
                inventoryFieldsContainer.style.display = isEnabled ? 'block' : 'none';
            }

            // الحالة عند التحميل
            toggleInventoryFields();

            // التبديل عند تغيير حالة التفعيل
            enableInventoryCheckbox.addEventListener('change', toggleInventoryFields);
        });
    </script>
@endsection
@endsection
