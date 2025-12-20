@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction:rtl;text-align:right">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">📅 الفعاليات</h4>
        <a href="{{ route('events.create') }}" class="btn btn-success">
            ➕ فعالية جديدة
        </a>
    </div>

    {{-- SEARCH --}}
    <input type="text"
           id="eventSearch"
           class="form-control mb-4"
           placeholder="🔍 ابحث عن فعالية...">

    {{-- CARDS --}}
    <div class="row g-4" id="eventCards">

        @forelse($events as $e)
        <div class="col-12 col-md-6 col-lg-4 event-card">
            <div class="card-2026 h-100 position-relative">

                {{-- STATUS BADGE --}}
                <span class="badge-status
                    {{ $e->is_active ? 'status-active' : 'status-inactive' }}">
                    {{ $e->is_active ? 'مفعل' : 'غير مفعل' }}
                </span>

                {{-- IMAGE --}}
                @if($e->image)
                    <img src="{{ asset($e->image) }}"
                         class="event-img"
                         alt="event">
                @else
                    <div class="no-image">
                        <i class="fa-regular fa-calendar"></i>
                        <span>بدون صورة</span>
                    </div>
                @endif

                {{-- BODY --}}
                <div class="p-3">
                    <h6 class="fw-bold mb-2">{{ $e->title }}</h6>

                    <p class="text-muted small mb-2">
                        {{ \Illuminate\Support\Str::limit($e->description, 90) }}
                    </p>

                    <div class="small text-muted">
                        📅 {{ $e->start_date }} → {{ $e->end_date }}
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="card-footer-2026">
                    <a href="{{ route('events.edit', $e->id) }}"
                       class="btn btn-sm btn-primary">✏️ تعديل</a>

                    <form method="POST"
                          action="{{ route('events.destroy', $e->id) }}"
                          onsubmit="return confirm('حذف الفعالية؟')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">🗑️ حذف</button>
                    </form>
                </div>

            </div>
        </div>
        @empty
            <div class="alert alert-info text-center">
                لا توجد فعاليات
            </div>
        @endforelse

    </div>
</div>
@endsection

{{-- ================= STYLE 2026 ================= --}}
<style>
.card-2026{
    background:#ffffff;
    border-radius:20px;
    box-shadow:0 12px 28px rgba(0,0,0,.08);
    overflow:hidden;
    transition:.25s;
}
.card-2026:hover{
    transform:translateY(-4px);
    box-shadow:0 18px 36px rgba(0,0,0,.12);
}

/* STATUS */
.badge-status{
    position:absolute;
    top:14px;
    left:14px;
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
}
.status-active{
    background:#16a34a;
    color:#fff;
}
.status-inactive{
    background:#9ca3af;
    color:#fff;
}

/* IMAGE */
.event-img{
    width:100%;
    height:180px;
    object-fit:cover;
}
.no-image{
    height:180px;
    background:#f1f5f9;
    color:#64748b;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:6px;
    font-size:14px;
}

/* FOOTER */
.card-footer-2026{
    padding:12px 16px;
    display:flex;
    justify-content:space-between;
    border-top:1px solid #e5e7eb;
}
</style>

{{-- ================= JS SEARCH ================= --}}
<script>
document.getElementById('eventSearch').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll('.event-card').forEach(card => {
        card.style.display =
            card.innerText.toLowerCase().includes(value)
            ? 'block'
            : 'none';
    });
});
</script>
