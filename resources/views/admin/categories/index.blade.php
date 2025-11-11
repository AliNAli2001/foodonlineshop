@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>التصنيفات</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">إضافة تصنيف</a>
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
                    <th>النوع</th>
                    <th>مميز</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name_en }}</td>
                        <td>{{ $category->name_ar }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($category->type) }}</span></td>
                        <td>
                            @if ($category->featured)
                                <span class="badge bg-success">نعم</span>
                            @else
                                <span class="badge bg-secondary">لا</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-sm btn-info">عرض</a>
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد تصنيفات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
