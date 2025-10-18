@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Shopping Cart</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div id="cartItems">
            <p>Loading cart...</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Cart Summary</h5>
                <p>Total Items: <span id="totalItems">0</span></p>
                <p>Total Price: $<span id="totalPrice">0.00</span></p>
                <a href="{{ route('order.checkout') }}" class="btn btn-primary w-100" id="checkoutBtn" disabled>Proceed to Checkout</a>
                <a href="{{ route('cart.clear') }}" class="btn btn-danger w-100 mt-2">Clear Cart</a>
                <a href="{{ route('products.index') }}" class="btn btn-secondary w-100 mt-2">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function loadCart() {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        const cartItemsDiv = document.getElementById('cartItems');
        
        if (Object.keys(cart).length === 0) {
            cartItemsDiv.innerHTML = '<p>Your cart is empty.</p>';
            document.getElementById('checkoutBtn').disabled = true;
            return;
        }

        let html = '<table class="table"><thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr></thead><tbody>';
        let totalItems = 0;
        let totalPrice = 0;

        Object.entries(cart).forEach(([productId, item]) => {
            const subtotal = item.price * item.quantity;
            totalItems += item.quantity;
            totalPrice += subtotal;

            html += `<tr>
                <td>${item.name}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td><input type="number" class="form-control" style="width: 80px;" value="${item.quantity}" 
                    onchange="updateQuantity(${productId}, this.value)"></td>
                <td>$${subtotal.toFixed(2)}</td>
                <td><button class="btn btn-sm btn-danger" onclick="removeFromCart(${productId})">Remove</button></td>
            </tr>`;
        });

        html += '</tbody></table>';
        cartItemsDiv.innerHTML = html;
        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('totalPrice').textContent = totalPrice.toFixed(2);
        document.getElementById('checkoutBtn').disabled = false;
    }

    function updateQuantity(productId, quantity) {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        if (quantity <= 0) {
            delete cart[productId];
        } else {
            cart[productId].quantity = parseInt(quantity);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }

    function removeFromCart(productId) {
        const cart = JSON.parse(localStorage.getItem('cart') || '{}');
        delete cart[productId];
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }

    loadCart();
</script>
@endsection
@endsection

