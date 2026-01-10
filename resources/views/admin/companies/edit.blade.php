@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>تعديل الشركة</h1>
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

                <form action="{{ route('admin.companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name_ar" class="form-label">الاسم (عربي)</label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                               id="name_ar" name="name_ar" value="{{ $company->name_ar }}" required>
                        @error('name_ar')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">الاسم (إنجليزي)</label>
                        <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                               id="name_en" name="name_en" value="{{ $company->name_en }}" required>
                        @error('name_en')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                  

                    <div class="mb-3">
                        <label for="logo" class="form-label">صورة الشركة</label>
                        @if ($company->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name_en }}" style="max-width: 150px; max-height: 150px;">
                            </div>
                        @endif
                        <input type="file" class="form-control @error('logo') is-invalid @enderror"
                               id="logo" name="logo" accept="image/*">
                        @error('logo')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

              

                    <button type="submit" class="btn btn-primary">تحديث الشركة</button>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">إلغاء</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
