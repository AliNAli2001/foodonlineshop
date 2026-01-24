@extends('layouts.admin')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8 d-flex align-items-center gap-2">
        <h1 class="h3 mb-0">أفراد التوصيل</h1>
        <span class="text-muted small">إدارة وتواصل سريع</span>
    </div>
    <div class="col-md-4 text-start text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.delivery.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-user-plus me-1"></i>
            إضافة موظف توصيل
        </a>
    </div>
</div>

<div class="row">
    @forelse ($deliveryPersons as $delivery)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 14px; overflow: hidden;">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $delivery->full_name }}</h5>
                    <span class="badge bg-light text-primary">#{{ $delivery->id }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center">
                        <strong class="me-2">الهاتف:</strong>
                        <span class="fw-semibold">{{ $delivery->phone }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $delivery->phone) }}" class="btn btn-sm btn-link text-decoration-none p-0" title="اتصال">
                            <i class="fas fa-phone fa-lg text-primary"></i>
                        </a>
                        <a href="sms:{{ $delivery->phone }}" class="btn btn-sm btn-link text-decoration-none p-0" title="رسالة">
                            <i class="fas fa-sms fa-lg text-secondary"></i>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $delivery->phone) }}" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0" title="واتساب">
                            <i class="fab fa-whatsapp fa-lg text-success"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" title="نسخ" onclick="copyToClipboard('{{ $delivery->phone }}')">
                            <i class="fas fa-copy fa-lg text-info"></i>
                        </button>
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <strong class="me-2">الهاتف +:</strong>
                        <span class="fw-semibold">{{ $delivery->phone_plus ?? '-' }}</span>
                    </div>
                    @if ($delivery->phone_plus && $delivery->phone_plus != '-')
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $delivery->phone_plus) }}" class="btn btn-sm btn-link text-decoration-none p-0" title="اتصال">
                                <i class="fas fa-phone fa-lg text-primary"></i>
                            </a>
                            <a href="sms:{{ $delivery->phone_plus }}" class="btn btn-sm btn-link text-decoration-none p-0" title="رسالة">
                                <i class="fas fa-sms fa-lg text-secondary"></i>
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $delivery->phone_plus) }}" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0" title="واتساب">
                                <i class="fab fa-whatsapp fa-lg text-success"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" title="نسخ" onclick="copyToClipboard('{{ $delivery->phone_plus }}')">
                                <i class="fas fa-copy fa-lg text-info"></i>
                            </button>
                        </div>
                    @endif

                    <div class="d-flex align-items-center mb-2">
                        <strong class="me-2">الحالة:</strong>
                        <span class="badge rounded-pill bg-{{ $delivery->status === 'available' ? 'success' : ($delivery->status === 'busy' ? 'warning text-dark' : 'secondary') }} px-3 py-2">
                            {{ \App\Models\Delivery::STATUSES[$delivery->status] ?? ucfirst($delivery->status) }}
                        </span>
                    </div>

                    @if (isset($delivery->orders_count))
                        <div class="text-muted small">عدد الطلبات: <span class="fw-bold">{{ $delivery->orders_count }}</span></div>
                    @endif
                </div>
                <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.delivery.show', $delivery->id) }}" class="btn btn-sm btn-link text-decoration-none">
                            <i class="fas fa-eye text-info"></i>
                        </a>
                        <a href="{{ route('admin.delivery.edit', $delivery->id) }}" class="btn btn-sm btn-link text-decoration-none">
                            <i class="fas fa-edit text-warning"></i>
                        </a>
                    </div>
                    <form action="{{ route('admin.delivery.destroy', $delivery->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-link text-decoration-none">
                            <i class="fas fa-trash-alt text-danger"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-md-12">
            <div class="alert alert-info text-center">لا يوجد موظفو توصيل.</div>
        </div>
    @endforelse
</div>

<div class="row mt-4">
    <div class="col-md-12 d-flex justify-content-center">
        {{ $deliveryPersons->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard(text) {
    if (!navigator.clipboard) {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        toast('تم نسخ الرقم إلى الحافظة');
        return;
    }
    navigator.clipboard.writeText(text).then(function() {
        toast('تم نسخ الرقم إلى الحافظة');
    }, function(err) {
        console.error('تعذر النسخ: ', err);
    });
}

function toast(message) {
    const el = document.createElement('div');
    el.textContent = message;
    el.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#111;color:#fff;padding:10px 16px;border-radius:8px;z-index:1055;box-shadow:0 6px 16px rgba(0,0,0,.2);opacity:0;transition:opacity .2s ease';
    document.body.appendChild(el);
    requestAnimationFrame(()=>{ el.style.opacity = 1; });
    setTimeout(()=>{
        el.style.opacity = 0;
        setTimeout(()=> document.body.removeChild(el), 250);
    }, 1200);
}
</script>
@endsection