@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>إنشاء طلب جديد</h1>
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

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
                @csrf

                <!-- معلومات الزبون -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>معلومات الزبون (اختياري)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">اختر الزبون</label>

                            <div class="d-flex gap-2 align-items-center p-3">
                                <input type="text" id="selectedClientName" class="form-control" disabled
                                    placeholder="لم يتم اختيار زبون">

                                <input type="hidden" name="client_id" id="selectedClientId" value="{{ old('client_id') }}">

                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#clientModal">
                                    اختيار
                                </button>

                                <button type="button" class="btn btn-danger" id="clearClientBtn">
                                    مسح
                                </button>
                            </div>

                            @error('client_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <small class="form-text text-muted">
                                ابحث عن الزبون بالاسم أو رقم الهاتف، أو اتركه فارغًا لإنشاء طلب بدون زبون.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="client_name" class="form-label">اسم الزبون (اختياري)</label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                id="client_name" name="client_name" value="{{ old('client_name') }}">
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="client_phone_number" class="form-label">رقم هاتف الزبون (اختياري)</label>
                            <input type="text" class="form-control @error('client_phone_number') is-invalid @enderror"
                                id="client_phone_number" name="client_phone_number"
                                value="{{ old('client_phone_number') }}">
                            @error('client_phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- موقع الطلب وطرق التوصيل -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>موقع الطلب وطرق التوصيل</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">مصدر الطلب</label>
                            <select name="order_source" id="orderSource"
                                class="form-control @error('order_source') is-invalid @enderror" required>
                                <option value="">-- اختر --</option>
                                <option value="inside_city" {{ old('order_source') == 'inside_city' ? 'selected' : '' }}>
                                    داخل المدينة</option>
                                <option value="outside_city" {{ old('order_source') == 'outside_city' ? 'selected' : '' }}>
                                    خارج المدينة</option>
                            </select>
                            @error('order_source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">طريقة التوصيل</label>
                            <select name="delivery_method" id="deliveryMethod"
                                class="form-control @error('delivery_method') is-invalid @enderror" required>
                                <option value="">-- اختر --</option>
                                <option value="delivery" data-source="inside_city">توصيل (اختر موظف التوصيل)</option>
                                <option value="hand_delivered" data-source="inside_city">تسليم يدوي (داخل المدينة)</option>
                                <option value="shipping" data-source="outside_city">شحن (خارج المدينة)</option>
                            </select>
                            @error('delivery_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- تفاصيل العنوان -->
                        <div class="mb-3" id="addressDetailsGroup">
                            <label class="form-label">تفاصيل العنوان <span class="text-danger">*</span></label>
                            <textarea name="address_details" class="form-control @error('address_details') is-invalid @enderror" rows="3"
                                required>{{ old('address_details') }}</textarea>
                            @error('address_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row" id="coordinatesGroup">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">خط العرض (اختياري)</label>
                                    <input type="number" name="latitude" step="0.000001"
                                        class="form-control @error('latitude') is-invalid @enderror"
                                        value="{{ old('latitude') }}" placeholder="-90 إلى 90">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">خط الطول (اختياري)</label>
                                    <input type="number" name="longitude" step="0.000001"
                                        class="form-control @error('longitude') is-invalid @enderror"
                                        value="{{ old('longitude') }}" placeholder="-180 إلى 180">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- خريطة لاختيار الموقع -->
                        <div class="mb-3" id="mapGroup">
                            <label class="form-label">اختر الموقع على الخريطة (اختياري)</label>
                            <div id="map" style="height: 300px;"></div>
                        </div>

                        <!-- ملاحظات الشحن -->
                        <div class="mb-3" id="shippingNotesGroup" style="display: none;">
                            <label class="form-label">ملاحظات الشحن (اختياري)</label>
                            <textarea name="shipping_notes" class="form-control @error('shipping_notes') is-invalid @enderror" rows="2">{{ old('shipping_notes') }}</textarea>
                            @error('shipping_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات إدارية عن الزبون (اختياري)</label>
                            <textarea name="admin_order_client_notes" class="form-control @error('admin_order_client_notes') is-invalid @enderror"
                                rows="2">{{ old('admin_order_client_notes') }}</textarea>
                            @error('admin_order_client_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">ملاحظات داخلية عن الزبون أو الطلب.</small>
                        </div>
                    </div>
                </div>

                <!-- عناصر الطلب -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>عناصر الطلب</h5>
                    </div>
                    <div class="card-body">
                        <template id="orderItemTemplate">
                            <div class="order-item mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">المنتج</label>

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="text" class="form-control selected-product-name" disabled
                                                placeholder="لم يتم اختيار منتج">


                                            <input type="hidden" class="selected-product-id"
                                                name="products[__INDEX__][product_id]" required>

                                            <button type="button" class="btn btn-primary open-product-modal">
                                                اختيار
                                            </button>

                                            <button type="button" class="btn btn-danger clear-product-btn">
                                                مسح
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">الكمية</label>
                                        <input type="number" class="form-control quantity-input" min="1"
                                            value="1" required>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger w-100 remove-item">إزالة</button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div id="itemsContainer">
                            <div class="order-item mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">المنتج</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="text" class="form-control selected-product-name" disabled
                                                placeholder="لم يتم اختيار منتج">

                                            <input type="hidden" class="selected-product-id"
                                                name="products[0][product_id]" required>

                                            <button type="button"
                                                class="btn btn-primary open-product-modal">اختيار</button>
                                            <button type="button" class="btn btn-danger clear-product-btn">مسح</button>
                                        </div>

                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">الكمية</label>
                                        <input type="number" name="products[0][quantity]"
                                            class="form-control quantity-input" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger w-100 remove-item"
                                            style="display:none;">إزالة</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary" id="addItemBtn">+ إضافة عنصر آخر</button>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">إنشاء الطلب</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5>ملخص الطلب</h5>
                </div>
                <div class="card-body">
                    <p><strong>إجمالي العناصر:</strong> <span id="totalItems">0</span></p>
                    <p><strong>الإجمالي:</strong> $<span id="totalAmount">0.00</span></p>
                    <hr>
                    <p class="text-muted small">سيتم إنشاء هذا الطلب بحالة <strong>تم التأكيد</strong>.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title">اختيار زبون</h5>
                    <button type="button" class="btn btn-close m-0" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="text" id="clientSearchInput" class="form-control mb-3"
                        placeholder="ابحث بالاسم أو رقم الهاتف...">

                    <div id="clientsList" class="list-group">
                        <div class="text-muted text-center">ابدأ بالبحث...</div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title">اختيار منتج</h5>
                    <button type="button" class="btn btn-close m-0" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="text" id="productSearchInput" class="form-control mb-3"
                        placeholder="ابحث باسم المنتج...">

                    <div id="productsList" class="list-group">
                        <div class="text-muted text-center">ابدأ بالبحث...</div>
                    </div>
                </div>

            </div>
        </div>
    </div>






    <style>
        /* Style for unavailable products in Select2 dropdown */
        .select2-results__option[aria-disabled="true"] {
            color: #999 !important;
            background-color: #f5f5f5 !important;
            font-style: italic;
            cursor: not-allowed !important;
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }
    </style>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>


    <!-- Leaflet Control Geocoder -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>


    <script>
        // Function to initialize Select2 on product select elements
        function initializeProductSelect($element) {
            $element.select2({
                theme: 'bootstrap-5',
                placeholder: '-- ابحث عن المنتج --',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.orders.autocomplete.products') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            exclude: Array.from(getSelectedProductIds())
                        };
                    },
                    processResults: data => ({
                        results: data.results
                    }),
                    cache: true
                }
            });

            $element.on('select2:select', updateSummary);
        }


        // function initializeProductSelect($element) {
        //     $element.select2({
        //         theme: 'bootstrap-5',
        //         placeholder: '-- ابحث عن المنتج --',
        //         allowClear: true,
        //         ajax: {
        //             url: '{{ route('admin.orders.autocomplete.products') }}',
        //             dataType: 'json',
        //             delay: 250,
        //             data: function(params) {
        //                 return {
        //                     q: params.term
        //                 };
        //             },
        //             processResults: function(data) {
        //                 return {
        //                     results: data.results
        //                 };
        //             },
        //             cache: true
        //         },
        //         minimumInputLength: 0,
        //         templateResult: formatProductOption,
        //         templateSelection: formatProductSelection
        //     });

        //     // Store price and stock data when product is selected
        //     $element.on('select2:select', function(e) {
        //         const data = e.params.data;
        //         $(this).data('price', data.price);
        //         $(this).data('available-stock', data.available_stock);
        //         updateSummary();
        //     });
        // }

        // Format product option in dropdown (with availability styling)
        function formatProductOption(product) {
            if (!product.id) {
                return product.text;
            }

            const $option = $(
                '<span style="' + (product.disabled ? 'color: #999; font-style: italic;' : '') + '">' +
                product.text +
                (product.disabled ? ' (غير متوفر)' : '') +
                '</span>'
            );

            return $option;
        }

        // Format selected product
        function formatProductSelection(product) {
            return product.text || product.id;
        }

        // DOM Elements
        const orderSource = document.getElementById('orderSource');
        const deliveryMethod = document.getElementById('deliveryMethod');
        const addressDetailsGroup = document.getElementById('addressDetailsGroup');
        const coordinatesGroup = document.getElementById('coordinatesGroup');
        const mapGroup = document.getElementById('mapGroup');
        const shippingNotesGroup = document.getElementById('shippingNotesGroup');

        // Update Delivery Method Options based on Order Source
        function updateDeliveryOptions() {
            const source = orderSource.value;
            deliveryMethod.innerHTML =
                '<option value="">-- اختر --</option><option value="delivery" data-source="inside_city">توصيل (اختر عامل توصيل)</option><option value="hand_delivered" data-source="inside_city">استلام باليد</option><option value="shipping" data-source="outside_city">شحن (خارج المدينة)</option>';
            const options = deliveryMethod.querySelectorAll('option[data-source]');

            // Reset all options
            deliveryMethod.innerHTML = '<option value="">-- اختر --</option>';

            options.forEach(option => {
                if (!source || option.dataset.source === source) {
                    deliveryMethod.appendChild(option.cloneNode(true));
                }
            });

            // Restore selected value if valid
            const oldValue = '{{ old('delivery_method') }}';
            if (oldValue && deliveryMethod.querySelector(`option[value="${oldValue}"]`)) {
                deliveryMethod.value = oldValue;
            }

            updateFieldVisibility();
        }

        // Show/Hide fields based on delivery method
        function updateFieldVisibility() {
            const method = deliveryMethod.value;

            // Address, Coordinates & Map: Hide only for hand_delivered
            if (method === 'hand_delivered') {
                addressDetailsGroup.style.display = 'none';
                coordinatesGroup.style.display = 'none';
                mapGroup.style.display = 'none';

                // Remove required attribute
                addressDetailsGroup.querySelector('textarea').removeAttribute('required');
            } else {
                addressDetailsGroup.style.display = 'block';
                coordinatesGroup.style.display = 'flex';
                mapGroup.style.display = 'block';

                // Re-add required
                addressDetailsGroup.querySelector('textarea').setAttribute('required', 'required');

                // Refresh map size if needed
                if (map) {
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 100);
                }
            }

            // Shipping Notes: Show only for shipping
            shippingNotesGroup.style.display = (method === 'shipping') ? 'block' : 'none';
        }

        // Event Listeners
        orderSource.addEventListener('change', () => {
            deliveryMethod.value = '';
            updateDeliveryOptions();
        });

        deliveryMethod.addEventListener('change', updateFieldVisibility);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            updateDeliveryOptions();
            updateFieldVisibility();
        });

        // Initialize Leaflet Map
        let map;
        let marker;
        document.addEventListener('DOMContentLoaded', () => {
            const defaultLat = {{ old('latitude', 33.51307) }};
            const defaultLng = {{ old('longitude', 36.309581) }};
            const hasInitialCoords = '{{ old('latitude') }}' && '{{ old('longitude') }}';

            map = L.map('map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            if (hasInitialCoords) {
                marker = L.marker([defaultLat, defaultLng]).addTo(map);
            }


            // ✅ ADD SEARCH CONTROL
            const geocoder = L.Control.geocoder({
                    defaultMarkGeocode: false,
                    placeholder: 'ابحث عن مدينة أو عنوان...'
                })
                .on('markgeocode', function(e) {
                    const latlng = e.geocode.center;

                    map.setView(latlng, 14);

                    if (marker) {
                        map.removeLayer(marker);
                    }

                    marker = L.marker(latlng).addTo(map);

                    // Fill inputs
                    document.querySelector('input[name="latitude"]').value = latlng.lat.toFixed(6);
                    document.querySelector('input[name="longitude"]').value = latlng.lng.toFixed(6);
                })
                .addTo(map);
            map.on('click', function(e) {
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker(e.latlng).addTo(map);
                document.querySelector('input[name="latitude"]').value = e.latlng.lat.toFixed(6);
                document.querySelector('input[name="longitude"]').value = e.latlng.lng.toFixed(6);
            });
        });

        // === Order Items Logic ===
        function getSelectedProductIds() {
            const selectedIds = new Set();
            $('.selected-product-id').each(function() {
                const val = $(this).val();
                if (val) selectedIds.add(val);
            });
            return selectedIds;
        }


        function updateSummary() {
            let total = 0,
                count = 0;
            $('.order-item').each(function() {
                const productId = $(this).find('.selected-product-id').val();

                const quantity = parseInt($(this).find('.quantity-input').val()) || 0;
                const price = parseFloat($(this).data('price')) || 0;

                if (productId && quantity > 0) {

                    total += price * quantity;
                    count++;
                }
            });
            $('#totalItems').text(count);
            $('#totalAmount').text(total.toFixed(2));
        }
        let itemCount = 1;

        $('#addItemBtn').on('click', function() {
            const template = document.getElementById('orderItemTemplate');
            const clone = template.content.cloneNode(true);
            const $row = $(clone).find('.order-item');

            // Set unique names
            $row.find('.selected-product-id').attr('name', `products[${itemCount}][product_id]`);
            $row.find('.quantity-input').attr('name', `products[${itemCount}][quantity]`);


            $('#itemsContainer').append($row);

            attachEventListeners();
            updateSummary();

            itemCount++;
        });


        function attachEventListeners() {
            // Attach quantity input listeners
            $('.quantity-input').off('input').on('input', updateSummary);

            // Attach remove button listeners
            $('.remove-item').off('click').on('click', function() {
                const $item = $(this).closest('.order-item');



                $item.remove();
                updateSummary();
            });
        }

        // Initial setup
        $(document).ready(function() {
            attachEventListeners();
            updateSummary();
        });


        // ================= CLIENT MODAL SEARCH =================

        // Search clients via AJAX
        $('#clientSearchInput').on('input', function() {
            let query = $(this).val().trim();

            if (query.length < 1) {
                $('#clientsList').html('<div class="text-muted text-center">ابدأ بالبحث...</div>');
                return;
            }

            $.ajax({
                url: '{{ route('admin.orders.autocomplete.clients') }}',
                data: {
                    q: query
                },
                success: function(data) {
                    let html = '';

                    if (data.results.length === 0) {
                        html = '<div class="text-muted text-center">لا توجد نتائج</div>';
                    } else {
                        data.results.forEach(client => {
                            html += `
                        <button type="button"
                            class="list-group-item list-group-item-action select-client"
                            data-id="${client.id}"
                            data-name="${client.text}">
                            ${client.text}
                        </button>
                    `;
                        });
                    }

                    $('#clientsList').html(html);
                },
                error: function() {
                    $('#clientsList').html(
                        '<div class="text-danger text-center">حدث خطأ أثناء البحث</div>');
                }
            });
        });

        // Select client from modal
        $(document).on('click', '.select-client', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#selectedClientId').val(id);
            $('#selectedClientName').val(name);

            $('#clientModal').modal('hide');
        });

        // Clear selected client
        $('#clearClientBtn').on('click', function() {
            $('#selectedClientId').val('');
            $('#selectedClientName').val('');
        });


        let activeProductRow = null;

        // Open product modal
        $(document).on('click', '.open-product-modal', function() {
            activeProductRow = $(this).closest('.order-item');
            $('#productSearchInput').val('');
            $('#productsList').html('<div class="text-muted text-center">ابدأ بالبحث...</div>');
            $('#productModal').modal('show');
        });

        $('#productSearchInput').on('input', function() {
            let query = $(this).val().trim();

            if (query.length < 1) {
                $('#productsList').html('<div class="text-muted text-center">ابدأ بالبحث...</div>');
                return;
            }

            $.ajax({
                url: '{{ route('admin.orders.autocomplete.products') }}',
                data: {
                    q: query
                },
                success: function(data) {
                    let html = '';

                    if (data.results.length === 0) {
                        html = '<div class="text-muted text-center">لا توجد نتائج</div>';
                    } else {
                        const selectedIds = getSelectedProductIds();

                        data.results.forEach(product => {
                            const isSelected = selectedIds.has(String(product.id));

                            html += `
        <button type="button"
            class="list-group-item list-group-item-action select-product ${isSelected ? 'disabled' : ''}"
            data-id="${product.id}"
            data-name="${product.text}"
            data-price="${product.price}"
            ${isSelected ? 'disabled' : ''}>
            ${product.text} ${isSelected ? '(تم اختياره مسبقًا)' : ''}
        </button>
    `;
                        });

                    }

                    $('#productsList').html(html);
                },
                error: function() {
                    $('#productsList').html(
                        '<div class="text-danger text-center">حدث خطأ أثناء البحث</div>');
                }
            });
        });

        $(document).on('click', '.select-product', function() {
            if ($(this).hasClass('disabled')) return;

            if (!activeProductRow) return;

            const id = $(this).data('id');
            const name = $(this).data('name');
            const price = $(this).data('price');

            // Prevent duplicate selection
            const selectedIds = getSelectedProductIds();
            if (selectedIds.has(String(id))) {
                alert('هذا المنتج مضاف مسبقًا إلى الطلب.');
                return;
            }

            activeProductRow.find('.selected-product-id').val(id);
            activeProductRow.find('.selected-product-name').val(name);
            activeProductRow.data('price', price);

            $('#productModal').modal('hide');
            updateSummary();
        });

        $(document).on('click', '.clear-product-btn', function() {
            const row = $(this).closest('.order-item');
            row.find('.selected-product-id').val('');
            row.find('.selected-product-name').val('');
            row.data('price', 0);
            updateSummary();
        });
    </script>
@endsection
