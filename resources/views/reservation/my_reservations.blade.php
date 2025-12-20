@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right; max-width:1200px">

    {{-- 🟦 Header --}}
    <div class="p-3 mb-4"
         style="background: linear-gradient(to right, #0a4f88, #0a8a67);
                border-radius: 14px;
                color: #fff;
                font-weight:700;">
        <div class="d-flex justify-content-between align-items-center">
            <span>📋 حجوزاتي</span>
            <a href="{{ route('activities.index') }}" class="btn btn-light fw-bold">
                ➕ حجز جديد
            </a>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- 🖥️ Desktop (DataTable) --}}
    {{-- ========================= --}}
    <div class="d-none d-lg-block">
        <div class="card shadow-sm p-3">

            <table id="reservationsTable"
                   class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>النشاط</th>
                        <th>الموسم</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>الساعات</th>
                        <th>الأيام / الساعات</th>
                        <th>السعر</th>
                        <th>الحالة</th>
                        <th>الدفع</th>
                        <th>التحكم</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($reservations as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ optional(optional($r->complexActivity)->activity)->title ?? '—' }}</td>
                        <td>{{ optional($r->season)->name ?? '—' }}</td>
                        <td>{{ $r->start_date?->format('Y-m-d') }}</td>
                        <td>{{ $r->end_date?->format('Y-m-d') }}</td>
                        <td>{{ $r->duration_hours ?? '—' }}</td>

                        {{-- Slots --}}
                        <td>
                            @php
                                $slots = $r->time_slots;
                                if (isset($slots['day_number'])) $slots = [$slots];
                            @endphp
                            @foreach($slots ?? [] as $slot)
                                <div class="bg-light rounded px-2 py-1 mb-1">
                                    {{ $r->getDayName($slot['day_number']) }}
                                    {{ $slot['start'] }} → {{ $slot['end'] }}
                                </div>
                            @endforeach
                        </td>

                        <td>{{ number_format($r->total_price ?? 0) }} دج</td>

                        {{-- status --}}
                        <td>
                            <span class="badge
                                {{ $r->status === 'confirmed' ? 'bg-success' :
                                   ($r->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                {{ $r->status }}
                            </span>
                        </td>

                        {{-- payment --}}
                        <td>
                            <span class="badge {{ $r->etat_label['class'] }}">
                                {{ $r->etat_label['label'] }}
                            </span>
                        </td>

                        {{-- actions --}}
                        <td class="d-flex gap-1 justify-content-center flex-wrap">

                            {{-- 🔁 Renew --}}
                            @if($r->payment_status === 'paid')
                                <button class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#renewModal{{ $r->id }}">
                                   🔁 تجديد
                                </button>
                            @else
                                <a href="{{ route('payments.pay', $r->id) }}"
                                   class="btn btn-sm btn-success">
                                    💳 الدفع
                                </a>
                            @endif

                            {{-- 🖨️ Print --}}
                            @if($r->payment_status === 'paid')
                                <button class="btn btn-sm btn-outline-dark"
                                        onclick="printReservation({{ $r->id }})">
                                    طباعة وصل التسديد🖨️
                                </button>
                            @else
                                <button class="btn btn-sm btn-outline-secondary"
                                        onclick="alert('⚠️ يجب إتمام الدفع قبل الطباعة');">
                                    🖨️
                                </button>
                            @endif

                            {{-- 🗑️ Delete --}}
                            <form action="{{ route('reservations.destroy', $r->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('حذف الحجز؟');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>

                    {{-- 🔁 Modal Renew --}}
                    <div class="modal fade" id="renewModal{{ $r->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <form action="{{ route('reservations.renew.store', $r->id) }}" method="POST">
                                @csrf
                                <div class="modal-content" style="direction: rtl">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">🔁 تجديد الحجز</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <strong>النشاط:</strong>
                                            {{ optional(optional($r->complexActivity)->activity)->title ?? '—' }}
                                            <br>
                                            <strong>السعر:</strong>
                                            {{ number_format($r->total_price ?? 0) }} دج
                                        </div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>⚠️ {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div class="row g-3">

    {{-- الموسم --}}
    <div class="col-md-12">
        <label class="fw-bold">🏷️ اختر الموسم</label>
        <select name="season_id"
                id="seasonSelect{{ $r->id }}"
                class="form-control"
                required
                onchange="fillSeasonDates{{ $r->id }}(this)">
            <option value="">— اختر الموسم —</option>
            @foreach($seasons as $season)
                <option value="{{ $season->id }}"
                        data-start="{{ $season->date_debut }}"
                        data-end="{{ $season->date_fin }}">
                    {{ $season->name }}
                    ({{ $season->date_debut }} → {{ $season->date_fin }})
                </option>
            @endforeach
        </select>
        @error('season_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>


</div>



                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label>📅 من</label>
                                                <input type="date" name="start_date" id="startDate{{ $r->id }}"
                                                       class="form-control"
                                                       value="{{ now()->toDateString() }}"
                                                       min="{{ now()->toDateString() }}" required>
                                            </div>
                                            @error('start_date')
    <div class="invalid-feedback d-block">
        {{ $message }}
    </div>
@enderror

                                            <div class="col-md-6">
                                                <label>📅 إلى</label>
                                                <input type="date" name="end_date" id="endDate{{ $r->id }}"
                                                       class="form-control" required>
                                            </div>
                                            @error('end_date')
    <div class="invalid-feedback d-block">
        {{ $message }}
    </div>
@enderror


                                        </div>

                                        <hr>

                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="pay_now"
                                                   value="1"
                                                   id="payNow{{ $r->id }}">
                                            <label class="form-check-label" for="payNow{{ $r->id }}">
                                                💳 الدفع مباشرة بعد التجديد
                                            </label>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                      <button type="submit"
        id="renewSubmitBtn"
        class="btn btn-success">
    ✅ تأكيد التجديد
</button>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- 📱 Mobile / Tablet (Cards) --}}
    {{-- ========================= --}}
    <div class="d-block d-lg-none">
        @foreach($reservations as $r)
        <div class="card mobile-reservation-card mb-3">
            <div class="card-body">

                <div class="d-flex justify-content-between fw-bold">
                    <span>{{ optional(optional($r->complexActivity)->activity)->title ?? '—' }}</span>
                    <span class="badge {{ $r->etat_label['class'] }}">
                        {{ $r->etat_label['label'] }}
                    </span>
                </div>

                <div class="text-muted small my-1">
                    📅 {{ $r->start_date?->format('Y-m-d') }}
                    → {{ $r->end_date?->format('Y-m-d') }}
                </div>

                <div class="fw-bold my-2">
                    💰 {{ number_format($r->total_price ?? 0) }} دج
                </div>

                <div class="d-grid gap-2">
                    @if($r->payment_status === 'paid')
                        <button class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#renewModal{{ $r->id }}">
                            🔁 تجديد
                        </button>

                        <button class="btn btn-outline-dark"
                                onclick="printReservation({{ $r->id }})">
                            🖨️ طباعة
                        </button>
                    @else
                        <a href="{{ route('payments.pay', $r->id) }}"
                           class="btn btn-success">
                            💳 دفع
                        </a>

                        <button class="btn btn-outline-secondary"
                                onclick="alert('⚠️ يجب إتمام الدفع قبل الطباعة');">
                            🖨️ طباعة
                        </button>
                    @endif

                    <form action="{{ route('reservations.destroy', $r->id) }}"
                          method="POST"
                          onsubmit="return confirm('حذف الحجز؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">🗑️ حذف</button>
                    </form>
                </div>

            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection

{{-- ============================= --}}
{{-- CSS --}}
{{-- ============================= --}}
@push('css')
<style>
.mobile-reservation-card{
    border-radius:18px;
    box-shadow:0 10px 24px rgba(0,0,0,.08);
    border:1px solid #eef2f7;
}
</style>
@endpush

{{-- ============================= --}}
{{-- JS --}}
{{-- ============================= --}}
@push('js')
@include('admin.partials.datatable-script', ['tableId' => '#reservationsTable'])

<script>

  
function fillSeasonDates{{ $r->id }}(select) {
    let option = select.options[select.selectedIndex];

    document.getElementById('startDate{{ $r->id }}').value =
        option.getAttribute('data-start');

    document.getElementById('endDate{{ $r->id }}').value =
        option.getAttribute('data-end');
}


function printReservation(id) {
    window.open(
        "{{ url('/reservations') }}/" + id + "/print",
        "_blank",
        "width=900,height=1200"
    );
}


document.addEventListener('DOMContentLoaded', function () {

    const hasErrors = document.querySelector('.alert-danger');

    if (hasErrors) {
        const btn = document.getElementById('renewSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('disabled');
        }
    }

});


flatpickr(".date", {
    dateFormat: "d/m/Y",
    allowInput: true
});

</script>
@endpush
