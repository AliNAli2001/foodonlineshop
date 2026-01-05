@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>الوسوم</h1>
        <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">إضافة وسم</a>
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
                @forelse ($tags as $tag)
                    <tr>
                        <td>{{ $tag->id }}</td>
                        <td>{{ $tag->name_en }}</td>
                        <td>{{ $tag->name_ar }}</td>
                        <td>
                            <a href="{{ route('admin.tags.show', $tag->id) }}" class="btn btn-sm btn-info">عرض</a>
                            <a href="{{ route('admin.tags.edit', $tag->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد وسوم</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $tags->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
