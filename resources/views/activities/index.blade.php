@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <!-- 🟦 Header -->
    <div class="p-3 mb-4 activity-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>إدارة جميع النشاطات الخاصة بك هنا</span>
            <span class="fs-5">
                <i class="fa-solid fa-wave-pulse"></i> نشاطاتي
            </span>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- 🔍 البحث + الفئة (Responsive) --}}
    {{-- ===================== --}}
    <form method="GET"
          action="{{ route('activities.index') }}"
          class="row g-2 mb-4">

        {{-- البحث --}}
      <input name="search"
       id="searchInput"
       type="text"
       class="form-control"
       value="{{ request('search') }}"
       placeholder="ابحث عن نشاط...">

        {{-- الفئة --}}
        <div class="col-12 col-md-6">
            <select name="category_id"
                    class="form-select"
                    onchange="this.form.submit()">
                <option value="">كل الفئات</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </form>

    <!-- 🧩 Activities Cards -->
    <div class="row g-4">

        @forelse ($activities as $a)
        <div class="col-12 col-sm-6 col-lg-4">

            <div class="card activity-card shadow-sm h-100"
                 style="border-top:4px solid {{ $a->color }};">

                {{-- الصورة --}}
                @if($a->icon)
                    <div class="activity-img">
                        <img src="{{ $a->icon }}"
                             alt="Activity Icon"
                             onerror="this.src='{{ asset('images/default-activity.png') }}'">
                    </div>
                @else
                    <div class="activity-img placeholder">
                        <i class="fa-regular fa-image fa-2x"></i>
                        <span class="ms-2">لا توجد صورة</span>
                    </div>
                @endif

                <div class="card-body d-flex flex-column">

                    <h5 class="fw-bold mb-1" style="color: {{ $a->color }};">
                        {{ $a->title }}
                    </h5>

                    <span class="badge bg-primary align-self-start mb-2">
                        {{ $a->activityCategory->name ?? 'بدون فئة' }}
                    </span>

                    <p class="text-muted flex-grow-1">
                        {{ Str::limit($a->description, 90) }}
                    </p>

                    <a href="{{ route('activities.complexes', $a->id) }}"
                       class="btn btn-success btn-sm mt-auto w-100">
                        <i class="fa-solid fa-pen-to-square ms-1"></i>
                        تسجيل في النشاط
                    </a>

                </div>
            </div>
        </div>

        @empty
            <div class="alert alert-info text-center">
                لا توجد نشاطات متاحة حالياً.
            </div>
        @endforelse

    </div>
</div>

@endsection

{{-- ===================== --}}
{{-- 🎨 CSS Responsive --}}
{{-- ===================== --}}
@push('css')
<style>
/* Header */
.activity-header {
    background: linear-gradient(to right, #0a4f88, #0a8a67);
    border-radius: 14px;
    color: #fff;
    font-weight: 700;
}

/* Card */
.activity-card {
    border-radius: 18px;
    transition: transform .2s ease, box-shadow .2s ease;
}

.activity-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 30px rgba(0,0,0,.12);
}

/* Image */
.activity-img {
    height: 180px;
    overflow: hidden;
    background: #f1f5f9;
}

.activity-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Placeholder */
.activity-img.placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #888;
    font-weight: 600;
}

/* Mobile tweaks */
@media (max-width: 575px) {
    .activity-img {
        height: 150px;
    }
    .activity-header {
        text-align: center;
    }
}
</style>
@endpush
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('searchInput');
    let timer = null;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            input.form.submit();
        }, 500); // نصف ثانية بعد التوقف عن الكتابة
    });
});
</script>
@endpush
