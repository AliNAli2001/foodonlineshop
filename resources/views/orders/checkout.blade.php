@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<style>
    #map {
        height: 400px;
        border-radius: 5px;
        margin-top: 10px;
    }
    .map-info {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Checkout</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Order Items</h5>
            </div>
            <div class="card-body">
                <div id="orderItemsContainer">
                    <p>Loading items from cart...</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Delivery Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('order.store') }}" method="POST" id="checkoutForm">
                    @csrf
                    <input type="hidden" id="cartData" name="cart_data" value="">

                    <div class="mb-3">
                        <label for="order_source" class="form-label">Order Source</label>
                        <select class="form-control @error('order_source') is-invalid @enderror" 
                                id="order_source" name="order_source" required>
                            <option value="">Select...</option>
                            <option value="inside_city">Inside City</option>
                            <option value="outside_city">Outside City</option>
                        </select>
                        @error('order_source')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="delivery_method" class="form-label">Delivery Method</label>
                        <select class="form-control @error('delivery_method') is-invalid @enderror" 
                                id="delivery_method" name="delivery_method" required>
                            <option value="">Select...</option>
                            <option value="delivery">Delivery</option>
                            <option value="shipping">Shipping</option>
                            <option value="hand_delivered">Hand Delivered</option>
                        </select>
                        @error('delivery_method')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address_details" class="form-label">Address Details</label>
                        <textarea class="form-control @error('address_details') is-invalid @enderror" 
                                  id="address_details" name="address_details" rows="3" required></textarea>
                        @error('address_details')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Location on Map (Optional)</label>
                        <div id="map"></div>
                        <div class="map-info">
                            <p><strong>Click on the map to select your delivery location</strong></p>
                            <p>Latitude: <span id="mapLatitude">-</span></p>
                            <p>Longitude: <span id="mapLongitude">-</span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="latitude" class="form-label">Latitude (Optional)</label>
                            <input type="number" class="form-control" id="latitude" name="latitude" step="0.000001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="longitude" class="form-label">Longitude (Optional)</label>
                            <input type="number" class="form-control" id="longitude" name="longitude" step="0.000001">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="shipping_notes" class="form-label">Shipping Notes (Optional)</label>
                        <textarea class="form-control" id="shipping_notes" name="shipping_notes" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="general_notes" class="form-label">General Notes (Optional)</label>
                        <textarea class="form-control" id="general_notes" name="general_notes" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Place Order</button>
                    <a href="{{ route('cart.index') }}" class="btn btn-secondary">Back to Cart</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order Summary</h5>
                <p>Total: ${{ number_format($total, 2) }}</p>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    // Load cart items from localStorage
    function loadCheckoutItems() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        const container = document.getElementById('orderItemsContainer');

        if (Object.keys(cart).length === 0) {
            container.innerHTML = '<p class="text-danger">Your cart is empty. <a href="{{ route("cart.index") }}">Go back to cart</a></p>';
            document.querySelector('button[type="submit"]').disabled = true;
            return;
        }

        let html = '<table class="table"><thead><tr><th>Product</th><th>Unit Price</th><th>Quantity</th><th>Subtotal</th></tr></thead><tbody>';
        let totalAmount = 0;

        Object.entries(cart).forEach(([productId, item]) => {
            const subtotal = item.price * item.quantity;
            totalAmount += subtotal;

            html += `<tr>
                <td>${item.name}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>$${subtotal.toFixed(2)}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    // Handle form submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');

        if (Object.keys(cart).length === 0) {
            e.preventDefault();
            alert('Your cart is empty!');
            return false;
        }

        // Store cart data in hidden field
        document.getElementById('cartData').value = JSON.stringify(cart);
    });

    let map;
    let marker;
    const defaultLat = 31.9454; // Default to Egypt center
    const defaultLng = 35.9284;

    function initMap() {
        // Initialize map
        map = L.map('map').setView([defaultLat, defaultLng], 13);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Handle map clicks
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // Remove existing marker
            if (marker) {
                map.removeLayer(marker);
            }

            // Add new marker
            marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`<b>Selected Location</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();

            // Update input fields
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            document.getElementById('mapLatitude').textContent = lat.toFixed(6);
            document.getElementById('mapLongitude').textContent = lng.toFixed(6);
        });

        // Check if there are existing coordinates
        const existingLat = document.getElementById('latitude').value;
        const existingLng = document.getElementById('longitude').value;

        if (existingLat && existingLng) {
            const lat = parseFloat(existingLat);
            const lng = parseFloat(existingLng);
            map.setView([lat, lng], 13);
            marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`<b>Selected Location</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();
            document.getElementById('mapLatitude').textContent = lat.toFixed(6);
            document.getElementById('mapLongitude').textContent = lng.toFixed(6);
        }
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadCheckoutItems();
        initMap();
    });

    // Update map when latitude/longitude inputs change
    document.getElementById('latitude').addEventListener('change', function() {
        const lat = parseFloat(this.value);
        const lng = parseFloat(document.getElementById('longitude').value);

        if (lat && lng && map) {
            map.setView([lat, lng], 13);

            if (marker) {
                map.removeLayer(marker);
            }

            marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`<b>Selected Location</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();
            document.getElementById('mapLatitude').textContent = lat.toFixed(6);
            document.getElementById('mapLongitude').textContent = lng.toFixed(6);
        }
    });

    document.getElementById('longitude').addEventListener('change', function() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(this.value);

        if (lat && lng && map) {
            map.setView([lat, lng], 13);

            if (marker) {
                map.removeLayer(marker);
            }

            marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`<b>Selected Location</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();
            document.getElementById('mapLatitude').textContent = lat.toFixed(6);
            document.getElementById('mapLongitude').textContent = lng.toFixed(6);
        }
    });
</script>
@endsection
@endsection

