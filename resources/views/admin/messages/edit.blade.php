@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Edit Message Template</h1>
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

                <form action="{{ route('admin.messages.update', $template->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Template Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ $template->name }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="client" {{ $template->type === 'client' ? 'selected' : '' }}>Client</option>
                            <option value="delivery" {{ $template->type === 'delivery' ? 'selected' : '' }}>Delivery</option>
                            <option value="admin" {{ $template->type === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content_ar" class="form-label">Content (Arabic)</label>
                        <textarea class="form-control @error('content_ar') is-invalid @enderror" 
                                  id="content_ar" name="content_ar" rows="5" required>{{ $template->content_ar }}</textarea>
                        @error('content_ar')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content_en" class="form-label">Content (English)</label>
                        <textarea class="form-control @error('content_en') is-invalid @enderror" 
                                  id="content_en" name="content_en" rows="5" required>{{ $template->content_en }}</textarea>
                        @error('content_en')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Template</button>
                    <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

