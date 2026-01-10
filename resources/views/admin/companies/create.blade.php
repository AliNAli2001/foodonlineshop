@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>إضافة شركة</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
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

                <form action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="name_ar" class="form-label">الاسم (عربي)</label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                               id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                        @error('name_ar')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">الاسم (إنجليزي)</label>
                        <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                               id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                        @error('name_en')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                  

                    <div class="mb-3">
                        <label for="logo" class="form-label">صورة الشركة</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror"
                               id="logo" name="logo" accept="image/*">
                        @error('logo')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                 

                    <button type="submit" class="btn btn-primary">إضافة الشركة</button>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">إلغاء</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
