@extends('layouts.app')

@section('content')

<style>
/* ===== OPOW CARDS ===== */
.opow-card{
    border:none;
    border-radius:14px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    margin-bottom:18px;
}
.opow-header{
    background:linear-gradient(135deg,#1b5e20,#2e7d32);
    color:#fff;
    padding:14px 18px;
    font-family:"Cairo",sans-serif;
    font-weight:700;
    font-size:15px;
}
.opow-body{
    padding:18px;
    font-family:"Cairo",sans-serif;
    font-size:14px;
}
.opow-section-title{
    font-weight:700;
    color:#1b5e20;
    margin-bottom:12px;
}
.opow-label{
    color:#2e7d32;
    font-weight:700;
}
.opow-table th{
    background:#f1f8f4;
    color:#1b5e20;
    font-weight:700;
}
.opow-table td{
    background:#fff;
}

/* ===== PAYMENT CARD ===== */
.payment-card{
    border:none;
    border-radius:16px;
    box-shadow:0 8px 22px rgba(0,0,0,.08);
}

/* ===== PRICE CARD ===== */
.price-card{
    border-radius:14px;
    background:linear-gradient(135deg,#198754,#20c997);
    color:#fff;
}

/* ===== RECEIPT ACTIONS 2026 ===== */
.receipt-title{
    font-weight:700;
    color:#14532d;
}
.btn2026{
    border:none;
    border-radius:10px;
    padding:9px 14px;
    font-size:13px;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:6px;
    transition:.2s;
}
.btn2026-blue{background:#0d6efd;color:#fff}
.btn2026-blue:hover{background:#0b5ed7}
.btn2026-green{background:#198754;color:#fff}
.btn2026-green:hover{background:#157347}
.btn2026-dark{background:#212529;color:#fff}
.btn2026-dark:hover{background:#111}

/* ===== MAIN BUTTON ===== */
.btn-main{
    background:#1b5e20;
    color:#fff;
    border:none;
    border-radius:8px;
}
.btn-main:hover{
    background:#145a1a;
    color:#fff;
    
}
.popup-email{

  display:none;
  position:fixed;

  top:0;
  left:0;

  width:100%;
  height:100%;

  background:rgba(0,0,0,0.5);

  justify-content:center;
  align-items:center;

  z-index:9999;
}

.popup-content{

  background:white;
  padding:25px;

  border-radius:10px;

  width:320px;

  box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.popup-content input{

  width:100%;
  padding:10px;

  margin-top:10px;
  margin-bottom:15px;

  border:1px solid #ddd;
  border-radius:6px;
}

.popup-actions{

  display:flex;
  justify-content:space-between;
}
</style>
<div class="container py-4" style="direction: rtl; text-align:right; max-width:900px">


    {{-- ================= STATUS ALERT ================= --}}
   @if($status === 'paid' || $status === 'success')
        <div class="alert alert-success border shadow-sm">
            <div class="fw-bold mb-1">✔ تم الدفع بنجاح</div>
           <small>
    {{ $action ?? 'نشكركم على إتمام عملية الدفع بنجاح. يمكنكم الاحتفاظ بهذا الوصل كمرجع رسمي.' }}
</small>
        </div>
    @elseif($status === 'failed')
        <div class="alert alert-danger border shadow-sm">
            <div class="fw-bold mb-1">❌ فشل في عملية الدفع</div>
         <small class="text-muted">
    {{ $action ?? 'لم تكتمل عملية الدفع. يرجى المحاولة مرة أخرى أو التواصل مع الدعم.' }}
</small>

        </div>
    @else
        <div class="alert alert-warning border shadow-sm">
            <div class="fw-bold mb-1">⏳ الدفع قيد المعالجة</div>
            <small>
    {{ $action ?? 'عملية الدفع لم تُؤكد بعد. يرجى الانتظار أو تحديث الصفحة لاحقًا.' }}
</small>

        </div>
    @endif
@if(session('success'))
    <div class="alert alert-success text-center">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger text-center">
        {{ session('error') }}
    </div>
@endif
    {{-- ================= DETAILS (PAID ONLY) ================= --}}
   @if($status === 'paid' || $status === 'success')

        <div class="row g-4">

            {{-- ================= USER INFO ================= --}}
          <div class="col-md-5">

    {{-- ====== USER CARD ====== --}}
    <div class="opow-card">
        <div class="opow-header">
            🏟️ ديوان المركب المتعدد الرياضات لولاية ميلة
        </div>

        <div class="opow-body">
            <div class="opow-section-title">👤 معلومات المستفيد</div>

            <p><span class="opow-label">الاسم:</span> {{ $user->name }}</p>
            <p><span class="opow-label">البريد الإلكتروني:</span> {{ $user->email }}</p>

            @if($user->phone)
                <p><span class="opow-label">الهاتف:</span> {{ $user->phone }}</p>
            @endif
        </div>
    </div>

    {{-- ====== RESERVATION CARD ====== --}}
    <div class="opow-card">
        <div class="opow-header">
            📅 تفاصيل الحجز
        </div>

        <div class="opow-body p-0">
            <table class="table opow-table text-center align-middle mb-0">
                <thead>
                    <tr>
                        <th>النشاط</th>
                          <th>المنشأة</th>
                        <th>مدة الحجز</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $reservation->complexActivity->activity->title ?? '—' }}</td>
                        <td>{{ $reservation->complexActivity->complex->nom ?? '—' }}</td>
                        <td>{{ $reservation->season->name ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

            
            {{-- ================= PAYMENT INFO ================= --}}
            <div class="col-md-7">
                <div class="card shadow-sm p-4">

                    <h6 class="fw-bold mb-3">💳 معلومات الدفع</h6>

                    <p><strong>رقم الوصل:</strong> {{ $payment->order_id ?? '—' }}</p>
                       <p>
    <strong>رقم العملية:</strong>
    {{   $order_id ?? '—' }}
</p>
<p>
    <strong>رمز الموافقة:</strong>
    {{ $approval_code ?? '—' }}
</p>


                   <p>
<strong>تاريخ الدفع:</strong>
{{ $payment->datetimesatim ? $payment->datetimesatim->format('Y-m-d H:i') : '-' }}
</p>
                    <p><strong>طريقة الدفع:</strong> بطاقة بنكية (SATIM)</p>

                    {{-- ===== PRICE CARD ===== --}}
                    <div class="card border-0 shadow-sm mb-3"
                         style="background: linear-gradient(135deg,#198754,#20c997); color:#fff">
                        <div class="card-body text-center py-4">
                            <div class="mb-2" style="font-size:14px; opacity:.9">
                                💰 المبلغ المدفوع
                            </div>
                            <div style="font-size:32px; font-weight:800">
                                {{ number_format($payment->amount,0) }} دج
                            </div>
                            <small style="opacity:.85">شامل كل الرسوم</small>
                        </div>
                    </div>

                   
                 

@if($status === 'paid' || $status === 'success')
<div class="receipt-actions mt-4">

    <div class="receipt-title mb-2">
        🧾 إدارة الوصل
    </div>

    <div class="d-flex flex-wrap gap-2">

        {{-- تحميل --}}
        <a href="{{ route('payment.receipt', $payment->order_id) }}"
           class="btn2026 btn2026-blue">
            <i class="fa-solid fa-file-arrow-down"></i>
            تحميل وصل الدفع
        </a>

        {{-- إرسال --}}
        <button type="button"
                class="btn2026 btn2026-green"
                onclick="openEmailPopup()">
            <i class="fa-solid fa-envelope"></i>
            إرسال بالبريد
        </button>

        {{-- طباعة --}}
        @if($reservation)
        <button type="button"
                class="btn2026 btn2026-dark"
                onclick="printReservation({{ $reservation->id }})">
            <i class="fa-solid fa-print"></i>
            طباعة وصل الحجز
        </button>
        @endif

    </div>

</div>
@endif



                    
                    
                    
                   <div class="text-center mt-4">
    <a href="{{ route('reservation.my-reservations') }}"
       class="btn btn-main btn-sm px-4">
        ⬅️ عرض الحجوزات
    </a>
</div>
<div class="text-center mt-3">

    <div style="font-size:13.5px; color:#14532d; margin-bottom:6px;">
        في حال وجود مشكلة في بطاقتك CIB أو الذهبية<br>
        يرجى الاتصال بمركز الدعم SATIM
    </div>

    <img src="{{ asset('images/app.png') }}"
         alt="SATIM 3020"
         style="height:48px">

</div>


                </div>
            </div>

        </div>

        <div class="text-center text-muted small mt-4">
            هذا الوصل تم إنشاؤه إلكترونيًا — {{ now()->format('Y-m-d H:i') }}
        </div>

    @else
{{-- ================= BACK BUTTON ================= --}}
<div class="text-center mt-4">
    <a href="{{ route('reservation.my-reservations') }}"
       class="btn btn-main btn-sm px-4">
        ⬅️ عرض الحجوزات
    </a>
    
    <div class="text-center mt-3">

    <div style="font-size:13.5px; color:#14532d; margin-bottom:6px;">
        في حال وجود مشكلة في بطاقتك CIB أو الذهبية<br>
        يرجى الاتصال بمركز الدعم SATIM
    </div>

    <img src="{{ asset('images/app.png') }}"
         alt="SATIM 3020"
         style="height:48px">

</div>
</div>


    @endif

</div>
<div id="emailPopup" class="popup-email">
    <div class="popup-content">

        <h3>إرسال الإيصال بالبريد</h3>

@if($payment)
<form method="POST"
      action="{{ route('payment.receipt.email', $payment->order_id) }}">
@endif

            @csrf

            <input type="email"
                   name="email"
                        value="{{ $user->email }}"
                   placeholder="example@email.com"
                   required>

            <div class="popup-actions">
                <button type="submit" class="btn2026 btn2026-green">
                    إرسال
                </button>

                <button type="button" onclick="closeEmailPopup()" class="btn2026 btn2026-red">
                    إلغاء
                </button>
            </div>

        </form>

    </div>
</div>
<script>

function openEmailPopup(){
    document.getElementById("emailPopup").style.display = "flex";
}

function closeEmailPopup(){
    document.getElementById("emailPopup").style.display = "none";
}

    function printReservation(id) {
        window.open("{{ url('/reservations') }}/" + id + "/print", "_blank");
    }
    

(function () {
  // منع حفظ هذه الصفحة في التاريخ كنسخة قابلة للرجوع
  if (window.history.replaceState) {
    window.history.replaceState(null, "", window.location.href);
  }

  // إضافة حالة وهمية ثم إجبار الرجوع يذهب إلى صفحة ثابتة
  window.history.pushState(null, "", window.location.href);

  window.addEventListener("popstate", function () {
    // هنا تختار أين تذهب عند ضغط Back
    window.location.replace("{{ route('reservation.my-reservations') }}");
  });
})();
    
</script>
@endsection
