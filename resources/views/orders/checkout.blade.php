@extends('layouts.app')

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
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item['product']->name_en }}</td>
                                <td>${{ number_format($item['unit_price'], 2) }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>${{ number_format($item['subtotal'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Delivery Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('order.store') }}" method="POST">
                    @csrf

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
@endsection

