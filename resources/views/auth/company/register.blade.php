@extends('layouts.app')

@section('title', 'تسجيل مؤسسة / شركة جديدة')

@section('content')

<div class="enterprise-register-bg">

    <div class="enterprise-register-box">

        <!-- Header -->
        <div class="text-center mb-3">
            <img src="{{ asset('images/djs-logo.png') }}" width="90">
            <h3 class="fw-bold mt-2">تسجيل مؤسسة / شركة</h3>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('entreprise.register.post') }}" enctype="multipart/form-data" id="companyRegisterForm" novalidate>
            @csrf
              @if($selectedComplex)
    <div class="alert alert-info text-center fw-bold">
        التسجيل في المركب: <span class="text-primary">{{ $selectedComplex->nom }}</span>
        <br>
        <small>رقم المركب: {{ $selectedComplex->id }}</small>
    </div>
@endif
            <div class="row g-4">

                <!-- Company Name -->
                <div class="col-md-6">
                    <label class="form-label">اسم الشركة</label>
                    <input type="text" name="name" id="company_name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror" required>

                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div id="company_name_feedback" class="mt-1" style="font-size:0.9rem;"></div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="company_email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" required>

                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div id="company_email_feedback" class="mt-1" style="font-size:0.9rem;"></div>
                </div>

                <!-- Complex -->

<input type="hidden" name="complex_id" value="{{ $complexId }}">

                <!-- Commercial number -->
                <div class="col-md-6">
                    <label class="form-label">رقم السجل التجاري</label>
                    <input type="text" name="commercial_number" id="company_commercial" value="{{ old('commercial_number') }}"
                        class="form-control @error('commercial_number') is-invalid @enderror" required>

                    @error('commercial_number')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div id="company_commercial_feedback" class="mt-1" style="font-size:0.9rem;"></div>
                </div>

                <!-- Documents -->
                <div class="col-md-6">
                    <label class="form-label">وثائق الشركة 📎</label>
                    <input type="file" name="attachments[]" id="company_attachments" multiple
                        class="form-control @error('attachments') is-invalid @enderror">

                    @error('attachments')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div id="company_attachments_feedback" class="mt-1" style="font-size:0.9rem;"></div>
                </div>

                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" id="company_password"
                        class="form-control @error('password') is-invalid @enderror" required>

                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div id="company_password_feedback" class="mt-1" style="font-size:0.9rem;"></div>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" id="company_password_confirmation" class="form-control" required>
                    <div id="company_password_confirmation_feedback" class="mt-1" style="font-size:0.9rem;"></div>
                </div>

                <!-- Not robot -->
             <div class="mt-3">
    <label class="fw-bold d-block mb-1">🔐 التحقق أنني لست روبوت :</label>

    <p class="text-muted mb-2">
        يرجى كتابة الكلمة التالية كما هي تماماً:
    </p>

    <!-- الكلمة داخل إطار جميل -->
    <div style="
        border: 2px dashed #007bff;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 1.3rem;
        font-weight: bold;
        text-align: center;
        background: #f8f9fa;
        color: #007bff;
        letter-spacing: 2px;
        margin-bottom: 15px;
        
    ">
        {{ $correctWord }}
    </div>

    <input type="text" 
           name="captcha_word" 
           id="company_captcha" 
           class="form-control text-center"
           placeholder="اكتب الكلمة هنا" 
           required>

    @error('captcha_word')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    <div id="company_captcha_feedback" class="mt-1" style="font-size:0.9rem;"></div>
</div>

                <!-- Privacy Zone -->
                <div class="privacy-zone-enterprise mt-4">

                    <div class="privacy-header">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>حماية المعطيات الشخصية</span>
                    </div>

                    <div class="privacy-content">

                        <p>
                            أوافق على جمع ومعالجة بياناتي طبقًا للقانون الجزائري رقم 18-07
                            المتعلق بحماية الأشخاص في مجال معالجة البيانات ذات الطابع الشخصي.
                        </p>

                        <div class="form-check mt-2">
                            <input class="form-check-input @error('privacy_policy') is-invalid @enderror"
                                type="checkbox" name="privacy_policy" id="company_privacy" value="1" required>

                            <label class="form-check-label fw-bold">
                                أوافق على <span class="privacy-link">سياسة حماية البيانات</span>
                            </label>

                            @error('privacy_policy')
                                <div class="invalid-feedback d-block">
                                    يجب الموافقة على السياسة
                                </div>
                            @enderror
                        </div>

                    </div>

                </div>

                <!-- Submit -->
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-success w-100 py-2">إنشاء الحساب</button>
                </div>

            </div>

            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    {{ $errors->first() }}
                </div>
            @endif

        </form>

        <p class="text-center mt-3">
            لديك حساب؟ <a href="{{ route('entreprise.login') }}">تسجيل الدخول</a>
        </p>

    </div>

</div>

{{-- Inline CSS --}}
<style>

.enterprise-register-bg {
    background:#e8f5e9;
    padding:30px;
}

.enterprise-register-box {
    background:white;
    width:95%;
    max-width:900px;
    margin:auto;
    padding:40px;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,0.12);
}

