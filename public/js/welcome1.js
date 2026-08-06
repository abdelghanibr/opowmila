
document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.querySelector('.activities-scroll-wrapper');
    const scrollContainer = document.querySelector('#activitiesScroll');
    
    let scrollSpeed = 0.25; // 🔥 Vitesse lente et élégante (ajustez entre 0.1 et 0.5)
    let isPaused = false;

    function autoScroll() {
        if (!isPaused && wrapper) {
            // Défilement de droite à gauche (RTL) → on augmente scrollLeft
            wrapper.scrollLeft += scrollSpeed;

            // Boucle infinie : quand on arrive à la fin, on revient au début
            if (wrapper.scrollLeft >= (scrollContainer.scrollWidth - wrapper.clientWidth)) {
                wrapper.scrollLeft = 0;
            }
        }
        requestAnimationFrame(autoScroll);
    }

    // Pause au survol (PC)
    wrapper.addEventListener('mouseenter', () => isPaused = true);
    wrapper.addEventListener('mouseleave', () => isPaused = false);

    // Pause au touch (mobile/tablette)
    wrapper.addEventListener('touchstart', () => isPaused = true);
    wrapper.addEventListener('touchend', () => isPaused = false);

    // Démarrage du défilement automatique
    autoScroll();
});

(function(){
    const slider = document.getElementById('activitiesSlider');
    if(!slider) return;

    // ===== Drag to scroll (mouse + touch) =====
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    slider.addEventListener('mousedown', (e) => {
        isDown = true;
        slider.style.cursor = 'grabbing';
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });

    window.addEventListener('mouseup', () => {
        isDown = false;
        slider.style.cursor = 'grab';
    });

    slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.style.cursor = 'grab';
    });

    slider.addEventListener('mousemove', (e) => {
        if(!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 1.4; // سرعة السحب
        slider.scrollLeft = scrollLeft - walk;
    });

    // ===== Buttons (desktop) =====
    const next = document.getElementById('actNext');
    const prev = document.getElementById('actPrev');

    function getCardStep(){
        const card = slider.querySelector('.activity-card');
        if(!card) return 320;
        const gap = 18;
        return card.getBoundingClientRect().width + gap;
    }

    next?.addEventListener('click', () => {
        slider.scrollBy({ left: getCardStep(), behavior: 'smooth' });
    });

    prev?.addEventListener('click', () => {
        slider.scrollBy({ left: -getCardStep(), behavior: 'smooth' });
    });
})();

function filterByType(type) {
    window.location.href = "/complexes?type=" + type;
}


