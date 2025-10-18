@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>My Profile</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Profile Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('client.update-profile') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ $client->first_name }}" required>
                            @error('first_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ $client->last_name }}" required>
                            @error('last_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ $client->phone }}" required>
                        @error('phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address_details" class="form-label">Address</label>
                        <textarea class="form-control @error('address_details') is-invalid @enderror" 
                                  id="address_details" name="address_details" rows="3">{{ $client->address_details }}</textarea>
                        @error('address_details')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="language_preference" class="form-label">Language</label>
                        <select class="form-control @error('language_preference') is-invalid @enderror" 
                                id="language_preference" name="language_preference" required>
                            <option value="en" {{ $client->language_preference === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ $client->language_preference === 'ar' ? 'selected' : '' }}>Arabic</option>
                        </select>
                        @error('language_preference')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="promo_consent" name="promo_consent" 
                                   value="1" {{ $client->promo_consent ? 'checked' : '' }}>
                            <label class="form-check-label" for="promo_consent">
                                I want to receive promotional offers
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Verification Status</h5>
                <p>
                    Email: 
                    @if ($client->email_verified)
                        <span class="badge bg-success">Verified</span>
                    @else
                        <span class="badge bg-warning">Not Verified</span>
                    @endif
                </p>
                <p>
                    Phone: 
                    @if ($client->phone_verified)
                        <span class="badge bg-success">Verified</span>
                    @else
                        <span class="badge bg-warning">Not Verified</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