/* PRIVACY ZONE */
.privacy-zone-enterprise {
    background: linear-gradient(135deg, #0a4f88, #2563eb);
    border-radius:18px;
    padding:18px 20px;
    color:#ffffff;
    box-shadow:0 12px 30px rgba(0,0,0,.15);
}

.privacy-header {
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:900;
    font-size:15px;
    margin-bottom:10px;
}

.privacy-header i {
    font-size:20px;
    color:#e0f2fe;
}

.privacy-content {
    background:rgba(255,255,255,0.12);
    border-radius:14px;
    padding:14px 16px;
    font-size:14px;
    line-height:1.8;
}

.privacy-content p {
    margin-bottom:10px;
    color:#f0f9ff;
}

.privacy-link {
    color:#a7f3d0;
    font-weight:900;
    text-decoration:underline;
}

.privacy-link:hover {
    color:white;
}

</style>

@endsection

@push('js')
<script>
(function () {
    const form = document.getElementById('companyRegisterForm');
    const captchaWord = {!! json_encode(strtolower($correctWord)) !!};
    const checkEmailUrl = "{{ route('auth.check-email') }}";

    function el(id) { return document.getElementById(id); }

    const rules = {
        company_name: { check: v => v.trim().length >= 2, ok: 'اسم الشركة صحيح ✔', err: 'اسم الشركة مطلوب (حرفان على الأقل)' },
        company_email: { ok: 'البريد متوفر ✔', err: 'صيغة البريد الإلكتروني غير صحيحة', taken: 'هذا البريد الإلكتروني مستعمل بالفعل' },
        company_commercial: { check: v => v.trim().length >= 3, ok: 'رقم السجل التجاري صحيح ✔', err: 'رقم السجل التجاري مطلوب' },
        company_password: { check: v => v.length >= 6, ok: 'قوة كلمة المرور مقبولة ✔', err: 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' },
        company_password_confirmation: { check: v => v === el('company_password').value, ok: 'كلمتا المرور متطابقتان ✔', err: 'كلمتا المرور غير متطابقتين' },
        company_captcha: { check: v => v.trim().toLowerCase() === captchaWord, ok: 'التحقق صحيح ✔', err: 'الكلمة غير صحيحة، أعد كتابتها' },
    };

    function setState(input, feedback, ok, msg) {
        input.classList.remove('is-valid', 'is-invalid');
        feedback.classList.remove('text-success', 'text-danger', 'text-muted');
        if (ok) {
            input.classList.add('is-valid');
            feedback.classList.add('text-success');
            feedback.innerHTML = '✔ ' + msg;
        } else {
            input.classList.add('is-invalid');
            feedback.classList.add('text-danger');
            feedback.innerHTML = '✘ ' + msg;
        }
    }

    function pending(input, feedback, msg) {
        input.classList.remove('is-valid', 'is-invalid');
        feedback.classList.remove('text-success', 'text-danger');
        feedback.classList.add('text-muted');
        feedback.innerHTML = '⏳ ' + msg;
    }

    function checkEmailAvailability(email) {
        return fetch(checkEmailUrl + '?email=' + encodeURIComponent(email), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => d.available);
    }

    let emailTimer = null;
    function liveEmailCheck() {
        const input = el('company_email');
        const fb = el('company_email_feedback');
        const email = input.value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
            setState(input, fb, false, rules.company_email.err);
            return;
        }
        pending(input, fb, 'جاري التحقق من التوفر...');
        clearTimeout(emailTimer);
        emailTimer = setTimeout(() => {
            checkEmailAvailability(email).then(available => {
                setState(input, fb, available, available ? rules.company_email.ok : rules.company_email.taken);
            }).catch(() => setState(input, fb, true, rules.company_email.ok));
        }, 500);
    }

    function validateField(name) {
        const input = el(name);
        if (!input) return true;
        const fb = el(name + '_feedback');
        const rule = rules[name];
        if (!rule) return true;
        if (name === 'company_email') {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(input.value.trim())) {
                setState(input, fb, false, rule.err);
                return false;
            }
            return true; // disponibilité via AJAX au submit
        }
        const ok = rule.check(input.value);
        setState(input, fb, ok, ok ? rule.ok : rule.err);
        return ok;
    }

    function validateAttachments() {
        const input = el('company_attachments');
        const fb = el('company_attachments_feedback');
        const ok = input.files.length >= 1;
        setState(input, fb, ok, ok ? 'الوثائق مرفقة ✔' : 'يرجى إرفاق وثائق الشركة');
        return ok;
    }

    Object.keys(rules).forEach(function (name) {
        const input = el(name);
        if (input) input.addEventListener('input', function () { validateField(name); });
    });

    el('company_email').addEventListener('input', liveEmailCheck);
    el('company_attachments').addEventListener('change', validateAttachments);

    el('company_password').addEventListener('input', function () {
        if (el('company_password_confirmation').value !== '') validateField('company_password_confirmation');
    });

    form.addEventListener('submit', async function (e) {
        let allOk = true;

        for (const name of Object.keys(rules)) {
            if (name === 'company_email') {
                const input = el('company_email');
                const fb = el('company_email_feedback');
                const email = input.value.trim();
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
                    setState(input, fb, false, rules.company_email.err);
                    allOk = false;
                    continue;
                }
                pending(input, fb, 'جاري التحقق من التوفر...');
                try {
                    const available = await checkEmailAvailability(email);
                    if (!available) { setState(input, fb, false, rules.company_email.taken); allOk = false; }
                    else setState(input, fb, true, rules.company_email.ok);
                } catch (err) { setState(input, fb, true, rules.company_email.ok); }
                continue;
            }
            if (!validateField(name)) allOk = false;
        }

        if (!validateAttachments()) allOk = false;

        const privacy = el('company_privacy');
        if (privacy && !privacy.checked) {
            allOk = false;
            privacy.classList.add('is-invalid');
        } else if (privacy) {
            privacy.classList.remove('is-invalid');
        }

        if (!allOk) {
            e.preventDefault();
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();
</script>
@endpush
