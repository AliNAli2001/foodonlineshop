@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">التسويات المالية</h2>
            <a href="{{ route('admin.adjustments.create') }}" class="btn btn-primary">إضافة تعديل</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المبلغ الكلي</th>
                                <th>نوع التعديل</th>
                                <th>السبب</th>
                                <th>التاريخ</th>
                                <th class="text-end">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adjustment)
                                <tr>
                                    <td>{{ $adjustment->id }}</td>
                                    <td>{{ $adjustment->quantity ?? '—' }}</td>
                                    <td>
                                        @php
                                            $types = [
                                                'gain' => 'ربح',
                                                'loss' => 'خسارة',
                                            ];
                                        @endphp
                                        <span
                                            class="badge {{ $adjustment->adjustment_type === 'gain' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $types[$adjustment->adjustment_type] ?? $adjustment->adjustment_type }}
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 300px;" title="{{ $adjustment->reason }}">
                                        {{ $adjustment->reason }}</td>
                                    <td>{{ optional($adjustment->date)->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.adjustments.show', $adjustment->id) }}"
                                            class="btn btn-sm btn-info">عرض</a>
                                        {{-- prevent edit and delete if damaged goods --}}
                                        @if ($adjustment->adjustable instanceof \App\Models\DamagedGoods)
                                           <a
                                            href="{{ route('admin.damaged-goods.show', $adjustment->adjustable->id) }}">تفاصيل البضاعة
                                            المخربة </a>
                                        @else
                                            <a href="{{ route('admin.adjustments.edit', $adjustment->id) }}"
                                                class="btn btn-sm btn-warning">تعديل</a>
                                            <form action="{{ route('admin.adjustments.destroy', $adjustment->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">حذف</button>
                                            </form>
                                        @endif

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">لا توجد تعديلات.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
@endsection
