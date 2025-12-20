@extends('layouts.app')

@section('content')
<style>
    body { font-family: "Cairo", sans-serif !important; }

    .dash-header {
        background: #9d1421;
        color: #fff;
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .dash-card {
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        background: #ffffff;
        border: 2px solid #e8eef3;
        transition: .25s;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        min-height: 220px;
    }

    .dash-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        border-color: #0a4f88;
    }

    .dash-icon {
        font-size: 45px;
        margin-bottom: 10px;
        color: #0a4f88;
    }

    .count-box {
        background: #f1f7fc;
        padding: 6px 12px;
        font-size: 14px;
        margin-top: 10px;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid #d8e4ef;
    }
</style>

<div class="container py-4" style="direction: rtl; text-align:right;">

    <div class="dash-header mb-4">
        <h3 class="fw-bold">🎯 أهلاً بك مدير النظام {{ Auth::user()->name }}!</h3>
        <p class="mb-0">يمكنك هنا إدارة الملفات والأنشطة والمشتركين بسهولة</p>
    </div>

    <div class="row g-4">

        <!-- 📂 ملفات المشتركين -->
        <div class="col-md-3">
            <a href="{{ route('admin.dossiers.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">🗂️</div>
                    <h6 class="fw-bold">ملفات المشتركين</h6>
                    <div class="count-box">الإجمالي: {{ $dossiersCount }}</div>
                </div>
            </a>
        </div>

        <!-- 🏊 النوادي الرياضية -->
        <div class="col-md-3">
            <a href="{{ route('admin.clubs.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">🏊‍♂️</div>
                    <h6 class="fw-bold">النوادي الرياضية</h6>
                    <div class="count-box">عدد النوادي: {{ $clubsCount }}</div>
                </div>
            </a>
        </div>

        <!-- 👑 المسؤولون -->
        <div class="col-md-3">
            <a href="{{ route('admins.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">👑</div>
                    <h6 class="fw-bold">المسؤولون</h6>
                    <div class="count-box">عدد المسؤولين: {{ $adminsCount ?? 0 }}</div>
                </div>
            </a>
        </div>

        <!-- 👥 الأفراد -->
        <div class="col-md-3">
            <a href="{{ route('persons.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">👥</div>
                    <h6 class="fw-bold">الأفراد</h6>
                    <div class="count-box">عدد الأفراد: {{ $personsCount }}</div>
                </div>
            </a>
        </div>

         <div class="col-md-3">
            <a href="{{ route('age-categories.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">🧒</div>
                    <h6 class="fw-bold">الفيئات العمرية</h6>
                 <div class="count-box">عدد الفئات: {{ \App\Models\AgeCategory::count() }}</div>
                </div>
            </a>
        </div>

        <!-- 🏋️ الأنشطة -->
        <div class="col-md-3">
            <a href="{{ route('admin.activities.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">🏋️‍♂️</div>
                    <h6 class="fw-bold">الأنشطة الرياضية</h6>
                    <div class="count-box">عدد الأنشطة: {{ \App\Models\Activity::count() }}</div>
                </div>
            </a>
        </div>
<div class="col-md-3">
    <a href="{{ route('activity-categories.index') }}" class="text-decoration-none text-dark">
        <div class="dash-card">
            <div class="dash-icon">🧩</div>
            <h6 class="fw-bold"> انواع النشاطات  </h6>
            <div class="count-box">
                {{ \App\Models\ActivityCategory::count() }}
            </div>
        </div>
    </a>
</div>
        <!-- 🏟 المركبات -->
        <div class="col-md-3">
            <a href="{{ route('admin.complexes.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">🏟️</div>
                    <h6 class="fw-bold">المركبات الرياضية</h6>
                    <div class="count-box">عدد المركبات: {{ \App\Models\Complex::count() }}</div>
                </div>
            </a>
        </div>

        <!-- 💰 التسعير -->
        <div class="col-md-3">
            <a href="{{ route('admin.pricing_plans.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">💵</div>
                    <h6 class="fw-bold">خطط التسعير</h6>
                    <div class="count-box">عدد الخطط: {{ \App\Models\PricingPlan::count() }}</div>
                </div>
            </a>
        </div>

      <div class="col-md-3">
    <a href="{{ route('admin.capacities.index') }}" class="text-decoration-none text-dark">
        <div class="dash-card">
            <div class="dash-icon">🏫</div>
            <h6 class="fw-bold">إسناد الأنشطة للمؤسسات</h6>
            <div class="count-box">
                {{ \App\Models\ComplexActivity::count() }}
            </div>
        </div>
    </a>
</div>

<div class="col-md-3">
    <a href="{{ route('admin.schedules.index') }}" class="text-decoration-none text-dark">
        <div class="dash-card">
            <div class="dash-icon">⏰</div>
            <h6 class="fw-bold">الجداول الزمنية</h6>
            <div class="count-box">
                {{ \App\Models\Schedule::count() }}
            </div>
        </div>
    </a>
</div>
<div class="col-md-3">
    <a href="{{ route('reservations.index') }}" class="text-decoration-none text-dark">
        <div class="dash-card">
            <div class="dash-icon">📝</div>
            <h6 class="fw-bold"> الحجوزات  </h6>
            <div class="count-box">
                {{ \App\Models\Reservation::count() }}
            </div>
        </div>
    </a>
</div>
<div class="col-md-3">
    <a href="{{ route('seasons.index') }}" class="text-decoration-none text-dark">
        <div class="dash-card">
          <div class="dash-icon">🗓️</div>
            <h6 class="fw-bold"> الفصول  </h6>
            <div class="count-box">
                {{ \App\Models\Season::count() }}
            </div>
        </div>
    </a>
</div>


<div class="col-md-3">
    <a href="{{ route('news.index') }}" class="text-decoration-none text-dark">
        <div class="dash-card">
            <div class="dash-icon">📰</div>
            <h6 class="fw-bold">الأخبار</h6>
            <div class="count-box">
                {{ \App\Models\News::count() }}
            </div>
        </div>
    </a>
</div>


<div class="col-md-3">
    <a href="{{ route('events.index') }}" class="text-decoration-none text-dark">
        <div class="dash-card">
            <div class="dash-icon">🎉</div>
            <h6 class="fw-bold">الأحداث</h6>
            <div class="count-box">
                {{ \App\Models\Event::count() }}
            </div>
        </div>
    </a>
</div>



    </div>
</div>

@endsection
