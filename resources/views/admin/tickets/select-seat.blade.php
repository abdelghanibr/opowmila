@extends('layouts.app')

@section('content')

<style>
body { font-family: "Cairo", sans-serif !important; }

.match-box {
    background: #ffffff;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    margin-bottom: 25px;
}

/* ============== MODERN SEAT CARDS 2026 ============== */

.seat-container-2026 {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 22px;
}

.seat-card-2026 input[type=radio] { display: none; }

.seat-box-2026 {
    width: 200px;
    padding: 22px;
    border-radius: 18px;
    text-align: center;
    cursor: pointer;
    transition: 0.3s ease;
    background: rgba(255,255,255,0.45);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.3);
    box-shadow: 0 5px 18px rgba(0,0,0,0.12);
}

.seat-box-2026:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.seat-card-2026 input[type=radio]:checked + .seat-box-2026 {
    border-color: #e11d48;
    background: #ffe4e6;
    box-shadow: 0 0 16px rgba(225,29,72,0.6);
    transform: scale(1.07);
}

.seat-icon i { font-size: 40px; margin-bottom: 12px; }

/* Title */
.seat-title-2026 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
}

/* Price */
.seat-price-2026 {
    display: inline-block;
    padding: 8px 16px;
    background: white;
    font-size: 17px;
    font-weight: 600;
    border-radius: 10px;
    border: 2px solid black;
    margin-bottom: 10px;
}

/* Status */
.seat-status-2026 {
    font-size: 15px;
}
.available { color: #16a34a; }
.unavailable { color: #dc2626; }

/* CATEGORY COLORS */
.vip { background: linear-gradient(135deg, #f7d774, #f2b900); }
.premium { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; }
.basic { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
.regular { background: linear-gradient(135deg, #cbd5e1, #94a3b8); }

.reserve-btn {
    background: #004aad;
    color: #fff;
    padding: 12px 25px;
    border-radius: 10px;
    width: 100%;
    border: none;
    font-size: 16px;
}
.reserve-btn:hover { background: #007bff; }

.team-logo {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e0e6f1;
}
</style>

<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="fw-bold mb-4">🎟️ حجز تذكرة المباراة</h3>

    <!-- Match Box -->
    <div class="match-box text-center">
        <div class="row align-items-center">

            <div class="col-4">
                <img src="{{ asset($match->homeTeam->logo) }}" class="team-logo mb-2">
                <h6 class="fw-bold">{{ $match->homeTeam->name }}</h6>
            </div>

            <div class="col-4">
                <h4 class="fw-bold my-3">VS</h4>
                <p>🏟 {{ $match->complex->nom }}</p>
                <p>📅 {{ $match->match_date }}</p>
                <p>⏰ {{ $match->match_time }}</p>
            </div>

            <div class="col-4">
                <img src="{{ asset($match->awayTeam->logo) }}" class="team-logo mb-2">
                <h6 class="fw-bold">{{ $match->awayTeam->name }}</h6>
            </div>

        </div>
    </div>

    <!-- SEAT SELECTION -->
    <form id="selectSeatForm">

        <div class="seat-container-2026">

            @foreach($complexSeats as $s)

            @php
                $class = match($s->seatType->name) {
                    'VIP' => 'vip',
                    'Premium' => 'premium',
                    'Basic' => 'basic',
                    default => 'regular'
                };
            @endphp

            <label class="seat-card-2026">

                <input type="radio" name="seat_type_id" value="{{ $s->seat_type_id }}"
                       {{ $s->remaining > 0 ? '' : 'disabled' }}>

                <div class="seat-box-2026 {{ $class }}">

                    <div class="seat-icon">
                        @if($class == 'vip')
                            <i class="fa-solid fa-crown"></i>
                        @elseif($class == 'premium')
                            <i class="fa-solid fa-star"></i>
                        @elseif($class == 'basic')
                            <i class="fa-solid fa-chair"></i>
                        @else
                            <i class="fa-solid fa-circle-dot"></i>
                        @endif
                    </div>

                    <div class="seat-title-2026">{{ $s->seatType->name }}</div>

                    <div class="seat-price-2026">{{ number_format($s->seatType->price, 2) }} دج</div>

                    <div class="seat-status-2026 {{ $s->remaining > 0 ? 'available' : 'unavailable' }}">
                        @if($s->remaining > 0)
                            <i class="fa-solid fa-check-circle"></i> مقاعد متاحة
                        @else
                            <i class="fa-solid fa-xmark-circle"></i> غير متاح
                        @endif
                    </div>

                </div>
            </label>

            @endforeach

        </div>

        <div class="text-center mt-4">
            <button type="button" onclick="continueReservation()" class="btn btn-primary btn-lg rounded-pill px-5">
                تأكيد نوع المقعد
            </button>
        </div>

    </form>

    <!-- USER INFO FORM -->
    <form id="userInfoForm" action="{{ route('chargilypay.redirect') }}" method="POST" style="display:none;">
        @csrf



        <input type="hidden" name="match_id" value="{{ $match->id }}">
        <input type="hidden" id="selectedSeat" name="seat_type_id">
        <input type="hidden" name="amount" value="{{ number_format($s->seatType->price, 2) }}">
        <h5 class="fw-bold mt-5 mb-3">معلومات الزبون</h5>

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">رقم الهاتف</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">رقم الهوية / جواز السفر</label>
                <input type="text" name="identity_number" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">العمر</label>
                <input type="number" name="age" class="form-control" required>
            </div>

        </div>

        <button type="submit" class="reserve-btn mt-4">
            ✔ تأكيد الحجز
        </button>

    </form>

</div>

<script>
function continueReservation() {
    let selectedSeat = document.querySelector('input[name="seat_type_id"]:checked');

    if (!selectedSeat) {
        alert("❗ الرجاء اختيار نوع المقعد قبل المتابعة");
        return;
    }

    document.getElementById("selectedSeat").value = selectedSeat.value;

    document.getElementById("selectSeatForm").style.display = "none";
    document.getElementById("userInfoForm").style.display = "block";
}
</script>

@endsection
