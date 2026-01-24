@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>تسجيل البضائع التالفة</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.damaged-goods.index') }}" class="btn btn-secondary">العودة</a>
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
                <form action="{{ route('admin.damaged-goods.store') }}" method="POST">
                    @csrf
<div class="mb-3">
                        <label class="form-label">المصدر</label>
                        <select name="source" id="source" class="form-control @error('source') is-invalid @enderror"
                            required>
                            <option value="">-- اختر المصدر --</option>
                            <option value="inventory" {{ old('source') == 'inventory' ? 'selected' : '' }}>المخزون (سيتم
                                إنشاء حركة مخزون)</option>
                            <option value="invoice" {{ old('source') == 'invoice' ? 'selected' : '' }}>الفاتورة</option>
                        </select>
                        @error('source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            اختيار "المخزون" سينشئ تلقائيًا سجل حركة مخزون ويخصم من المخزون الحالي.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المنتج</label>
                        <select name="product_id" id="product_id"
                            class="form-control @error('product_id') is-invalid @enderror" required>
                            <option value="">-- اختر المنتج --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="inventory-field" style="display: none;">
                        <label class="form-label">دفعة المخزون</label>
                        <select name="inventory_batch_id" class="form-control @error('inventory_batch_id') is-invalid @enderror">
                            <option value="">-- اختر دفعة المخزون --</option>
                        </select>
                        @error('inventory_batch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="mb-3">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                            value="{{ old('quantity') }}" min="1" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    

                    <div class="mb-3">
                        <label class="form-label">السبب</label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">تسجيل البضائع التالفة</button>
                        <a href="{{ route('admin.damaged-goods.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_id');
            const sourceSelect = document.getElementById('source');
            const inventoryField = document.getElementById('inventory-field');
            const inventorySelect = inventoryField.querySelector('select[name="inventory_batch_id"]');

            // Route لجلب دفعات المنتج
            const getBatchesUrlTemplate = '{{ route('admin.products.batches', ':product') }}';

            function resetBatches() {
                inventorySelect.innerHTML = '<option value="">-- اختر دفعة المخزون --</option>';
                inventoryField.style.display = 'none';
                inventorySelect.required = false;
            }

            function loadBatches() {
                const productId = productSelect.value;
                const source = sourceSelect.value;

                // نظف الدفعات القديمة دائماً
                resetBatches();

                // لا تجلب الدفعات إلا لو المصدر مخزون ويوجد منتج
                if (source !== 'inventory' || !productId) {
                    return;
                }

                const url = getBatchesUrlTemplate.replace(':product', productId);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.inventory_batches && data.inventory_batches.length > 0) {
                            data.inventory_batches.forEach(batch => {
                                let optionText = '';

                                if (batch.batch_number) {
                                    optionText += 'Batch: ' + batch.batch_number + ' - ';
                                }

                                optionText +=
                                    'Expiry: ' + (batch.expiry_date ?? 'N/A') +
                                    ' - Stock: ' + batch.stock_quantity;

                                const option = document.createElement('option');
                                option.value = batch.id;
                                option.textContent = optionText;
                                inventorySelect.appendChild(option);
                            });

                            inventoryField.style.display = 'block';
                            inventorySelect.required = true;
                        } else {
                            // لا توجد دفعات لهذا المنتج
                            inventoryField.style.display = 'block';
                            inventorySelect.required = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading batches:', error);
                        resetBatches();
                    });
            }

            // عند تغيير المنتج → تحديث الدفعات
            productSelect.addEventListener('change', loadBatches);

            // عند تغيير المصدر → إظهار/إخفاء الدفعات
            sourceSelect.addEventListener('change', loadBatches);

            // تحميل مبدئي إذا كان هناك old values
            loadBatches();
        });
    </script>


    {{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('product_id');
        const sourceSelect = document.getElementById('source');
        const inventoryField = document.getElementById('inventory-field');
        const inventorySelect = inventoryField.querySelector('select[name="inventory_batch_id"]');
        const getInventoriesUrlTemplate = '{{ route("admin.damaged-goods.product-inventories", ":product") }}';

        let productId = productSelect.value;
        let source = sourceSelect.value;

        function loadInventories() {
            if (source === 'inventory' && productId) {
                const url = getInventoriesUrlTemplate.replace(':product', productId);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        inventorySelect.innerHTML = '<option value="">-- Select Inventory Batch --</option>';
                        data.forEach(value => {
                            let optionText = '';
                            if (value.batch_number) {
                                optionText += 'Batch: ' + value.batch_number + ' - ';
                            }
                            optionText += 'Expiry: ' + (value.expiry_date ? value.expiry_date : 'N/A') + ' - Stock: ' + value.stock_quantity;
                            const option = document.createElement('option');
                            option.value = value.id;
                            option.textContent = optionText;
                            inventorySelect.appendChild(option);
                        });
                        inventoryField.style.display = 'block';
                        inventorySelect.required = true;
                    })
                    .catch(error => {
                        inventoryField.style.display = 'none';
                    });
            } else {
                inventoryField.style.display = 'none';
                inventorySelect.required = false;
            }
        }

        productSelect.addEventListener('change', function() {
            productId = this.value;
            loadInventories();
        });

        sourceSelect.addEventListener('change', function() {
            source = this.value;
            loadInventories();
        });

        // Initial load if old values
        loadInventories();
    });
</script> --}}
@endsection
