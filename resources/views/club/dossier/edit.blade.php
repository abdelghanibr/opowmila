@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction:rtl;text-align:right;max-width:1000px">

<h3 class="fw-bold mb-4">✏️ تعديل ملف النادي</h3>
<div class="alert alert-info py-2 small">
    📎 يُرجى التأكد من أن حجم كل ملف مرفق لا يتجاوز <strong>5 ميغابايت (5MB)</strong>.
</div>

{{-- ================= FORM ================= --}}
<form id="dossierForm"
      action="{{ route('club.dossier.update') }}"
      method="POST"
      enctype="multipart/form-data">
@csrf
@method('PUT')

@php
$files = [
    'statut' => 'القانون الأساسي',
    'bureau_members' => 'قائمة أعضاء المكتب',
    'coaches_certificates' => 'شهادات المدربين',
    'federation_affiliation' => 'شهادة الانخراط في الرابطة',
    'insurance_certificate' => 'شهادة التأمين',
    'rules_book' => 'دفتر الشروط',
    'minutes_meeting' => 'محضر الجمعية',
    'exploitation_request' => 'طلب الاستغلال'
];

$attachments = json_decode($club->attachments ?? '{}', true);
@endphp

<div class="row g-3">

    {{-- ================== النشاط الرياضي للنادي ================== --}}
    <div class="col-md-6">
        <label class="fw-bold mb-1 d-block">🏅 النشاط الرياضي للنادي</label>
        <select name="activity_id" class="form-select">
            <option value="">— اختر النشاط —</option>
            @foreach($activities as $activity)
                <option value="{{ $activity->id }}"
                    {{ old('activity_id', $club->activity_id) == $activity->id ? 'selected' : '' }}>
                    {{ $activity->title }}
                </option>
            @endforeach
        </select>
        <div class="form-text">
            نشاط النادي يُعرض تلقائيًا على بطاقات كل أعضائه.
        </div>
        @error('activity_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- ================== اعتماد النادي ================== --}}
    <div class="col-md-6">
        <label class="fw-bold mb-1 d-block">📄 الإعتماد (recto + verso)</label>
        <input type="file"
               name="agrement"
               class="form-control"
               accept="application/pdf">
        @if(isset($attachments['agrement']))
            <a href="{{ asset($attachments['agrement']) }}"
               target="_blank"
               class="btn btn-sm btn-outline-success mt-1">
               👁 عرض الملف الحالي
            </a>
        @endif
    </div>

    {{-- ================== الملفات الأخرى ================== --}}
    @foreach($files as $key => $label)
        <div class="col-md-6">
            <label class="fw-bold mb-1 d-block">{{ $label }}</label>
            <input type="file"
                   name="{{ $key }}"
                   class="form-control">
            @if(isset($attachments[$key]))
                <a href="{{ asset($attachments[$key]) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-success mt-1">
                   👁 عرض الملف الحالي
                </a>
            @endif
        </div>
    @endforeach

    {{-- ================== التبرئة ================== --}}
    <div class="col-md-6">
        <label class="fw-bold mb-1 d-block">التبرئة</label>
        <input type="file"
               name="tabri2a"
               class="form-control"
               accept="image/jpeg,image/png,application/pdf">
        @if(isset($attachments['tabri2a']))
            <a href="{{ asset($attachments['tabri2a']) }}"
               target="_blank"
               class="btn btn-sm btn-outline-success mt-1">
               👁 عرض الملف الحالي
            </a>
        @endif
    </div>

</div>

{{-- ================= PROGRESS BAR ================= --}}
<div class="mt-4 d-none" id="uploadProgressWrapper">
    <div class="progress" style="height:26px;border-radius:30px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
             role="progressbar"
             style="width:0%"
             id="uploadProgressBar">
            0%
        </div>
    </div>
    <small class="text-muted d-block mt-1">
        ⏳ جارٍ حفظ الملفات…
    </small>
</div>

{{-- ================= ACTIONS ================= --}}
<div class="mt-5 d-flex justify-content-between">
    <a href="{{ route('club.dossier.index') }}"
       class="btn btn-secondary px-4">
       ⬅ رجوع
    </a>

    <button type="submit"
            class="btn btn-success px-5 fw-bold">
        💾 حفظ وإرسال
    </button>
</div>

</form>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
document.getElementById('dossierForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    const wrapper = document.getElementById('uploadProgressWrapper');
    const bar = document.getElementById('uploadProgressBar');

    // إظهار Progress
    wrapper.classList.remove('d-none');
    bar.style.width = '0%';
    bar.textContent = '0%';
    bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);

    xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            bar.style.width = percent + '%';
            bar.textContent = percent + '%';
        }
    };

    xhr.onload = function () {
        if (xhr.status === 200) {
            bar.classList.remove('progress-bar-animated');
            bar.classList.replace('bg-primary', 'bg-success');
            bar.textContent = '✅ تم الحفظ بنجاح';

            setTimeout(() => {
                window.location.href = "{{ route('club.dossier.index') }}";
            }, 900);
        } else {
            bar.classList.remove('progress-bar-animated');
            bar.classList.replace('bg-primary', 'bg-danger');
            bar.textContent = '❌ خطأ أثناء الحفظ';
        }
    };

    xhr.onerror = function () {
        bar.classList.remove('progress-bar-animated');
        bar.classList.replace('bg-primary', 'bg-danger');
        bar.textContent = '❌ فشل الاتصال';
    };

    xhr.send(formData);
});
</script>
@endsection
