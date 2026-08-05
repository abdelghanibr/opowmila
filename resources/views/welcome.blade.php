@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ديوان المركب المتعدد الرياضات لولاية ميلة</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

   <style>
    :root{
        --hero-green:#2c8b43;
        --hero-blue:#06284a;
        --white:#ffffff;
        --soft-white:rgba(255,255,255,.92);
        --menu-bg:rgba(255,255,255,.08);
        --menu-border:rgba(255,255,255,.14);
        --card-bg:rgba(255,255,255,.16);
        --shadow:0 12px 30px rgba(0,0,0,.14);
    }

    html{
        scroll-behavior:smooth;
    }

    body{
        margin:0;
        padding:0;
        font-family:'Cairo', sans-serif;
        background:#f7f9fc;
        color:#16324a;
    }

    a{
        text-decoration:none;
    }

    /* ===== Top menu ===== */
    .top-access-bar{
        position:sticky;
        top:0;
        z-index:1200;
        display:flex;
        flex-wrap:wrap;
        justify-content:center;
        gap:10px;
        padding:12px 18px;
        background:linear-gradient(90deg, var(--hero-green) 0%, var(--hero-blue) 100%);
        box-shadow:0 8px 20px rgba(0,0,0,.10);
    }

    .top-access-link{
        display:inline-flex;
        align-items:center;
        gap:8px;
        color:#fff;
        font-size:14px;
        font-weight:800;
        padding:10px 16px;
        border-radius:999px;
        background:var(--menu-bg);
        border:1px solid var(--menu-border);
        transition:.25s ease;
    }

    .top-access-link:hover{
        color:#fff;
        background:rgba(255,255,255,.16);
        transform:translateY(-2px);
    }

    .top-access-link.primary-link{
        background:#fff;
        color:var(--hero-blue);
        border-color:#fff;
    }

    .top-access-link.primary-link:hover{
        color:var(--hero-blue);
        background:#f2f6fb;
    }

    /* ===== HERO ===== */
    .hero{
        background:linear-gradient(90deg, var(--hero-green) 0%, #0e4c59 42%, var(--hero-blue) 100%);
        min-height:370px;
        display:flex;
        align-items:center;
        position:relative;
        overflow:hidden;
    }

    .hero .container{
        position:relative;
        z-index:2;
    }

    .hero-wrapper{
        min-height:300px;
        align-items:center !important;
    }

    .hero-text-block{
        text-align:right;
        color:#fff;
        display:flex;
        flex-direction:column;
        justify-content:center;
        height:100%;
    }

    .header-government h5,
    .header-government h6{
        color:#fff;
        margin:0;
        line-height:1.5;
        font-weight:800;
    }

    .header-government .tamazight{
        color:#1fb4ff;
        font-weight:900;
    }

    .hero-title{
        color:#fff;
        font-weight:900;
        font-size:3.1rem;
        line-height:1.25;
        margin:18px 0 14px;
        letter-spacing:0;
    }

    .hero-subtitle{
        color:var(--soft-white);
        font-size:1.15rem;
        font-weight:600;
        line-height:1.9;
        margin:0;
    }

    /* left card */
    .hero-card{
        background:var(--card-bg);
        border-radius:18px;
        padding:34px 28px;
        box-shadow:var(--shadow);
        backdrop-filter:blur(3px);
        text-align:center;
        max-width:530px;
        margin-inline:auto;
    }

    .hero-logo{
        width:110px;
        height:110px;
        object-fit:contain;
        margin-bottom:16px;
    }

    .hero-card h5{
        color:#fff;
        font-weight:800;
        font-size:1.05rem;
        margin-bottom:8px;
    }

    .hero-card p{
        color:#fff;
        font-size:1rem;
        font-weight:600;
        margin:0;
    }

    /* ===== centrer en mode PC ===== */
    @media (min-width: 992px){
        .hero .col-lg-7{
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .hero-text-block{
            width:100%;
            max-width:760px;
            margin:0 auto;
            text-align:center;
            padding-inline:20px;
        }

        .hero .col-lg-5{
            display:flex;
            align-items:center;
            justify-content:center;
        }
    }

    /* ===== Stats ===== */
.stats-section{
    margin-top:-18px;
    position:relative;
    z-index:5;
}

.mini-nav-card{
    display:flex;
    align-items:center;
    gap:14px;
    background:rgba(255,255,255,.96);
    border:1px solid rgba(15,39,71,.08);
    border-radius:18px;
    padding:14px 14px;
    min-height:92px;
    box-shadow:0 10px 24px rgba(15,39,71,.07);
    transition:all .25s ease;
    color:inherit;
    position:relative;
    overflow:hidden;
}

.mini-nav-card::before{
    content:"";
    position:absolute;
    top:0;
    right:0;
    left:0;
    height:3px;
    background:linear-gradient(90deg, #d6a319, #f1c85b);
    opacity:.95;
}

.mini-nav-card:hover{
    transform:translateY(-4px);
    box-shadow:0 16px 30px rgba(15,39,71,.12);
    border-color:rgba(15,39,71,.14);
}

.mini-nav-card-accent{
    background:linear-gradient(135deg, #fffaf0 0%, #fff4d9 100%);
    border-color:rgba(214,163,25,.20);
}

.mini-nav-icon{
    flex:0 0 50px;
    width:50px;
    height:50px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg, #0f2747, #143d6b);
    color:#fff;
    font-size:19px;
    box-shadow:0 8px 18px rgba(15,39,71,.16);
}

.mini-nav-card-accent .mini-nav-icon{
    background:linear-gradient(135deg, #d6a319, #f1c85b);
    color:#1f2430;
    box-shadow:0 8px 18px rgba(214,163,25,.20);
}

.mini-nav-content{
    flex:1;
    min-width:0;
}

.mini-nav-number{
    font-size:1.25rem;
    font-weight:900;
    line-height:1.1;
    color:#0f2747;
    margin-bottom:4px;
}

.mini-nav-title{
    font-size:.98rem;
    font-weight:900;
    color:#0f2747;
    line-height:1.3;
    margin-bottom:2px;
}

.mini-nav-sub{
    font-size:.82rem;
    font-weight:700;
    color:#6c7b8a;
    line-height:1.4;
}

.mini-nav-card:hover .mini-nav-sub{
    color:#0f2747;
}

@media (max-width: 991px){
    .mini-nav-card{
        min-height:86px;
        padding:13px 12px;
        gap:12px;
    }

    .mini-nav-icon{
        width:46px;
        height:46px;
        flex-basis:46px;
        font-size:17px;
    }

    .mini-nav-number{
        font-size:1.15rem;
    }

    .mini-nav-title{
        font-size:.92rem;
    }

    .mini-nav-sub{
        font-size:.78rem;
    }
}

@media (max-width: 575px){
    .mini-nav-card{
        flex-direction:column;
        text-align:center;
        justify-content:center;
        gap:10px;
        min-height:120px;
        padding:14px 10px;
        border-radius:16px;
    }

    .mini-nav-icon{
        width:44px;
        height:44px;
        flex-basis:44px;
        border-radius:12px;
        font-size:16px;
    }

    .mini-nav-number{
        font-size:1.05rem;
        margin-bottom:3px;
    }

    .mini-nav-title{
        font-size:.88rem;
        margin-bottom:1px;
    }

    .mini-nav-sub{
        font-size:.74rem;
    }
}
    /* ===== ticker ===== */
    .news-ticker{
        overflow:hidden;
        background:#fff;
        border-radius:16px;
        margin:28px auto 0;
        box-shadow:0 10px 22px rgba(0,0,0,.06);
        border:1px solid rgba(6,40,74,.06);
        padding:12px 0;
    }

    .ticker-track{
        display:flex;
        width:max-content;
        white-space:nowrap;
        animation:tickerMove 34s linear infinite;
        color:var(--hero-blue);
        font-weight:700;
        padding-inline:20px;
    }

    @keyframes tickerMove{
        0%{transform:translateX(0);}
        100%{transform:translateX(-50%);}
    }

    /* ===== sections ===== */
    .section-title{
        font-weight:900;
        color:var(--hero-blue);
        margin-bottom:28px;
    }

    .activities-section{
        padding:55px 0;
    }

    .facility-card,
    .card-2026{
        background:#fff;
        border-radius:22px;
        border:1px solid rgba(6,40,74,.06);
        box-shadow:0 10px 24px rgba(6,40,74,.08);
        transition:.25s ease;
        height:100%;
        padding:28px 24px;
    }

    .facility-card:hover,
    .card-2026:hover{
        transform:translateY(-5px);
        box-shadow:0 16px 28px rgba(6,40,74,.12);
    }

  .facility-icon-circle{
    width:120px;
    height:120px;
    border-radius:28px;
    display:flex;
    align-items:center;
    justify-content:center;

    background:linear-gradient(135deg, #2c8b43 0%, #16507c 100%);
    border:none;

    box-shadow:0 14px 28px rgba(44,139,67,.25);

    margin:auto;
    position:relative;
    overflow:hidden;
}

    .facility-icon-circle img{
        width:118px;
        height:118px;
        object-fit:cover;
        border-radius:50%;
    }

    .circle-thumb{
        width:150px;
        height:150px;
        object-fit:cover;
        border-radius:50%;
        box-shadow:0 10px 20px rgba(0,0,0,.10);
    }

    .btn-primary{
        background:linear-gradient(90deg, var(--hero-green), var(--hero-blue));
        border:none;
        border-radius:14px;
        font-weight:800;
        padding:10px 14px;
    }

    .btn-primary:hover{
        background:linear-gradient(90deg, var(--hero-blue), var(--hero-green));
    }

    .modal-content{
        border-radius:22px;
        overflow:hidden;
    }

    .modal-header.bg-primary{
        background:linear-gradient(90deg, var(--hero-green), var(--hero-blue)) !important;
    }

    /* ===== Footer ===== */
    .footer-2026{
        margin-top:60px;
        padding:50px 0 25px;
        background:linear-gradient(90deg, var(--hero-green), var(--hero-blue));
        color:#fff;
    }

    .footer-title{
        font-weight:900;
        margin-bottom:16px;
    }

    .footer-text{
        color:rgba(255,255,255,.94);
        margin-bottom:10px;
    }

    .footer-links{
        list-style:none;
        margin:0;
        padding:0;
    }

    .footer-links li{
        margin-bottom:10px;
    }

    .footer-links a{
        color:rgba(255,255,255,.94);
        font-weight:700;
    }

    .footer-links a:hover{
        color:#fff;
    }

    .footer-social{
        display:flex;
        justify-content:center;
        gap:12px;
    }

    .footer-social a{
        width:42px;
        height:42px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        background:rgba(255,255,255,.12);
        color:#fff;
        transition:.25s ease;
    }

    .footer-social a:hover{
        transform:translateY(-3px);
        background:rgba(255,255,255,.18);
        color:#fff;
    }

    .footer-bottom{
        border-top:1px solid rgba(255,255,255,.14);
        padding-top:16px;
        margin-top:18px;
        color:rgba(255,255,255,.92);
        font-weight:700;
    }

    @media (max-width: 1200px){
        .hero-title{
            font-size:2.5rem;
        }
    }

    @media (max-width: 991.98px){
        .hero{
            min-height:auto;
            padding:55px 0;
        }

        .hero-title{
            font-size:2rem;
        }

        .hero-text-block{
            text-align:center;
            padding-inline:10px;
        }

        .hero-card{
            margin-top:18px;
        }

        .facility-icon-circle{
            width:115px;
            height:115px;
        }

        .facility-icon-circle img{
            width:103px;
            height:103px;
        }

        .circle-thumb{
            width:135px;
            height:135px;
        }
    }

    @media (max-width: 768px){
        .top-access-bar{
            gap:8px;
            padding:10px;
        }

        .top-access-link{
            font-size:13px;
            padding:8px 12px;
        }

        .hero-title{
            font-size:1.65rem;
            line-height:1.45;
        }

        .hero-subtitle{
            font-size:1rem;
        }

        .stat-number{
            font-size:24px;
        }

        .facility-card,
        .card-2026{
            padding:22px 18px;
        }

        .facility-icon-circle{
            width:105px;
            height:105px;
        }

        .facility-icon-circle img{
            width:94px;
            height:94px;
        }

        .circle-thumb{
            width:120px;
            height:120px;
        }
    }

    @media (max-width: 576px){
        .hero-title{
            font-size:1.45rem;
        }

        .hero-subtitle{
            font-size:.95rem;
            line-height:1.8;
        }

        .facility-icon-circle{
            width:95px;
            height:95px;
        }

        .facility-icon-circle img{
            width:84px;
            height:84px;
        }

        .circle-thumb{
            width:110px;
            height:110px;
        }
    }
    .hero-cta-wrap{
    display:flex;
    justify-content:center;
    margin-top:28px;
}

.hero-inscription-btn{
    display:inline-flex;
    align-items:center;
    gap:12px;
    padding:14px 24px;
    border-radius:999px;
    background:linear-gradient(135deg, #ffd84d 0%, #ffbf00 100%);
    color:#06284a;
    font-weight:900;
    font-size:1rem;
    box-shadow:0 14px 30px rgba(255, 191, 0, .30);
    border:2px solid rgba(255,255,255,.25);
    transition:all .25s ease;
    position:relative;
    overflow:hidden;
}

.hero-inscription-btn:hover{
    color:#06284a;
    transform:translateY(-3px) scale(1.02);
    box-shadow:0 18px 36px rgba(255, 191, 0, .40);
}

.hero-inscription-btn::before{
    content:"";
    position:absolute;
    top:0;
    left:-120%;
    width:80%;
    height:100%;
    background:linear-gradient(
        120deg,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,.45) 50%,
        rgba(255,255,255,0) 100%
    );
    transform:skewX(-20deg);
    animation:shineMove 2.8s infinite;
}

.btn-icon{
    width:34px;
    height:34px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(6, 40, 74, .10);
    font-size:14px;
    position:relative;
    z-index:1;
}

.hero-inscription-btn span:last-child{
    position:relative;
    z-index:1;
}

@keyframes shineMove{
    0%{
        left:-120%;
    }
    100%{
        left:140%;
    }
}

@media (max-width: 991.98px){
    .hero-cta-wrap{
        justify-content:center;
    }
}

@media (max-width: 576px){
    .hero-inscription-btn{
        font-size:.95rem;
        padding:12px 20px;
    }

    .btn-icon{
        width:30px;
        height:30px;
        font-size:13px;
    }
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

    $activeNews = $news->where('is_active', 1);
    $activeEvents = $events->where('is_active', 1);
@endphp



<section class="hero" id="top">
    <div class="container">
        <div class="row align-items-center g-4 hero-wrapper">

            {{-- bloc texte à droite --}}
            <div class="col-lg-7 order-1 order-lg-1">
                <div class="hero-text-block">
                    <div class="header-government mb-2">
                        <h5>الجمهورية الجزائرية الديمقراطية الشعبية</h5>
                    </div>

                    <div class="header-government mb-2">
                        <h6>وزارة الرياضة</h6>
                        <h6 class="tamazight">ⵜⴰⵙⴳⴰ ⵏ ⵜⵎⵓⵔⵜ ⵏ ⵉⵎⴰⵙⵙⵏ</h6>
                    </div>

                    <h1 class="hero-title">
                        ديوان المركب المتعدد الرياضات لولاية ميلة
                    </h1>

                    <p class="hero-subtitle">
                        منصة إلكترونية حديثة لتنظيم الأنشطة الرياضية، متابعة المنخرطين، وحجز المرافق عن بعد.
                    </p>
                </div>

            </div>

            {{-- carte/logo à gauche --}}
            <div class="col-lg-5 order-2 order-lg-2">
                <div class="hero-card">
                    <img src="{{ asset('images/djs-logo.png') }}" class="hero-logo" alt="Logo">
                    <h5>OPOW Mila</h5>
                    <p>Office du Parc Omnisports de la wilaya de Mila</p>
                                                        <div class="hero-cta-wrap mt-4">
    <a href="#facilities" class="hero-inscription-btn">
        <span class="btn-icon">
            <i class="fa-solid fa-arrow-down"></i>
        </span>
        <span>ابدأ التسجيل الآن</span>
    </a>
</div>
                </div>

            </div>

        </div>
    </div>

</div>
</section>
@php
    $activeNews = $news->where('is_active', 1);
@endphp

<div class="row">
    <div class="col-12">
        @if($activeNews->count())
            <div class="news-ticker">
                <div class="ticker-track">

                    <span>
                        @foreach($activeNews as $item)
                            {{ $item->title }} — {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 500) }}
                            @if(!$loop->last)
                                <span class="mx-4">♦</span>
                            @endif
                        @endforeach
                    </span>

                  
                </div>
            </div>
        @else
            <p class="text-center text-muted">لا توجد مستجدات</p>
        @endif
    </div>
</div>
<section class="stats-section py-4">
    <div class="container">
        <div class="row g-3 justify-content-center">

            <div class="col-6 col-md-3">
                <a href="#news" class="mini-nav-card">
                    <div class="mini-nav-icon">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div class="mini-nav-content">
                        <div class="mini-nav-number">{{ $activeNews->count() }}</div>
                        <div class="mini-nav-title">المستجدات</div>
                        <div class="mini-nav-sub">آخر الأخبار</div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-3">
                <a href="#events" class="mini-nav-card">
                    <div class="mini-nav-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div class="mini-nav-content">
                        <div class="mini-nav-number">{{ $activeEvents->count() }}</div>
                        <div class="mini-nav-title">الفعاليات</div>
                        <div class="mini-nav-sub">البرامج القادمة</div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-3">
                <a href="{{ ($matchesCount ?? 0) > 0 ? route('matches.public') : '#facilities' }}" class="mini-nav-card mini-nav-card-accent">
                    <div class="mini-nav-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div class="mini-nav-content">
                        <div class="mini-nav-number">{{ $matchesCount ?? 0 }}</div>
                        <div class="mini-nav-title">التذاكر</div>
                        <div class="mini-nav-sub">
                            {{ ($matchesCount ?? 0) > 0 ? 'شراء الآن' : 'استكشاف' }}
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-3">
                <a href="#facilities" class="mini-nav-card">
                    <div class="mini-nav-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div class="mini-nav-content">
                        <div class="mini-nav-number">3</div>
                        <div class="mini-nav-title">التسجيل</div>
                        <div class="mini-nav-sub">اختر المنشأة</div>
                    </div>
                </a>
            </div>

        </div>
    </div>
</section>



<section class="activities-section" id="facilities">
    <div class="container">
        <h4 class="fw-bold text-center mb-4">🏟️ اختر المنشأة الرياضية المراد التسجيل فيها</h4>

        <div class="row g-4 justify-content-center">

            <div class="col-12 col-md-6 col-lg-4">
                <div class="facility-card text-center">
                    <div class="facility-icon-circle">
                        <img src="{{ asset('images/icons/swimming.png') }}" alt="Swimming Icon">
                    </div>
                    <h5 class="fw-bold mt-3">المسابح</h5>
                    <p class="text-muted">مخصص للسباحة، التدريب، واللياقة المائية.</p>

                    <button class="btn btn-primary w-100 mt-2 open-complex-modal"
                            data-type="swimming"
                            data-bs-toggle="modal"
                            data-bs-target="#complexModal">
                        📝 تسجيل
                    </button>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="facility-card text-center">
                    <div class="facility-icon-circle">
                        <img src="{{ asset('images/icons/stadium.png') }}" alt="Stadium Icon">
                    </div>
                    <h5 class="fw-bold mt-3">الملاعب</h5>
                    <p class="text-muted">مخصص لكرة القدم والرياضات الجماعية.</p>

                    <a href="#" class="btn btn-primary w-100 mt-2 open-complex-modal"
                       data-type="stadium" data-bs-toggle="modal" data-bs-target="#complexModal">
                        📝 تسجيل
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="facility-card text-center">
                    <div class="facility-icon-circle">
                        <img src="{{ asset('images/icons/gym.png') }}" alt="Gym Icon">
                    </div>
                    <h5 class="fw-bold mt-3">القاعات الرياضية</h5>
                    <p class="text-muted">مخصصة للتدريب، اللياقة، وأنشطة indoor.</p>

                    <a href="#" class="btn btn-primary w-100 mt-2 open-complex-modal"
                       data-type="hall" data-bs-toggle="modal" data-bs-target="#complexModal">
                        📝 تسجيل
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<div class="modal fade" id="complexModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white d-flex justify-content-between">
                <h5 class="modal-title">🏟️ المركبات المتاحة</h5>
                <button class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">✖ إغلاق</button>
            </div>

            <div class="modal-body p-3" id="complexModalBody">
                <p class="text-center text-muted">جاري التحميل...</p>
            </div>
        </div>
    </div>
</div>

<section class="container my-5" id="news">
    <h2 class="section-title text-center">📰 آخر المستجدات</h2>

    <div class="row g-4">
        @forelse($news->where('is_active', 1) as $item)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card-2026 text-center">
                    <div class="d-flex justify-content-center align-items-center mb-3">
                        @if($item->image)
                            <img src="{{ asset($item->image) }}" alt="Photo" class="circle-thumb">
                        @else
                            <img src="{{ asset('images/avatar-placeholder.png') }}" alt="No photo" class="circle-thumb">
                        @endif
                    </div>

                    <h6 class="fw-bold">{{ $item->title }}</h6>
                    <p class="text-muted small">{{ \Illuminate\Support\Str::limit($item->content, 90) }}</p>

                    <a href="{{ route('news.show', $item->id) }}" class="btn btn-primary w-100 mt-2">
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
                <div class="card-2026 text-center">
                    <div class="d-flex justify-content-center align-items-center mb-3">
                        @if($item->image)
                            <img src="{{ asset($item->image) }}" alt="Event image" class="circle-thumb">
                        @else
                            <img src="{{ asset('images/avatar-placeholder.png') }}" alt="No image" class="circle-thumb">
                        @endif
                    </div>

                    <h6 class="fw-bold">
                        <i class="fa-solid fa-calendar-days me-2"></i>
                        {{ $item->title }}
                    </h6>

                    <div class="mb-2">
                        <span class="badge bg-success">
                            من {{ \Carbon\Carbon::parse($item->start_date)->format('d/m/Y') }}
                        </span>
                        <span class="badge bg-success">
                            إلى {{ \Carbon\Carbon::parse($item->end_date)->format('d/m/Y') }}
                        </span>
                    </div>

                    <p class="text-muted small">
                        {{ \Illuminate\Support\Str::limit($item->description, 120) }}
                    </p>

                    <a href="{{ route('events.show', $item->id) }}" class="btn btn-primary w-100 mt-2">
                        تفاصيل الحدث
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">لا توجد فعاليات</p>
        @endforelse
    </div>
</section>

<footer class="footer-2026" id="contact">
    <div class="container">
        <div class="row g-4 align-items-start">

            <div class="col-12 col-md-4 text-md-end text-center">
                <h5 class="footer-title">ديوان المركب المتعدد الرياضات</h5>
                <p class="footer-text">
                    Office du parc omnisports<br>
                    de la wilaya de Mila
                </p>
                <p class="footer-text fw-bold">OPOW Mila</p>
            </div>

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

            <div class="col-12 col-md-4 text-md-start text-center">
                <h5 class="footer-title">روابط مهمة</h5>
                <ul class="footer-links">
                    <li><a href="https://msport.gov.dz/">الموقع الرسمي للوزارة</a></li>
                    <li><a href="#">منصة مشاركة</a></li>
                    <li><a href="#">بوابة الفضاءات الشبانية</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom text-center mt-4">
            © 2026 – جميع الحقوق محفوظة | ديوان المركب المتعدد الرياضات لولاية ميلة
        </div>
    </div>
</footer>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("mobileMenuToggle");
    const menu = document.getElementById("topAccessBar");

    if (toggleBtn && menu) {
        toggleBtn.addEventListener("click", function () {
            toggleBtn.classList.toggle("active");
            menu.classList.toggle("show");
        });

        menu.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", function () {
                if (window.innerWidth <= 767) {
                    menu.classList.remove("show");
                    toggleBtn.classList.remove("active");
                }
            });
        });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/welcome1.js') }}"></script>
</body>
</html>
@endsection