@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Settings</h1>
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

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="dollar_exchange_rate" class="form-label">Dollar Exchange Rate</label>
                        <input type="number" class="form-control @error('dollar_exchange_rate') is-invalid @enderror" 
                               id="dollar_exchange_rate" name="dollar_exchange_rate" 
                               value="{{ $settings->dollar_exchange_rate }}" step="0.0001" required>
                        @error('dollar_exchange_rate')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="general_minimum_alert_quantity" class="form-label">General Minimum Alert Quantity</label>
                        <input type="number" class="form-control @error('general_minimum_alert_quantity') is-invalid @enderror" 
                               id="general_minimum_alert_quantity" name="general_minimum_alert_quantity" 
                               value="{{ $settings->general_minimum_alert_quantity }}" min="0" required>
                        @error('general_minimum_alert_quantity')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="max_order_items" class="form-label">Max Order Items</label>
                        <input type="number" class="form-control @error('max_order_items') is-invalid @enderror" 
                               id="max_order_items" name="max_order_items" 
                               value="{{ $settings->max_order_items }}" min="1" required>
                        @error('max_order_items')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

