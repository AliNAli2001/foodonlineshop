@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>إدارة البضائع التالفة</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.damaged-goods.create') }}" class="btn btn-primary">إضافة بضاعة تالفة</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">العودة</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>السبب</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($damagedGoods as $damaged)
                        <tr>
                            <td>{{ $damaged->product->name_ar }}</td>
                            <td>{{ $damaged->quantity }}</td>
                            
                            <td>{{ $damaged->reason }}</td>
                            <td>{{ $damaged->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.damaged-goods.show', $damaged->id) }}" class="btn btn-sm btn-info">عرض</a>
                                <form action="{{ route('admin.damaged-goods.destroy', $damaged->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف ، سيتم حذف التسوية المالية (الخسارة) المتعلقة بها؟')">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $damagedGoods->links() }}
        </div>
    </div>
</div>
@endsection
