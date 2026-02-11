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
                        <p><strong>الزبون:</strong> {{ $order->client->first_name }} {{ $order->client->last_name }}</p>
                        <p><strong>هاتف الزبون:</strong> {{ $order->client->phone }}</p>
                    @else
                        <p><strong>نوع الطلب:</strong> <span class="badge bg-warning">تم إنشاؤه بواسطة الإدارة</span></p>
                        @if ($order->createdByAdmin)
                            <p><strong>تم الإنشاء بواسطة:</strong> {{ $order->createdByAdmin->first_name }}
                                {{ $order->createdByAdmin->last_name }}</p>
                        @endif
                    @endif
                    <p><strong>الحالة:</strong> <span class="badge bg-info">{{ $order::STATUSES[$order->status] }}</span></p>
                    <p><strong>تاريخ الطلب:</strong> {{ $order->order_date->format('Y-m-d H:i') }}</p>
                    <p><strong>مصدر الطلب:</strong> {{ $order::SOURCES[$order->order_source] }}</p>
                    <p><strong>طريقة التوصيل:</strong> {{ $order::DELIVERY_METHODS[$order->delivery_method] }}</p>
                    <p><strong>العنوان:</strong> {{ $order->address_details }}</p>
                    @if ($order->latitude && $order->longitude)
                        <p><strong>الموقع:</strong> {{ $order->latitude }}, {{ $order->longitude }}</p>
                        <div id="map" style="height: 300px;"></div>
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
                                @if (!in_array($order->status, ['pending', 'cancelled', 'returned']))
                                    <th>
                                        @if (in_array($order->status, ['returned', 'canceled']))
                                            <span class="badge bg-warning text-dark">هذه الكميات أرجعت إلى المخازن المتعلقة
                                                بها</span>
                                        @endif <br>
                                        الدفعة المستخدمة
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name_en }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                    @if (!in_array($order->status, ['pending', 'cancelled', 'returned']))
                                        <td>
                                            @if ($item->batches->isNotEmpty())
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($item->batches as $batchItem)
                                                        <li>
                                                            <strong>دفعة:</strong>
                                                            {{ $batchItem->inventoryBatch->batch_number }}
                                                            <br>
                                                            <strong>كمية:</strong> {{ $batchItem->quantity }}
                                                            <br>
                                                            @if ($batchItem->inventoryBatch->expiry_date)
                                                                <strong>تاريخ الانتهاء:</strong>
                                                                {{ $batchItem->inventoryBatch->expiry_date }}
                                                            @else
                                                                <strong>تاريخ الانتهاء:</strong> غير محدد
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endif
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
                    <p class="mb-3"><strong>الحالة الحالية:</strong>
                        <span
                            class="badge
                            @if ($order->status === 'pending') bg-warning
                            @elseif($order->status === 'confirmed') bg-info
                            @elseif($order->status === 'shipped') bg-primary
                            @elseif($order->status === 'delivered') bg-primary
                            @elseif($order->status === 'done') bg-success
                            @elseif($order->status === 'canceled') bg-danger
                            @elseif($order->status === 'returned') bg-secondary @endif">
                            {{ $order::STATUSES[$order->status] }}
                        </span>
                    </p>

                    @if (count($availableTransitions) > 0)
                        <div class="mb-3">
                            <p><strong>الإجراءات المتاحة:</strong></p>

                            @if ($order->status === 'pending')
                                {{-- Pending orders: Confirm or Reject --}}
                                @if (in_array('confirmed', $availableTransitions))
                                    <form action="{{ route('admin.orders.confirm', $order->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success mb-2">
                                            <i class="fas fa-check"></i> تأكيد الطلب
                                        </button>
                                    </form>
                                @endif

                                @if (in_array('canceled', $availableTransitions))
                                    <button type="button" class="btn btn-danger mb-2" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        <i class="fas fa-times"></i> رفض الطلب
                                    </button>
                                @endif
                            @else
                                {{-- Other statuses: Show available transitions --}}
                                @foreach ($availableTransitions as $transition)
                                    @if ($transition === 'delivered' && $order->delivery_method === 'delivery' && !$order->delivery_id)
                                        {{-- Special case: Need to assign delivery person --}}
                                        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                            data-bs-target="#assignDeliveryModal">
                                            <i class="fas fa-truck"></i> تعيين موظف التوصيل & وضع كتم التوصيل
                                        </button>
                                    @else
                                        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $transition }}">
                                            <button type="submit"
                                                class="btn mb-2
                                                    @if ($transition === 'done') btn-success
                                                    @elseif($transition === 'canceled') btn-danger
                                                    @elseif($transition === 'returned') btn-warning
                                                    @elseif($transition === 'shipped') btn-primary
                                                    @elseif($transition === 'delivered') btn-primary
                                                    @else btn-secondary @endif"
                                                @if (in_array($transition, ['canceled', 'returned'])) onclick="return confirm('هل أنت متأكد من {{ $transition === 'canceled' ? 'إلغاء' : 'إرجاع' }} الطلب؟')" @endif>
                                                <i
                                                    class="fas
                                                    @if ($transition === 'done') fa-check-circle
                                                    @elseif($transition === 'canceled') fa-times-circle
                                                    @elseif($transition === 'returned') fa-undo
                                                    @elseif($transition === 'shipped') fa-shipping-fast
                                                    @elseif($transition === 'delivered') fa-truck @endif"></i>
                                                @if ($transition === 'done')
                                                    إنجاز الطلب
                                                @elseif($transition === 'canceled')
                                                    إلغاء الطلب
                                                @elseif($transition === 'returned')
                                                    إرجاع الطلب
                                                @elseif($transition === 'shipped')
                                                    شحن الطلب
                                                @elseif($transition === 'delivered')
                                                    تم التوصيل
                                                @else
                                                    {{ ucfirst($transition) }}
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    @else
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            هذا الطلب في حالة نهائية ({{ $order::STATUSES[$order->status] }}). لا توجد إجراءات أخرى متاحة.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Modal for rejecting pending orders --}}
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectModalLabel">رفض الطلب</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">سبب الرفض <span
                                            class="text-danger">*</span></label>
                                    <textarea name="reason" id="reason" class="form-control" rows="3" required
                                        placeholder="أدخل سبب رفض الطلب..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-danger">رفض الطلب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal for assigning delivery person --}}
            <div class="modal fade" id="assignDeliveryModal" tabindex="-1" aria-labelledby="assignDeliveryModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="delivered">
                            <div class="modal-header">
                                <h5 class="modal-title" id="assignDeliveryModalLabel">تعيين موظف التوصيل</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="delivery_id" class="form-label">اختر موظف التوصيل <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="delivery_id" name="delivery_id" required>
                                        <option value="">-- اختر موظف التوصيل --</option>
                                        @foreach ($deliveryPersons as $delivery)
                                            <option value="{{ $delivery->id }}">
                                                {{ $delivery->first_name }} {{ $delivery->last_name }} -
                                                {{ $delivery->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">تعيين و تحديث الحالة</button>
                            </div>
                        </form>
                    </div>
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
                    <h5 class="card-title">رسائل قابلة للنسخ</h5>

                    <h6>رسالة للزبون</h6>
                    <textarea id="clientMessage" class="form-control mb-2" readonly onclick="this.select()"
                        style="overflow:hidden; resize:none; width:100%; min-height:100px;">{{ $order->prepareClientMessage() }}</textarea>
        

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                            onclick="copyClientMessage()">
                            <i class="fa fa-copy"></i>
                        </button>
                        @if ($order->client_phone_number)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->client_phone_number) }}"
                                class="btn btn-sm btn-link text-decoration-none p-0" title="اتصال">
                                <i class="fas fa-phone fa-lg text-primary"></i>
                            </a>
                            <a href="sms:{{ preg_replace('/[^0-9+]/', '', $order->client_phone_number) }}"
                                class="btn btn-sm btn-link text-decoration-none p-0" title="رسالة">
                                <i class="fas fa-sms fa-lg text-secondary"></i>
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->client_phone_number) }}?text={{ urlencode($order->prepareClientMessage()) }}"
                                target="_blank" class="btn btn-sm btn-link text-decoration-none p-0" title="واتساب">
                                <i class="fab fa-whatsapp fa-lg text-success"></i>
                            </a>
                        @endif
                    </div>


                    @if ($order->delivery_id)
                        <h6>رسالة لموظف التوصيل</h6>
                        <textarea id="deliveryMessage" class="form-control mb-2" readonly onclick="this.select()"
                            style="overflow:hidden; resize:none; width:100%; min-height:100px;">{{ $order->prepareDeliveryMessage() }}</textarea>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                onclick="copyDeliveryMessage()">
                                <i class="fa fa-copy"></i>
                            </button>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->delivery->phone) }}"
                                class="btn btn-sm btn-link text-decoration-none p-0" title="اتصال">
                                <i class="fas fa-phone fa-lg text-primary"></i>
                            </a>
                            <a href="sms:{{ preg_replace('/[^0-9+]/', '', $order->delivery->phone)  }}"
                                class="btn btn-sm btn-link text-decoration-none p-0" title="رسالة">
                                <i class="fas fa-sms fa-lg text-secondary"></i>
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->delivery->phone) }}?text={{ urlencode($order->prepareClientMessage()) }}"
                                target="_blank" class="btn btn-sm btn-link text-decoration-none p-0" title="واتساب">
                                <i class="fab fa-whatsapp fa-lg text-success"></i>
                            </a>
                        </div>
                    @endif
                </div>

                <script>
                    const clientTextarea = document.getElementById('clientMessage');
                    @if ($order->delivery_id)
                        const deliveryTextarea = document.getElementById('deliveryMessage');
                    @endif


                    // Automatically adjust height based on content

                    function autoResize(textarea) {

                        textarea.style.height = 'auto'; // reset
                        textarea.style.height = textarea.scrollHeight + 'px';
                    }

                    clientTextarea.addEventListener('input', () => autoResize(clientTextarea));
                    window.addEventListener('load', () => autoResize(clientTextarea)); // resize on page load

                    @if ($order->delivery_id)
                        deliveryTextarea.addEventListener('input', () => autoResize(deliveryTextarea));
                        window.addEventListener('load', () => autoResize(deliveryTextarea));
                    @endif

                    // Copy to clipboard functions
                    function copyClientMessage() {
                        clientTextarea.select();
                        clientTextarea.setSelectionRange(0, 99999); // for mobile
                        navigator.clipboard.writeText(clientTextarea.value)
                            .then(() => console.log('تم نسخ رسالة الزبون!'))
                            .catch(err => console.log('فشل النسخ: ' + err));
                    }

                    @if ($order->delivery_id)
                        function copyDeliveryMessage() {
                            deliveryTextarea.select();
                            deliveryTextarea.setSelectionRange(0, 99999); // for mobile
                            navigator.clipboard.writeText(deliveryTextarea.value)
                                .then(() => console.log('تم نسخ رسالة التوصيل!'))
                                .catch(err => console.log('فشل النسخ: ' + err));
                        }
                    @endif
                </script>

            </div>
            {{-- <div class="card mt-3">
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

            </div> --}}
        </div>
    </div>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($order->latitude && $order->longitude)
                const lat = {{ $order->latitude }};
                const lng = {{ $order->longitude }};

                const map = L.map('map').setView([lat, lng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                L.marker([lat, lng]).addTo(map);
            @endif
        });
    </script>
@endsection