document.addEventListener("DOMContentLoaded", function () {

    const modalBody = document.getElementById("complexModalBody");
    let lastLoadedType = null; // لحفظ نوع المركب الحالي
    let selectedComplexId = null; // لحفظ رقم المركب المختار

    // === 1) عند الضغط على فتح نافذة المركبات حسب النوع ===
    document.querySelectorAll('.open-complex-modal').forEach(el => {

        el.addEventListener('click', function () {

            let type = this.dataset.type;
            lastLoadedType = type;

            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3 text-muted">⏳ جاري جلب البيانات...</p>
                </div>
            `;

            fetch('/complexes/filter/' + type)
                .then(resp => resp.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    attachRegisterButtons(); // تفعيل أزرار تسجيل بعد التحميل
                })
                .catch(() => {
                    modalBody.innerHTML = `
                        <p class="text-danger text-center py-4">
                        ⚠ حدث خطأ أثناء جلب البيانات
                        </p>`;
                });

        });
    });


    // === 2) عند الضغط على زر "تسجيل" داخل كل بطاقة مركب ===
 function attachRegisterButtons() {

        document.querySelectorAll('[id^="btn-register-"]').forEach(btn => {

            btn.addEventListener('click', function () {

                selectedComplexId = this.dataset.complexId;
                  selectedComplexName = this.dataset.complexName;

              modalBody.innerHTML = loginTemplate(selectedComplexId, selectedComplexName);

                attachBackButton();
            });

        });
    }


 // === 3) قالب شاشة اختيار نوع الحساب مع إدراج ID المركب في الروابط ===
function loginTemplate(complexId, complexName) {

    injectLoginCSS(); // إضافة CSS تلقائياً

    return `
        <div class="text-center mb-4">
            <h4 class="title-2026">👇 اختر نوع الإنخراط للمتابعة</h4>
            <p class="text-muted">
                المنشأة المختارة:
                <strong style="color:#0077c8">${complexName}</strong>
            </p>
        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-12 col-md-4 col-lg-3">
                <div class="box-2026">
                    <div class="circle-icon person"><i class="fa-solid fa-user"></i></div>
                        <h5 class="fw-bold mt-2">الانخراط كممارس</h5>
                    <a class="btn-2026 person-btn" href="/person/register?complex=${complexId}">
                        التسجيل
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-4 col-lg-3">
                <div class="box-2026">
                    <div class="circle-icon club"><i class="fa-solid fa-people-group"></i></div>
                    <h5 class="fw-bold mt-2">الانخراط كنادي رياضي</h5>
                    <a class="btn-2026 club-btn" href="/club/register?complex=${complexId}">
                        التسجيل
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-4 col-lg-3">
                <div class="box-2026">
                    <div class="circle-icon company"><i class="fa-solid fa-building"></i></div>
                    <h5 class="fw-bold mt-2">الانخراط كشركة أو مؤسسة</h5>
                    <a class="btn-2026 company-btn" href="/entreprise/register?complex=${complexId}">
                        التسجيل
                    </a>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <button id="backToComplex" class="btn btn-secondary px-4">
                ← رجوع إلى المركبات
            </button>
        </div>
    `;
}


    // === 4) زر الرجوع إلى قائمة المركبات ===
    function attachBackButton() {
        let backBtn = document.getElementById("backToComplex");

        if (backBtn) {
            backBtn.addEventListener('click', function () {

                modalBody.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-3 text-muted">⏳ جاري التحميل...</p>
                    </div>
                `;

                fetch('/complexes/filter/' + lastLoadedType)
                    .then(resp => resp.text())
                    .then(html => {
                        modalBody.innerHTML = html;
                        attachRegisterButtons();
                    });
            });
        }
    }

});
function injectLoginCSS() {
    if (!document.getElementById("loginTemplateStyles")) {
        const style = document.createElement("style");
        style.id = "loginTemplateStyles";
        style.innerHTML = `
            .title-2026 {
                font-weight: 900;
                font-size: 1.8rem;
                text-align: center;
                margin-bottom: 15px;
            }

            .box-2026 {
                background: #ffffff;
                padding: 22px;
                border-radius: 18px;
                box-shadow: 0 12px 32px rgba(0,0,0,.08);
                text-align: center;
                transition: 0.3s ease;
            }
            .box-2026:hover {
                transform: translateY(-6px);
                box-shadow: 0 20px 45px rgba(0,0,0,.15);
            }

            .circle-icon {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                margin: auto;
                font-size: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                margin-bottom: 12px;
            }

            .circle-icon.person { background: linear-gradient(135deg,#2563eb,#3b82f6); }
            .circle-icon.club    { background: linear-gradient(135deg,#059669,#10b981); }
            .circle-icon.company { background: linear-gradient(135deg,#d97706,#f59e0b); }

            .btn-2026 {
                display: block;
                width: 100%;
                border-radius: 14px;
                padding: 10px;
                color: #fff;
                font-weight: 700;
                text-decoration: none;
                transition: 0.25s ease;
                margin-top: 10px;
            }

            .person-btn  { background: linear-gradient(135deg,#2563eb,#3b82f6); }
            .club-btn    { background: linear-gradient(135deg,#059669,#10b981); }
            .company-btn { background: linear-gradient(135deg,#d97706,#f59e0b); }

            .person-btn:hover  { background: #1e3a8a; }
            .club-btn:hover    { background: #047857; }
            .company-btn:hover { background: #b45309; }

            #backToComplex {
                margin-top: 20px;
                font-weight: 700;
            }
        `;
        document.head.appendChild(style);
    }
}
