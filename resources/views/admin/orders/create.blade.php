@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Create New Order</h1>
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

            <!-- Client Selection -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Client Information (Optional)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Select Client</label>
                        <select name="client_id" class="form-control @error('client_id') is-invalid @enderror">
                            <option value="">-- No Client (Admin Order) --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->first_name }} {{ $client->last_name }} ({{ $client->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Leave empty to create an order without a client.</small>
                    </div>
                </div>
            </div>

            <!-- Order Location & Delivery -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Order Location & Delivery</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Order Source</label>
                        <select name="order_source" class="form-control @error('order_source') is-invalid @enderror" required>
                            <option value="">-- Select --</option>
                            <option value="inside_city" {{ old('order_source') == 'inside_city' ? 'selected' : '' }}>Inside City</option>
                            <option value="outside_city" {{ old('order_source') == 'outside_city' ? 'selected' : '' }}>Outside City</option>
                        </select>
                        @error('order_source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Delivery Method</label>
                        <select name="delivery_method" class="form-control @error('delivery_method') is-invalid @enderror" required>
                            <option value="">-- Select --</option>
                            <option value="delivery" {{ old('delivery_method') == 'delivery' ? 'selected' : '' }}>Delivery (Select Delivery Person)</option>
                            <option value="hand_delivered" {{ old('delivery_method') == 'hand_delivered' ? 'selected' : '' }}>Hand Delivered (Inside City)</option>
                            <option value="shipping" {{ old('delivery_method') == 'shipping' ? 'selected' : '' }}>Shipping (Outside City)</option>
                        </select>
                        @error('delivery_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address Details</label>
                        <textarea name="address_details" class="form-control @error('address_details') is-invalid @enderror" rows="3" required>{{ old('address_details') }}</textarea>
                        @error('address_details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Latitude (Optional)</label>
                                <input type="number" name="latitude" step="0.000001" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" placeholder="-90 to 90">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Longitude (Optional)</label>
                                <input type="number" name="longitude" step="0.000001" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" placeholder="-180 to 180">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shipping Notes (Optional)</label>
                        <textarea name="shipping_notes" class="form-control @error('shipping_notes') is-invalid @enderror" rows="2">{{ old('shipping_notes') }}</textarea>
                        @error('shipping_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Notes About Client (Optional)</label>
                        <textarea name="admin_order_client_notes" class="form-control @error('admin_order_client_notes') is-invalid @enderror" rows="2">{{ old('admin_order_client_notes') }}</textarea>
                        @error('admin_order_client_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Internal notes about the client or order.</small>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Order Items</h5>
                </div>
                <div class="card-body">
                    <div id="itemsContainer">
                        <div class="order-item mb-3 p-3 border rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Product</label>
                                    <select name="products[0][product_id]" class="form-control product-select" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                {{ $product->name_en }} - ${{ number_format($product->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" name="products[0][quantity]" class="form-control quantity-input" min="1" value="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger w-100 remove-item" style="display:none;">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary" id="addItemBtn">+ Add Another Item</button>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Order</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <p><strong>Total Items:</strong> <span id="totalItems">0</span></p>
                <p><strong>Total Amount:</strong> $<span id="totalAmount">0.00</span></p>
                <hr>
                <p class="text-muted small">This order will be created with <strong>Confirmed</strong> status.</p>
            </div>
        </div>
    </div>
</div>

<script>
    let itemCount = 1;

    function getSelectedProductIds() {
        const selectedIds = new Set();
        document.querySelectorAll('.product-select').forEach(select => {
            if (select.value) {
                selectedIds.add(select.value);
            }
        });
        return selectedIds;
    }

    function updateProductSelectOptions() {
        const selectedIds = getSelectedProductIds();

        document.querySelectorAll('.product-select').forEach(select => {
            const currentValue = select.value;

            select.querySelectorAll('option').forEach(option => {
                if (option.value === '') return; // Skip empty option

                // Disable if product is selected elsewhere
                if (selectedIds.has(option.value) && option.value !== currentValue) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });
        });
    }

    function updateSummary() {
        let total = 0;
        let itemCount = 0;

        document.querySelectorAll('.order-item').forEach(item => {
            const select = item.querySelector('.product-select');
            const quantity = parseInt(item.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(select.options[select.selectedIndex].dataset.price) || 0;

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

        // Reset values
        newItem.querySelector('.product-select').value = '';
        newItem.querySelector('.quantity-input').value = '1';

        // Update name attributes
        const inputs = newItem.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.name.replace(/\[\d+\]/, `[${itemCount}]`);
            input.name = name;
        });

        // Show remove button
        newItem.querySelector('.remove-item').style.display = 'block';

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

    function handleProductChange(e) {
        updateProductSelectOptions();
        updateSummary();
    }

    function removeItem(e) {
        e.target.closest('.order-item').remove();
        updateProductSelectOptions();
        updateSummary();
    }

    attachEventListeners();
    updateProductSelectOptions();
    updateSummary();
</script>
@endsection

