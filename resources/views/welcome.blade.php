<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title> ديوان المركب المتعدد الرياضات لولاية ميلة</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">
    
    <style>
        body{
            font-family:'Cairo',sans-serif;
            background:#f7f8fb;
        }

        /* ===== 2026 Card System ===== */
        .card-2026{
            background:#fff;
            border-radius:22px;
            padding:22px;
            height:100%;
            box-shadow:0 12px 32px rgba(0,0,0,.08);
            transition:.35s;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
        }
        .card-2026:hover{
            transform:translateY(-6px);
            box-shadow:0 18px 45px rgba(0,0,0,.14);
        }

        .card-img-circle{
            width:88px;
            height:88px;
            border-radius:50%;
            overflow:hidden;
            margin:0 auto 14px;
        }
        .card-img-circle img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .section-title{
            font-weight:900;
            margin-bottom:30px;
        }

        .btn-2026{
            border-radius:999px;
            font-size:.85rem;
            padding:6px 18px;
        }

        @media(max-width:576px){
            .section-title{font-size:1.3rem;text-align:center}
        }
    </style>
</head>
<body>
@php
    if (app()->environment('local')) {
        $storageUrl = '/storage';
    } else {
        $storageUrl = rtrim(env('PUBLIC_STORAGE_URL'), '/');
    }
@endphp
<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-main sticky-top">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center gap-2" href="#top">
            <img src="{{ asset('images/djs-logo.png') }}" alt="Logo"
                 style="width:48px; height:48px; object-fit:contain;">
            <div class="d-flex flex-column lh-sm text-start">
                <span class="fw-bold" style="font-size:15px;">وزارة الرياضة - ولاية ميلة</span>
                <span style="font-size:14px;">ديوان المركب المتعدد الرياضات</span>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#about">حول المنصة</a></li>
                <li class="nav-item"><a class="nav-link" href="#news">المستجدات</a></li>
                <li class="nav-item"><a class="nav-link" href="#events">الفعاليات</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">الاتصال</a></li>
            </ul>
        </div>
    </div>
</nav>


<!-- HERO -->
<section class="hero" id="top">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <h1 class="hero-title">فضاء رقمي للمنخرطين والرياضة للجميع بولاية ميلة</h1>
                <p class="hero-subtitle mt-3">
                    منصة إلكترونية حديثة لتنظيم الأنشطة الرياضية، متابعة المنخرطين، وحجز المرافق عن بعد.
                </p>
            </div>
            <div class="col-md-5">
                <div class="hero-card text-center">
                    <img src="{{ asset('images/djs-logo.png') }}" class="hero-logo mb-3" alt="Logo">
                    <h5>OP O W Mila</h5>
                    <p class="mb-0">مرافقة النشاطات الرياضية عبر كامل ولاية ميلة.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- LOGIN / REGISTER BLOCK -->
<div class="container my-5">
    <h2 class="section-title mb-4 text-center">👇 اختر نوع الحساب للدخول أو التسجيل</h2>

    <div class="row g-4 justify-content-center">

        <!-- PERSON -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-user fa-3x text-primary mb-3"></i>
                <h5 class="fw-bold">حساب فردي</h5>
                <p class="text-muted small mb-3">للأشخاص الراغبين في ممارسة الرياضة وحجز الحصص.</p>
                <a class="btn btn-primary w-100 mb-2" href="{{ route('person.login') }}">دخول كفرد</a>
               
            </div>
        </div>

        <!-- CLUB -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-people-group fa-3x text-success mb-3"></i>
                <h5 class="fw-bold">نادي رياضي</h5>
                <p class="text-muted small mb-3">للأندية المعتمدة لإدارة لاعبيها وبرمجة التدريبات.</p>
                <a class="btn btn-success w-100 mb-2" href="{{ route('club.login') }}">دخول نادي</a>
            
            </div>
        </div>

        <!-- COMPANY -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-building fa-3x text-warning mb-3"></i>
                <h5 class="fw-bold">مؤسسة / شركة</h5>
                <p class="text-muted small mb-3">مخصص للمؤسسات الراغبة بحجز المرافق لموظفيها.</p>
                <a class="btn btn-warning text-white w-100 mb-2" href="{{ route('entreprise.login') }}">دخول مؤسسة</a>
                
            </div>
        </div>

        <!-- ADMIN -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-shield-halved fa-3x text-danger mb-3"></i>
                <h5 class="fw-bold">تسجيل دخول الإدارة</h5>
                <p class="text-muted small mb-3">مخصص فقط للمسؤلين  عن النظام وعمال الإدارة.</p> 
                <a class="btn btn-danger w-100" href="{{ route('admin.login') }}">دخول كـ Admin</a>
            </div>
        </div>

    </div>

 

