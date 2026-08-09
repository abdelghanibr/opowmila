@extends('layouts.app')

@section('content')
<style>
/* ======== Modern Stepper 2025 ======== */
.profile-progress {
    width: 100%;
    height: 7px;
    background: #e5e7eb;
    border-radius: 50px;
    margin-bottom: 30px;
    position: relative;
}
.profile-progress-bar {
    background: linear-gradient(90deg, #198754, #23d36b);
    height: 7px;
    width: {{ ($step / 4) * 100 }}%;
    border-radius: 50px;
    transition: .4s ease;
}

.stepper-wrapper {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 10px;
}
.stepper-item {
    text-align: center;
    flex-shrink: 0;
    width: 110px;
}
.step-counter {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    margin: auto;
    font-size: 20px;
    font-weight: bold;
    background: #cfd1d4;
    color: #444;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: .3s;
}
.step-name {
    font-size: 13px;
    font-weight: 600;
    margin-top: 8px;
    white-space: nowrap;
    color: #6c757d;
}

.stepper-item.active .step-counter {
    background: #198754;
    color: #fff;
    transform: scale(1.07);
}
.stepper-item.active .step-name {
    color: #198754;
}
.stepper-item.completed .step-counter {
    background: #28a745;
    color: #fff;
}
.stepper-item.completed .step-name {
    color: #28a745;
}

/* 🔹 لإخفاء Scroll على الحاسوب */
.stepper-wrapper::-webkit-scrollbar {
    height: 5px;
}
.stepper-wrapper::-webkit-scrollbar-thumb {
    background: #198754;
    border-radius: 4px;
}

.box-area {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,.07);
}
</style>


<div class="container py-4" style="direction: rtl; text-align:right;">

    <!-- Progress -->
    <div class="profile-progress">
        <div class="profile-progress-bar"></div>
    </div>

    <!-- Stepper -->
    <div class="stepper-wrapper">
        @for ($i = 1; $i <= 4; $i++)
            <div class="stepper-item 
                {{ $step == $i ? 'active' : '' }}
                {{ $step > $i ? 'completed' : '' }}
            ">
                <div class="step-counter">
                    @if($i == 1) <i class="fa-solid fa-user"></i>
                    @elseif($i == 2) <i class="fa-solid fa-user-shield"></i>
                    @elseif($i == 3) <i class="fa-solid fa-info-circle"></i>
                    @elseif($i == 4) <i class="fa-solid fa-file-medical"></i>
                    @endif
                </div>
                <div class="step-name">
                    @if($i == 1) المعلومات الأساسية
                    @elseif($i == 2) معلومات الولي
                    @elseif($i == 3) معلومات إضافية
                    @elseif($i == 4) الوثائق المطلوبة
                    @endif
                </div>
            </div>
        @endfor
    </div>

    <!-- Errors -->
    @if ($errors->any())
        <div class="alert alert-danger text-right">
            ⚠ يرجى ملء كل الحقول المطلوبة بشكل صحيح.
        </div>
    @endif

    <!-- Step Content -->
    <div class="box-area">
        @include('profile.steps_parts.step' . $step)
    </div>

</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.box-area form');
    if (!form) return;

    var labels = {
        'firstname': 'الاسم',
        'lastname': 'اللقب',
        'birth_date': 'تاريخ الميلاد',
        'gender': 'الجنس',
        'handicap': 'الاحتياجات الخاصة',
        'tuteur_fullname': 'اسم الأب',
        'parent_firstname': 'اسم الولي',
        'parent_lastname': 'لقب الولي',
        'parent_phone': 'رقم هاتف الولي',
        'phone': 'رقم الهاتف',
        'address': 'العنوان',
        'education': 'الفئة داخل النادي / المؤسسة',
        'photo': 'الصورة الشمسية',
        'medical_certificate': 'الشهادة الطبية',
        'engagement': 'التعهد',
        'birth_certificate': 'شهادة الميلاد',
        'parental_authorization': 'التصريح الأبوي',
        'guardian_id_card': 'بطاقة الولي',
        'national_id_card': 'بطاقة التعريف الوطنية'
    };

    function markField(input, ok) {
        var group = input.closest('.mb-3') || input.closest('.col-lg-5') || input.closest('.col-12');
        if (!group) return;
        group.classList.remove('has-missing');
        var existing = group.querySelector('.js-missing-hint');
        if (existing) existing.remove();
        input.classList.remove('is-invalid');
        if (!ok) {
            group.classList.add('has-missing');
            input.classList.add('is-invalid');
            var hint = document.createElement('div');
            hint.className = 'text-danger small fw-bold mt-1 js-missing-hint';
            hint.textContent = '⚠ هذا الحقل مطلوب';
            group.appendChild(hint);
        }
    }

    function visible(el) {
        return el.offsetParent !== null || el.tagName === 'INPUT' && el.type === 'hidden';
    }

    form.addEventListener('submit', function (e) {
        var missing = [];
        var firstMissing = null;

        form.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (el) {
            var name = el.name;
            if (!labels[name]) return;
            if (el.type === 'radio') {
                var group = form.querySelectorAll('input[name="' + name + '"]');
                var checked = Array.prototype.some.call(group, function (r) { return r.checked; });
                group.forEach(function (r) { markField(r, checked); });
                if (!checked) {
                    missing.push(labels[name]);
                    if (!firstMissing) firstMissing = group[0];
                }
                return;
            }
            if (el.type === 'file') return; // fichiers gérés par step4
            if (el.type === 'hidden') return;

            var isRequired = el.hasAttribute('required') || el.getAttribute('required') !== null;
            var fieldRequired = isRequired || labels[name] && form.getAttribute('data-step') === '3' && (name === 'phone' || name === 'address');
            if (!fieldRequired) return;

            var val = el.value.trim();
            var ok = val !== '';
            markField(el, ok);
            if (!ok) {
                missing.push(labels[name]);
                if (!firstMissing) firstMissing = el;
            }
        });

        if (missing.length > 0) {
            e.preventDefault();
            e.stopPropagation();

            var alertBox = document.getElementById('js-missing-alert');
            if (!alertBox) {
                alertBox = document.createElement('div');
                alertBox.id = 'js-missing-alert';
                alertBox.className = 'alert alert-danger text-right';
                alertBox.style.cssText = 'margin-bottom:15px;';
                form.parentNode.insertBefore(alertBox, form);
            }
            alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> <strong>يرجى ملء الحقول التالية:</strong> ' + missing.join('، ');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

            if (firstMissing) {
                if (firstMissing.type === 'radio') {
                    var firstRadio = firstMissing;
                    firstRadio.focus();
                } else {
                    firstMissing.focus();
                }
            }
        } else {
            var alertBox = document.getElementById('js-missing-alert');
            if (alertBox) alertBox.remove();
        }
    });

    // Efface l'erreur dès que l'utilisateur corrige un champ
    form.addEventListener('input', function (e) {
        var el = e.target;
        if (el.name && labels[el.name] && el.value.trim() !== '') {
            markField(el, true);
        }
    });
    form.addEventListener('change', function (e) {
        var el = e.target;
        if (el.type === 'radio' && el.name && labels[el.name] && el.checked) {
            form.querySelectorAll('input[name="' + el.name + '"]').forEach(function (r) { markField(r, true); });
        }
    });
});
</script>
@endpush

@endsection
