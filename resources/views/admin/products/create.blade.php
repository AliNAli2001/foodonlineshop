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
                                    id="selling_price" name="selling_price" value="{{ old('selling_price') }}"
                                    step="0.001" required>
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
                        <!-- تفعيل المخزون المبدئي -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_initial_stock"
                                    name="enable_initial_stock" value="1"
                                    {{ old('enable_initial_stock') ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_initial_stock">
                                    إضافة مخزون مبدئي
                                </label>
                            </div>
                        </div>

                        <!-- حقول المخزون المبدئي -->
                        <div id="inventory_fields">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">كمية المخزون المبدئية</label>
                                    <input type="number"
                                        class="form-control @error('initial_stock_quantity') is-invalid @enderror"
                                        name="initial_stock_quantity" value="{{ old('initial_stock_quantity') }}">
                                    @error('initial_stock_quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">حد التنبيه الأدنى</label>
                                    <input type="number"
                                        class="form-control @error('minimum_alert_quantity') is-invalid @enderror"
                                        name="minimum_alert_quantity"
                                        value="{{ old('minimum_alert_quantity', $generalMinimumAlertQuantity) }}">
                                    @error('minimum_alert_quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">رقم الدفعة</label>
                                    <input type="text"
                                        class="form-control @error('initial_batch_number') is-invalid @enderror"
                                        name="initial_batch_number" value="{{ old('initial_batch_number') }}">
                                    @error('initial_batch_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">تاريخ الانتهاء</label>
                                    <input type="date"
                                        class="form-control @error('initial_expiry_date') is-invalid @enderror"
                                        name="initial_expiry_date" value="{{ old('initial_expiry_date') }}">
                                    @error('initial_expiry_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">سعر التكلفة</label>
                                    <input type="number" step="0.001"
                                        class="form-control @error('initial_cost_price') is-invalid @enderror"
                                        name="initial_cost_price" value="{{ old('initial_cost_price') }}">
                                    @error('initial_cost_price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="company_id" class="form-label">الشركة</label>
                                <select name="company_id" id="company_id"
                                    class="form-control @error('company_id') is-invalid @enderror">
                                    <option value="">-- اختر الشركة --</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name_ar }} - {{ $company->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">التصنيف</label>
                            <select name="category_id" id="category_id"
                                class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- اختر التصنيف --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name_ar }} - {{ $category->name_en }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label for="tags" class="form-label">الوسوم</label>
                            <div>
                                @foreach ($tags as $tag)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tags[]"
                                            id="tag_{{ $tag->id }}" value="{{ $tag->id }}">
                                        <label class="form-check-label"
                                            for="tag_{{ $tag->id }}">{{ $tag->name_ar }}</label>
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
            const enableInventoryCheckbox = document.getElementById('enable_initial_stock');
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