<!-- NEWS -->
<section class="container my-5" id="news">
    <h2 class="section-title text-center">📰 آخر المستجدات</h2>

    <div class="row g-4">
        @forelse($news->where('is_active', 1) as $item)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card-2026 text-center">

                    <div class="card-img-circle">
                        <img src="{{ $item->image
                            ? $storageUrl.'/'.ltrim($item->image,'/')
                            : asset('images/placeholder.png') }}">
                    </div>

                    <h6 class="fw-bold">{{ $item->title }}</h6>

                    <p class="text-muted small">
                        {{ \Illuminate\Support\Str::limit($item->content,90) }}
                    </p>

                    <a href="{{ route('news.show',$item->id) }}"
                       class="btn btn-outline-primary btn-2026 mt-2">
                       اقرأ المزيد
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">لا توجد مستجدات</p>
        @endforelse
    </div>
</section>
<section class="container my-5" id="events">
    <h2 class="section-title text-center">📅 الفعاليات القادمة</h2>

    <div class="row g-4">
        @forelse($events->where('is_active', 1) as $item)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card-2026">

                    <div class="card-img-circle">
                        <img src="{{ $item->image
                            ? $storageUrl.'/'.ltrim($item->image,'/')
                            : asset('images/placeholder.png') }}">
                    </div>

                    <h6 class="fw-bold">
                        <i class="fa-solid fa-calendar-days me-2"></i>
                        {{ $item->title }}
                    </h6>

                    <span class="badge bg-success mb-2">
                       من {{ \Carbon\Carbon::parse($item->start_date)->format('d/m/Y') }}
                    </span>
                     <span class="badge bg-success mb-2">
                        إلى {{ \Carbon\Carbon::parse($item->end_date)->format('d/m/Y') }}
                    </span>
                    <p class="text-muted small">
                        {{ \Illuminate\Support\Str::limit($item->description,120) }}
                    </p>

                    <a href="{{ route('events.show',$item->id) }}"
                       class="btn btn-outline-success btn-2026 align-self-start">
                       تفاصيل الحدث
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">لا توجد فعاليات</p>
        @endforelse
    </div>
</section>
   

<!-- FOOTER -->
<footer class="footer-2026">
    <div class="container">
        <div class="row g-4 align-items-start">

            <!-- RIGHT: Institution Info -->
            <div class="col-12 col-md-4 text-md-end text-center">
                <h5 class="footer-title">ديوان المركب المتعدد الرياضات</h5>
                <p class="footer-text">
                    Office du parc omnisports<br>
                    de la wilaya de Mila
                </p>
                <p class="footer-text fw-bold">OPOW Mila</p>
            </div>

            <!-- CENTER: Contact & Social -->
            <div class="col-12 col-md-4 text-center">
                <h5 class="footer-title">تواصل معنا</h5>

                <p class="footer-text">
                    <i class="fa-solid fa-location-dot"></i>
                   ديوان المركب المتعدد الرياضات لولاية ميلة
                </p>

                <p class="footer-text">
                    <i class="fa-solid fa-envelope"></i>
                    contact@opowmila.dz
                </p>

                <div class="footer-social mt-3">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <!-- LEFT: Useful Links -->
            <div class="col-12 col-md-4 text-md-start text-center">
                <h5 class="footer-title">روابط مهمة</h5>
                <ul class="footer-links">
                    <li><a href="#">الموقع الرسمي للوزارة</a></li>
                    <li><a href="#">منصة مشاركة</a></li>
                    <li><a href="#">بوابة الفضاءات الشبانية</a></li>
                </ul>
            </div>

        </div>

        <!-- Bottom -->
        <div class="footer-bottom text-center mt-4">
 © 2025 – جميع الحقوق محفوظة | ديوان المركب المتعدد الرياضات لولاية ميلة
        </div>
    </div>
</footer>
<style>
 
.footer-2026{
    background: linear-gradient(135deg, #0a3d62, #0b5d57);
    color:#fff;
    padding:50px 0 25px;
    font-family: "Cairo", sans-serif;
}

.footer-title{
    font-weight:800;
    margin-bottom:15px;
}

.footer-text{
    font-size:0.9rem;
    opacity:.9;
    margin-bottom:6px;
}

.footer-links{
    list-style:none;
    padding:0;
    margin:0;
}
.footer-links li{
    margin-bottom:8px;
}
.footer-links a{
    color:#fff;
    text-decoration:none;
    font-size:0.9rem;
    opacity:.9;
    transition:.3s;
}
.footer-links a:hover{
    opacity:1;
    text-decoration:underline;
}

.footer-social a{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:38px;
    height:38px;
    border-radius:50%;
    background:rgba(255,255,255,.15);
    color:#fff;
    margin:0 6px;
    font-size:1rem;
    transition:.3s;
}
.footer-social a:hover{
    background:#fff;
    color:#0b5d57;
}

.footer-bottom{
    border-top:1px solid rgba(255,255,255,.2);
    padding-top:15px;
    font-size:0.85rem;
    opacity:.85;
}

/* Mobile adjustments */
@media(max-width:576px){
    .footer-title{
        font-size:1.1rem;
    }
}
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/welcome.js') }}"></script>

</body>
</html>
