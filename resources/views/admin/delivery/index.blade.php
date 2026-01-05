@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>أفراد التوصيل</h1>
        <a href="{{ route('admin.delivery.create') }}" class="btn btn-primary">إضافة موظف توصيل</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>المعرف</th>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($deliveryPersons as $delivery)
                    <tr>
                        <td>{{ $delivery->id }}</td>
                        <td>{{ $delivery->first_name }} {{ $delivery->last_name }}</td>
                        <td>{{ $delivery->phone }}</td>
                        <td>
                            <span class="badge bg-{{ $delivery->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($delivery->status) === 'Active' ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.delivery.edit', $delivery->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('admin.delivery.destroy', $delivery->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                            </form>
                            <a href="{{ route('admin.delivery.show', $delivery->id) }}" class="btn btn-sm btn-info">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا يوجد موظفو توصيل.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $deliveryPersons->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
