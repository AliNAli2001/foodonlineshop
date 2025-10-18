@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Verify Email</h3>
                </div>
                <div class="card-body">
                    <p>A verification code has been sent to your email. Please enter it below.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('verify.email.confirm') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" placeholder="10-digit code" maxlength="10" required>
                            @error('code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Verify Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

