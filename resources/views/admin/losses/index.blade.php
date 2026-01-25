@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">الخسائر</h2>
        <a href="{{ route('admin.losses.create') }}" class="btn btn-primary">إضافة خسارة</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الكمية</th>
                            <th>النوع</th>
                            <th>السبب</th>
                            <th>التاريخ</th>
                            <th class="text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($losses as $loss)
                            <tr>
                                <td>{{ $loss->id }}</td>
                                <td>{{ $loss->quantity ?? '—' }}</td>
                                <td>
                                    @php
                                        $types = [
                                            'shipping_costs' => 'تكاليف الشحن',
                                            'general_costs' => 'تكاليف عامة',
                                            'delivery_costs' => 'تكاليف التوصيل',
                                            'other' => 'أخرى',
                                        ];
                                    @endphp
                                    <span class="badge bg-secondary">{{ $types[$loss->type] ?? $loss->type }}</span>
                                </td>
                                <td class="text-truncate" style="max-width: 300px;" title="{{ $loss->reason }}">{{ $loss->reason }}</td>
                                <td>{{ optional($loss->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.losses.show', $loss->id) }}" class="btn btn-sm btn-outline-info">عرض</a>
                                    <a href="{{ route('admin.losses.edit', $loss->id) }}" class="btn btn-sm btn-outline-warning">تعديل</a>
                                    <form action="{{ route('admin.losses.destroy', $loss->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">لا توجد خسائر.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $losses->links() }}
        </div>
    </div>
</div>
@endsection
