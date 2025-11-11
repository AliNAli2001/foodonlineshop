@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>طلب رقم #{{ $order->id }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>معلومات الطلب</h5>
                </div>
                <div class="card-body">
                    @if ($order->client_id)
                        <p><strong>العميل:</strong> {{ $order->client->first_name }} {{ $order->client->last_name }}</p>
                        <p><strong>هاتف العميل:</strong> {{ $order->client->phone }}</p>
                    @else
                        <p><strong>نوع الطلب:</strong> <span class="badge bg-warning">تم إنشاؤه بواسطة الإدارة</span></p>
                        @if ($order->createdByAdmin)
                            <p><strong>تم الإنشاء بواسطة:</strong> {{ $order->createdByAdmin->first_name }}
                                {{ $order->createdByAdmin->last_name }}</p>
                        @endif
                    @endif
                    <p><strong>الحالة:</strong> <span class="badge bg-info">{{ ucfirst($order->status) }}</span></p>
                    <p><strong>تاريخ الطلب:</strong> {{ $order->order_date->format('Y-m-d H:i') }}</p>
                    <p><strong>مصدر الطلب:</strong> {{ ucfirst(str_replace('_', ' ', $order->order_source)) }}</p>
                    <p><strong>طريقة التوصيل:</strong> {{ ucfirst(str_replace('_', ' ', $order->delivery_method)) }}</p>
                    <p><strong>العنوان:</strong> {{ $order->address_details }}</p>
                    @if ($order->latitude && $order->longitude)
                        <p><strong>الموقع:</strong> {{ $order->latitude }}, {{ $order->longitude }}</p>
                    @endif
                    @if ($order->shipping_notes)
                        <p><strong>ملاحظات الشحن:</strong> {{ $order->shipping_notes }}</p>
                    @endif
                    @if ($order->admin_order_client_notes)
                        <p><strong>ملاحظات الإدارة:</strong> {{ $order->admin_order_client_notes }}</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5>عناصر الطلب</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>سعر الوحدة</th>
                                <th>الكمية</th>
                                <th>المجموع الفرعي</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name_en }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $item->status === 'normal' ? 'bg-success' : 'bg-warning' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- قسم إدارة حالة الطلب -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>إدارة حالة الطلب</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3"><strong>الحالة الحالية:</strong> <span
                            class="badge bg-info">{{ ucfirst($order->status) }}</span></p>

                    @if ($order->status === 'pending')
                        <div class="btn-group" role="group">
                            <form action="{{ route('admin.orders.confirm', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">تأكيد الطلب</button>
                            </form>
                            <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <textarea name="reason" placeholder="سبب الرفض"></textarea>
                                <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد؟')">رفض
                                    الطلب</button>
                            </form>
                        </div>
                    @elseif ($order->status === 'confirmed')
                        <div class="btn-group" role="group">
                            @if ($order->order_source == 'inside_city')
                                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="delivered">
                                    @if ($order->delivery_method === 'delivery' && $order->order_source === 'inside_city' && !$order->delivery_id)
                                        <div class="mb-3">
                                            <label for="delivery_id" class="form-label">اختر موظف التوصيل (مطلوب)</label>
                                            <select class="form-control" id="delivery_id" name="delivery_id" required>
                                                <option value="">-- اختر موظف التوصيل --</option>
                                                @foreach ($deliveryPersons as $delivery)
                                                    <option value="{{ $delivery->id }}">{{ $delivery->first_name }}
                                                        {{ $delivery->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <button type="submit" class="btn btn-primary">
                                        @if ($order->delivery_method === 'delivery' && $order->order_source === 'inside_city' && !$order->delivery_id)
                                            تعيين موظف التوصيل & وضع كتم التوصيل
                                        @else
                                            وضع كتم التوصيل
                                        @endif
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="shipped">
                                    <button type="submit" class="btn btn-primary">
                                        وضع كتم الشحن
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="canceled">
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('هل أنت متأكد؟')">إلغاء الطلب</button>
                            </form>
                        </div>
                    @elseif ($order->status === 'shipped' || $order->status === 'delivered')
                        <div class="btn-group" role="group">
                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="done">
                                <button type="submit" class="btn btn-success">وضع كتم الإنجاز</button>
                            </form>
                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="returned">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد؟')">تم
                                    إرجاع الطلب</button>
                            </form>
                        </div>
                    @elseif ($order->status === 'done' || $order->status === 'canceled')
                        <p class="text-muted">هذا الطلب {{ $order->status }}. لا توجد إجراءات أخرى متاحة.</p>
                    @endif
                </div>
            </div>

            @if ($order->delivery_id)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>معلومات التوصيل</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>موظف التوصيل:</strong> {{ $order->delivery->first_name }}
                            {{ $order->delivery->last_name }}</p>
                        <p><strong>هاتفه:</strong> {{ $order->delivery->phone }}</p>
                        <p><strong>الحالة:</strong> {{ ucfirst($order->delivery->status) }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">ملخص الطلب</h5>
                    <p>الإجمالي: ${{ number_format($order->total_amount, 2) }}</p>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary w-100">العودة للطلبات</a>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body" style="direction: rtl;">
                    <h5 class="card-title">رسالة قابلة للنسخ</h5>

                    <textarea id="copiableMessage" class="form-control" readonly onclick="this.select()"
                        style="overflow:hidden; resize:none; width:100%; min-height:100px;">{{ $order->prepareCopiableMessage() }}</textarea>

                    <button type="button" class="btn btn-primary mt-2" onclick="copyMessage()">نسخ الرسالة</button>
                </div>

                <script>
                    const textarea = document.getElementById('copiableMessage');

                    // Automatically adjust height based on content
                    function autoResize() {
                        textarea.style.height = 'auto'; // reset
                        textarea.style.height = textarea.scrollHeight + 'px';
                    }

                    textarea.addEventListener('input', autoResize);
                    window.addEventListener('load', autoResize); // resize on page load

                    // Copy to clipboard function
                    function copyMessage() {
                        textarea.select();
                        textarea.setSelectionRange(0, 99999); // for mobile
                        navigator.clipboard.writeText(textarea.value)
                            .then(() => console.log('تم نسخ الرسالة!'))
                            .catch(err => console.log('فشل النسخ: ' + err));
                    }
                </script>

            </div>
        </div>
    </div>

@endsection
