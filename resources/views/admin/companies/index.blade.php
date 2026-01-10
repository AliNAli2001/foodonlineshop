@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>الشركات</h1>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">إضافة شركة</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>الاسم (إنجليزي)</th>
                    <th>الاسم (عربي)</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        <td>{{ $company->name_en }}</td>
                        <td>{{ $company->name_ar }}</td>
                        <td>
                            <a href="{{ route('admin.companies.show', $company->id) }}" class="btn btn-sm btn-info">عرض</a>
                            <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد شركات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $companies->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
