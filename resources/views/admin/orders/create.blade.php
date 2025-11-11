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

                <!-- معلومات العميل -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>معلومات العميل (اختياري)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">اختر العميل</label>
                            <select name="client_id" class="form-control @error('client_id') is-invalid @enderror">
                                <option value="">-- بدون عميل (طلب إداري) --</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->first_name }} {{ $client->last_name }} ({{ $client->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">اتركه فارغًا لإنشاء طلب بدون عميل.</small>
                        </div>
                        <div class="mb-3">
                            <label for="client_name" class="form-label">اسم العميل (اختياري)</label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                id="client_name" name="client_name" value="{{ old('client_name') }}">
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="client_phone_number" class="form-label">رقم هاتف العميل (اختياري)</label>
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
                            <label class="form-label">ملاحظات إدارية عن العميل (اختياري)</label>
                            <textarea name="admin_order_client_notes" class="form-control @error('admin_order_client_notes') is-invalid @enderror"
                                rows="2">{{ old('admin_order_client_notes') }}</textarea>
                            @error('admin_order_client_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">ملاحظات داخلية عن العميل أو الطلب.</small>
                        </div>
                    </div>
                </div>

                <!-- عناصر الطلب -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>عناصر الطلب</h5>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            <div class="order-item mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">المنتج</label>
                                        <select name="products[0][product_id]" class="form-control product-select"
                                            required>
                                            <option value="">-- اختر المنتج --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                    {{ $product->name_en }} - ${{ number_format($product->price, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
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


    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        let itemCount = 1;

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
                '<option value="">-- Select --</option><option value="delivery" data-source="inside_city">Delivery (Select Delivery Person)</option><option value="hand_delivered" data-source="inside_city">Hand Delivered (Inside City)</option><option value="shipping" data-source="outside_city">Shipping (Outside City)</option>';
            const options = deliveryMethod.querySelectorAll('option[data-source]');

            // Reset all options
            deliveryMethod.innerHTML = '<option value="">-- Select --</option>';

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
            const defaultLat = {{ old('latitude', 52.3676) }};
            const defaultLng = {{ old('longitude', 4.9041) }};
            const hasInitialCoords = '{{ old('latitude') }}' && '{{ old('longitude') }}';

            map = L.map('map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            if (hasInitialCoords) {
                marker = L.marker([defaultLat, defaultLng]).addTo(map);
            }

            map.on('click', function(e) {
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker(e.latlng).addTo(map);
                document.querySelector('input[name="latitude"]').value = e.latlng.lat.toFixed(6);
                document.querySelector('input[name="longitude"]').value = e.latlng.lng.toFixed(6);
            });
        });

        // === Order Items Logic (unchanged) ===
        function getSelectedProductIds() {
            const selectedIds = new Set();
            document.querySelectorAll('.product-select').forEach(select => {
                if (select.value) selectedIds.add(select.value);
            });
            return selectedIds;
        }

        function updateProductSelectOptions() {
            const selectedIds = getSelectedProductIds();
            document.querySelectorAll('.product-select').forEach(select => {
                const currentValue = select.value;
                select.querySelectorAll('option').forEach(option => {
                    if (option.value === '') return;
                    option.disabled = selectedIds.has(option.value) && option.value !== currentValue;
                });
            });
        }

        function updateSummary() {
            let total = 0,
                itemCount = 0;
            document.querySelectorAll('.order-item').forEach(item => {
                const select = item.querySelector('.product-select');
                const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(select.selectedOptions[0]?.dataset.price) || 0;
                if (select.value && quantity > 0) {
                    total += price * quantity;
                    itemCount++;
                }
            });
            document.getElementById('totalItems').textContent = itemCount;
            document.getElementById('totalAmount').textContent = total.toFixed(2);
        }

        document.getElementById('addItemBtn').addEventListener('click', function() {
            const container = document.getElementById('itemsContainer');
            const newItem = document.querySelector('.order-item').cloneNode(true);

            newItem.querySelector('.product-select').value = '';
            newItem.querySelector('.quantity-input').value = '1';
            newItem.querySelector('.remove-item').style.display = 'block';

            const inputs = newItem.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.name.replace(/\[\d+\]/, `[${itemCount}]`);
                input.name = name;
            });

            container.appendChild(newItem);
            itemCount++;

            attachEventListeners();
            updateProductSelectOptions();
            updateSummary();
        });

        function attachEventListeners() {
            document.querySelectorAll('.product-select').forEach(select => {
                select.removeEventListener('change', handleProductChange);
                select.addEventListener('change', handleProductChange);
            });

            document.querySelectorAll('.quantity-input').forEach(input => {
                input.removeEventListener('input', updateSummary);
                input.addEventListener('input', updateSummary);
            });

            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.removeEventListener('click', removeItem);
                btn.addEventListener('click', removeItem);
            });
        }

        function handleProductChange() {
            updateProductSelectOptions();
            updateSummary();
        }

        function removeItem(e) {
            e.target.closest('.order-item').remove();
            updateProductSelectOptions();
            updateSummary();
        }

        // Initial setup
        attachEventListeners();
        updateProductSelectOptions();
        updateSummary();
    </script>
@endsection
