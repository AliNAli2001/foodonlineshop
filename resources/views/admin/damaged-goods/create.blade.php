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
                        <small class="form-text text-muted">
                            سيتم طرح الكمية من المخزون و ستظهر العملية في سجل المخزون المحدد.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المنتج</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" id="product_search" class="form-control" placeholder="ابحث عن المنتج..." autocomplete="off">
                            <input type="hidden" name="product_id" id="product_id" required>
                            <input type="text" id="selected_product_name" class="form-control" disabled placeholder="لم يتم اختيار منتج">
                            <button type="button" class="btn btn-danger" id="clear_product_btn">مسح</button>
                        </div>
                        <div id="product_results" class="list-group mt-2" style="display: none; max-height: 300px; overflow-y: auto;"></div>
                        @error('product_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const productSearchInput = $('#product_search');
            const productIdInput = $('#product_id');
            const selectedProductName = $('#selected_product_name');
            const productResults = $('#product_results');
            const clearProductBtn = $('#clear_product_btn');
          
            const inventoryField = $('#inventory-field');
            const inventorySelect = inventoryField.find('select[name="inventory_batch_id"]');

            // Route لجلب دفعات المنتج
            const getBatchesUrlTemplate = '{{ route('admin.products.batches', ':product') }}';

            // Product search autocomplete
            let searchTimeout;
            productSearchInput.on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();

                if (query.length < 1) {
                    productResults.hide().html('');
                    return;
                }

                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: '{{ route('admin.damaged-goods.autocomplete.products') }}',
                        data: { q: query },
                        success: function(data) {
                            let html = '';
                            if (data.results.length === 0) {
                                html = '<div class="list-group-item text-muted">لا توجد نتائج</div>';
                            } else {
                                data.results.forEach(product => {
                                    html += `
                                        <button type="button" class="list-group-item list-group-item-action select-product"
                                            data-id="${product.id}"
                                            data-name="${product.text}">
                                            ${product.text}
                                        </button>
                                    `;
                                });
                            }
                            productResults.html(html).show();
                        },
                        error: function() {
                            productResults.html('<div class="list-group-item text-danger">حدث خطأ أثناء البحث</div>').show();
                        }
                    });
                }, 300);
            });

            // Select product from results
            $(document).on('click', '.select-product', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                productIdInput.val(id);
                selectedProductName.val(name);
                productSearchInput.val('');
                productResults.hide().html('');

                // Load batches after product selection
                loadBatches();
            });

            // Clear product selection
            clearProductBtn.on('click', function() {
                productIdInput.val('');
                selectedProductName.val('');
                productSearchInput.val('');
                productResults.hide().html('');
                resetBatches();
            });

            // Hide results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#product_search, #product_results').length) {
                    productResults.hide();
                }
            });

            function resetBatches() {
                inventorySelect.html('<option value="">-- اختر دفعة المخزون --</option>');
                inventoryField.hide();
                inventorySelect.prop('required', false);
            }

            function loadBatches() {
                const productId = productIdInput.val();
                

                // نظف الدفعات القديمة دائماً
                resetBatches();

                // لا تجلب الدفعات إلا لو يوجد منتج
                if (!productId) {
                    return;
                }

                const url = getBatchesUrlTemplate.replace(':product', productId);

                $.ajax({
                    url: url,
                    success: function(data) {
                        if (data.inventory_batches && data.inventory_batches.length > 0) {
                            data.inventory_batches.forEach(batch => {
                                let optionText = '';

                                if (batch.batch_number) {
                                    optionText += 'الفاتورة: ' + batch.batch_number + ' - ';
                                }

                                optionText += 'تاريخ الانتهاء: ' + (batch.expiry_date.slice(0, 10) ?? 'N/A') +
                                    ' - الكمية المتاحة في هذه الدفعة: ' + batch.available_quantity;

                                inventorySelect.append(
                                    $('<option></option>').val(batch.id).text(optionText)
                                );
                            });

                            inventoryField.show();
                            inventorySelect.prop('required', true);
                        } else {
                            inventoryField.show();
                            inventorySelect.prop('required', false);
                        }
                    },
                    error: function(error) {
                        console.error('Error loading batches:', error);
                        resetBatches();
                    }
                });
            }

            

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
