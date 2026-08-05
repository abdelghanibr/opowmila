@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #1a3a5c;
    --primary-light: #2d5a87;
    --primary-dark: #0f2540;
    --success: #22c55e;
    --success-light: #dcfce7;
    --warning: #f59e0b;
    --warning-light: #fef3c7;
    --danger: #ef4444;
    --danger-light: #fee2e2;
    --info: #3b82f6;
    --info-light: #dbeafe;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --radius: 8px;
    --radius-sm: 6px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    --transition: all 0.2s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.ass-page {
    direction: rtl;
    font-family: 'Cairo', sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
    min-height: 100vh;
    padding: 20px;
    color: var(--gray-800);
    font-size: 14px;
    line-height: 1.6;
}

.ass-shell {
    background: #fff;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--shadow-md);
    max-width: 1600px;
    margin: 0 auto;
}

.ass-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--gray-100);
}

.ass-head-info {
    flex: 1;
    min-width: 200px;
}

.ass-title {
    margin: 0 0 4px 0;
    color: var(--primary);
    font-weight: 700;
    font-size: 24px;
    letter-spacing: -0.5px;
}

.ass-sub {
    color: var(--gray-500);
    font-weight: 400;
    font-size: 13px;
    margin: 0;
}

.ass-sub.warning {
    color: var(--warning);
    background: var(--warning-light);
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    margin-top: 8px;
    display: inline-block;
}

.ass-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-box {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 16px 20px;
    color: var(--gray-800);
    min-height: 90px;
    background: linear-gradient(135deg, #fafbfc 0%, #fff 100%);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.stat-box::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 4px;
    height: 100%;
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.stat-box:hover {
    box-shadow: var(--shadow);
    transform: translateY(-2px);
}

.stat-box strong {
    display: block;
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    color: var(--gray-900);
    margin-bottom: 8px;
}

.stat-box span {
    display: block;
    font-weight: 500;
    font-size: 13px;
    color: var(--gray-600);
}

.stat-box.bg-total::before { background: var(--primary); }
.stat-box.bg-pending::before { background: var(--warning); }
.stat-box.bg-assured::before { background: var(--success); }
.stat-box.bg-expired::before { background: var(--danger); }
.stat-box.bg-cancelled::before { background: var(--gray-400); }

.stat-box.bg-total { border-color: var(--primary); }
.stat-box.bg-pending { border-color: var(--warning); }
.stat-box.bg-assured { border-color: var(--success); }
.stat-box.bg-expired { border-color: var(--danger); }
.stat-box.bg-cancelled { border-color: var(--gray-400); }

.filter-card {
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 16px;
    margin-bottom: 24px;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-card input,
.filter-card select {
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    font-weight: 500;
    background: #fff;
    color: var(--gray-800);
    font-size: 13px;
    height: 40px;
    transition: var(--transition);
    font-family: 'Cairo', sans-serif;
}

.filter-card input:focus,
.filter-card select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
}

.filter-card input:disabled,
.filter-card select:disabled {
    background: var(--gray-100);
    cursor: not-allowed;
    opacity: 0.7;
}

.ass-btn {
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    padding: 10px 16px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    white-space: nowrap;
    min-height: 40px;
    background: #fff;
    color: var(--gray-700);
    transition: var(--transition);
    font-family: 'Cairo', sans-serif;
}

.ass-btn:hover {
    background: var(--gray-50);
    border-color: var(--gray-400);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.ass-btn:active {
    transform: translateY(0);
}

.ass-btn.green {
    background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
    color: #fff;
    border-color: var(--success);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
}

.ass-btn.green:hover {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
}

.ass-btn.blue {
    background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
    color: #fff;
    border-color: var(--info);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.ass-btn.blue:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.ass-btn.orange {
    background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
    color: #fff;
    border-color: var(--warning);
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.ass-btn.orange:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.ass-btn.red {
    background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
    color: #fff;
    border-color: var(--danger);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.ass-btn.red:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.ass-btn.gray {
    background: var(--gray-100);
    color: var(--gray-700);
}

.ass-btn.gray:hover {
    background: var(--gray-200);
}

.ass-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.ass-btn:disabled:hover {
    transform: none;
    box-shadow: none;
}

.table-wrap {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    padding: 16px;
    overflow: auto;
    background: #fff;
    box-shadow: var(--shadow-sm);
}

.dataTables_wrapper {
    direction: rtl;
    font-size: 13px;
}

.dataTables_filter input,
.dataTables_length select {
    border: 1px solid var(--gray-300) !important;
    border-radius: var(--radius-sm) !important;
    padding: 8px 12px !important;
    font-size: 13px !important;
    font-family: 'Cairo', sans-serif !important;
    transition: var(--transition) !important;
}

.dataTables_filter input:focus,
.dataTables_length select:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1) !important;
    outline: none !important;
}

.dt-buttons .dt-button {
    border: 1px solid var(--gray-300) !important;
    border-radius: var(--radius-sm) !important;
    padding: 8px 14px !important;
    background: linear-gradient(135deg, var(--gray-50) 0%, #fff 100%) !important;
    color: var(--primary) !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    font-family: 'Cairo', sans-serif !important;
    transition: var(--transition) !important;
    box-shadow: var(--shadow-sm) !important;
}

.dt-buttons .dt-button:hover {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%) !important;
    color: #fff !important;
    border-color: var(--primary) !important;
    box-shadow: var(--shadow-md) !important;
}

.ass-table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

.ass-table thead th {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%) !important;
    color: #fff !important;
    font-weight: 600 !important;
    border-bottom: none !important;
    text-align: right !important;
    white-space: nowrap;
    font-size: 12px !important;
    padding: 14px 12px !important;
    letter-spacing: 0.3px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.ass-table thead th:first-child {
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0 !important;
}

.ass-table thead th:last-child {
    border-radius: var(--radius-sm) 0 0 var(--radius-sm) !important;
}

.ass-table tbody td {
    font-weight: 400 !important;
    color: var(--gray-700);
    vertical-align: middle;
    white-space: nowrap;
    font-size: 13px !important;
    padding: 12px !important;
    border-bottom: 1px solid var(--gray-100) !important;
    transition: var(--transition);
}

.ass-table tbody tr:hover {
    background: var(--gray-50) !important;
}

.ass-table tbody tr:hover td {
    background: var(--gray-50);
}

.ass-table tbody tr:last-child td {
    border-bottom: none !important;
}

.badge-ass {
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 1px solid transparent;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.badge-ass.pending {
    background: var(--warning-light);
    color: #92400e;
    border-color: #fcd34d;
}

.badge-ass.assured {
    background: var(--success-light);
    color: #166534;
    border-color: #86efac;
}

.badge-ass.expired {
    background: var(--danger-light);
    color: #991b1b;
    border-color: #fca5a5;
}

.badge-ass.cancelled {
    background: var(--gray-100);
    color: var(--gray-600);
    border-color: var(--gray-300);
}

.ass-mini-btn {
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--gray-50) 0%, #fff 100%);
    color: var(--gray-600);
    padding: 6px 10px;
    font-weight: 500;
    font-size: 12px;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}

.ass-mini-btn:hover {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    border-color: var(--primary);
    box-shadow: var(--shadow);
    transform: translateY(-1px);
}

/* Modal Styles */
.ass-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    padding: 20px;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.ass-modal.show {
    display: flex !important;
}

.ass-modal-card {
    width: min(1200px, 95vw);
    max-height: 90vh;
    overflow: auto;
    background: #fff;
    border-radius: var(--radius);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid var(--gray-200);
    animation: slideUp 0.3s ease;
}

.ass-modal-head {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.ass-modal-head h5 {
    margin: 0;
    font-weight: 700;
    font-size: 18px;
    letter-spacing: -0.3px;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: var(--radius-sm);
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    transform: rotate(90deg);
}

.ass-modal-body {
    padding: 20px;
    font-size: 13px;
}

.candidate-note {
    background: linear-gradient(135deg, var(--info-light) 0%, #eff6ff 100%);
    border: 1px solid #93c5fd;
    color: #1e40af;
    border-radius: var(--radius);
    padding: 12px 16px;
    font-weight: 500;
    margin-bottom: 16px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.candidate-note::before {
    content: 'ℹ';
    font-size: 18px;
    flex-shrink: 0;
}

.selected-count {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    border-radius: 20px;
    padding: 6px 14px;
    font-weight: 600;
    display: inline-flex;
    margin-bottom: 12px;
    font-size: 13px;
    box-shadow: 0 2px 8px rgba(26, 58, 92, 0.3);
}

/* Alert Styles */
.alert {
    padding: 14px 18px;
    border-radius: var(--radius);
    margin-bottom: 16px;
    font-weight: 600;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: linear-gradient(135deg, var(--success-light) 0%, #dcfce7 100%);
    color: #166534;
    border: 1px solid #86efac;
}

.alert-danger {
    background: linear-gradient(135deg, var(--danger-light) 0%, #fee2e2 100%);
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Responsive Styles */
@media (max-width: 1200px) {
    .stat-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .filter-card {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .ass-head {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .ass-head-info {
        min-width: 100%;
    }
    
    .ass-actions {
        justify-content: flex-start;
    }
    
    .stat-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-card {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .ass-page {
        padding: 12px;
    }
    
    .ass-shell {
        padding: 16px;
    }
    
    .ass-title {
        font-size: 20px;
    }
    
    .stat-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stat-box {
        padding: 14px 16px;
        min-height: 80px;
    }
    
    .stat-box strong {
        font-size: 24px;
    }
    
    .filter-card {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .ass-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .ass-btn {
        width: 100%;
        justify-content: center;
    }
    
    .table-wrap {
        padding: 10px;
        margin: 0 -12px;
        border-radius: 0;
    }
    
    .ass-modal {
        padding: 10px;
    }
    
    .ass-modal-card {
        width: 100%;
        max-height: 95vh;
    }
    
    .ass-modal-head {
        padding: 12px 16px;
    }
    
    .ass-modal-head h5 {
        font-size: 16px;
    }
    
    .ass-modal-body {
        padding: 12px;
    }
    
    .dt-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    
    .dt-buttons .dt-button {
        flex: 1;
        min-width: calc(50% - 3px);
    }
}

@media (max-width: 480px) {
    .ass-page {
        padding: 8px;
    }
    
    .ass-shell {
        padding: 12px;
    }
    
    .ass-title {
        font-size: 18px;
    }
    
    .ass-sub {
        font-size: 12px;
    }
    
    .stat-box strong {
        font-size: 22px;
    }
    
    .stat-box span {
        font-size: 12px;
    }
    
    .ass-btn {
        padding: 8px 12px;
        min-height: 36px;
        font-size: 12px;
    }
    
    .badge-ass {
        font-size: 11px;
        padding: 3px 8px;
    }
    
    .candidate-note {
        font-size: 12px;
        padding: 10px 12px;
    }
    
    .selected-count {
        font-size: 12px;
        padding: 5px 12px;
    }
}

/* Print Styles */
@media print {
    .ass-page {
        background: #fff;
        padding: 0;
    }
    
    .ass-shell {
        box-shadow: none;
        border: none;
    }
    
    .ass-head,
    .filter-card,
    .table-wrap {
        display: none;
    }
    
    .ass-table thead th {
        background: #f3f4f6 !important;
        color: #111 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* Scrollbar Styles */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--gray-100);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--gray-300);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--gray-400);
}
</style>

<div class="ass-page">
    <div class="ass-shell">
        <div class="ass-head">
            <div class="ass-head-info">
                <h4 class="ass-title">تسيير التأمينات</h4>
                <p class="ass-sub">الإضافة اليدوية تتم من حجوزات نشطة ومدفوعة فقط، مع منع التكرار حسب الشخص وفترة التأمين.</p>
                @if(!empty($assignedComplexId))
                    <p class="ass-sub warning">ملاحظة: هذا المستخدم مرتبط بمنشأة محددة، لذلك تظهر وتعالج فقط بيانات هذه المنشأة.</p>
                @endif
            </div>
            <div class="ass-actions">
                <button type="button" class="ass-btn green" id="openCandidatesModal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    إضافة مؤمنين
                </button>
                <form id="bulkAssureForm" action="{{ route('admin.assurances.bulk-assure') }}" method="POST">
                    @csrf
                    <div id="bulkAssureInputs"></div>
                    <button type="submit" class="ass-btn orange" id="bulkAssureBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        تأمين المحددين
                    </button>
                </form>
                <form id="printSelectedForm" action="{{ route('admin.assurances.print-selected') }}" method="POST" target="_blank">
                    @csrf
                    <div id="printSelectedInputs"></div>
                    <button type="submit" class="ass-btn blue" id="printSelectedBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        طباعة المحددين
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="stat-grid">
            <div class="stat-box bg-total">
                <strong>{{ $stats['total'] ?? 0 }}</strong>
                <span>إجمالي السجلات</span>
            </div>
            <div class="stat-box bg-pending">
                <strong>{{ $stats['pending'] ?? 0 }}</strong>
                <span>معني بالتأمين</span>
            </div>
            <div class="stat-box bg-assured">
                <strong>{{ $stats['assured'] ?? 0 }}</strong>
                <span>مؤمن</span>
            </div>
            <div class="stat-box bg-expired">
                <strong>{{ $stats['expired'] ?? 0 }}</strong>
                <span>منتهي</span>
            </div>
            <div class="stat-box bg-cancelled">
                <strong>{{ $stats['cancelled'] ?? 0 }}</strong>
                <span>ملغى</span>
            </div>
        </div>

        <div class="filter-card">
            <div class="filter-group">
                <label>الحالة</label>
                <select id="filterStatus">
                    <option value="all">كل الحالات</option>
                    <option value="pending">معني بالتأمين</option>
                    <option value="assured">مؤمن</option>
                    <option value="expired">منتهي</option>
                    <option value="cancelled">ملغى</option>
                </select>
            </div>
            <div class="filter-group">
                <label>العملية</label>
                <select id="filterOperation">
                    <option value="all">كل العمليات</option>
                    <option value="new">تسجيل جديد</option>
                    <option value="renewal">تجديد</option>
                </select>
            </div>
            <div class="filter-group">
                <label>المنشأة</label>
                <select id="filterComplex" {{ !empty($assignedComplexId) ? 'disabled' : '' }}>
                    @if(empty($assignedComplexId))
                        <option value="all">كل المنشآت</option>
                    @endif
                    @foreach($complexes as $complex)
                        <option value="{{ $complex->id }}">{{ $complex->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>من تاريخ</label>
                <input type="date" id="filterStartDate">
            </div>
            <div class="filter-group">
                <label>إلى تاريخ</label>
                <input type="date" id="filterEndDate">
            </div>
        </div>

        <div class="table-wrap">
            <table id="assurancesTable" class="ass-table display nowrap">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAllAssurances"></th>
                        <th>#</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الاسم</th>
                        <th>اللقب</th>
                        <th>الهاتف</th>
                        <th>تاريخ الميلاد</th>
                        <th>المنشأة</th>
                        <th>فترة الحجز</th>
                        <th>فترة التأمين</th>
                        <th>الحالة</th>
                        <th>العملية</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Popup المرشحين --}}
<div class="ass-modal" id="candidatesModal" aria-hidden="true">
    <div class="ass-modal-card">
        <div class="ass-modal-head">
            <h5>إضافة أشخاص إلى جدول التأمين</h5>
            <button type="button" class="modal-close" id="closeCandidatesModal">×</button>
        </div>
        <div class="ass-modal-body">
            <div class="candidate-note">
                تظهر هنا فقط الأشخاص الذين لديهم حجز نشط غير منتهي وحالة الدفع paid، وغير موجودين في جدول التأمين لنفس الفترة.
            </div>

            <form id="storeCandidatesForm" action="{{ route('admin.assurances.store-selected') }}" method="POST">
                @csrf
                <div id="candidateInputs"></div>
                <span class="selected-count" id="candidateSelectedCount">0 محدد</span>
                <button type="submit" class="ass-btn green" id="storeCandidatesBtn" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                    حفظ المحددين في جدول التأمين
                </button>
            </form>

            <div class="table-wrap mt-3">
                <table id="candidatesTable" class="ass-table display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAllCandidates"></th>
                            <th>الاسم</th>
                            <th>اللقب</th>
                            <th>الهاتف</th>
                            <th>تاريخ الميلاد</th>
                            <th>المنشأة</th>
                            <th>فترة الحجز</th>
                            <th>العملية</th>
                            <th>تاريخ الإنشاء</th>
                            <th>رقم الحجز</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(function(){
    let selectedAssurances = new Set();
    let selectedReservations = new Set();

    const arLang = {
        processing: 'جاري المعالجة...',
        search: 'بحث:',
        lengthMenu: 'إظهار _MENU_',
        info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_',
        infoEmpty: 'لا توجد بيانات',
        infoFiltered: '(منتقاة من _MAX_)',
        loadingRecords: 'تحميل...',
        zeroRecords: 'لا توجد نتائج',
        emptyTable: 'لا توجد بيانات',
        paginate: {
            first: 'الأول',
            previous: 'السابق',
            next: 'التالي',
            last: 'الأخير'
        }
    };

    const table = $('#assurancesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
        ajax: {
            url: "{{ route('admin.assurances.data') }}",
            type: 'GET',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.operation_type = $('#filterOperation').val();
                d.complex_id = $('#filterComplex').val();
                d.start_date = $('#filterStartDate').val();
                d.end_date = $('#filterEndDate').val();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Erreur serveur: ' + (xhr.responseJSON?.error || 'Ajax DataTable'));
            }
        },
        columns: [
            {data: 'checkbox', orderable: false, searchable: false},
            {data: 'id'},
            {data: 'created_at'},
            {data: 'firstname'},
            {data: 'lastname'},
            {data: 'phone'},
            {data: 'birth_date'},
            {data: 'complex'},
            {data: 'reservation_period'},
            {data: 'assurance_period'},
            {data: 'status'},
            {data: 'operation_type'},
            {data: 'actions', orderable: false, searchable: false}
        ],
        dom: 'Blfrtip',
        buttons: [
            {extend: 'excelHtml5', text: 'تصدير Excel'},
            {extend: 'print', text: 'طباعة الجدول'}
        ],
        language: arLang,
        drawCallback: function() {
            $('.assurance-check').each(function() {
                this.checked = selectedAssurances.has(String(this.value));
            });
            updateAssuranceForms();
        }
    });

    $('#filterStatus, #filterOperation, #filterComplex, #filterStartDate, #filterEndDate').on('change', function() {
        table.ajax.reload();
    });

    $(document).on('change', '.assurance-check', function() {
        this.checked ? selectedAssurances.add(String(this.value)) : selectedAssurances.delete(String(this.value));
        updateAssuranceForms();
    });

    $('#checkAllAssurances').on('change', function() {
        $('.assurance-check').each(function() {
            $(this).prop('checked', $('#checkAllAssurances').prop('checked')).trigger('change');
        });
    });

    function updateAssuranceForms() {
        $('#bulkAssureInputs, #printSelectedInputs').empty();
        selectedAssurances.forEach(id => {
            $('#bulkAssureInputs').append(`<input type="hidden" name="ids[]" value="${id}">`);
            $('#printSelectedInputs').append(`<input type="hidden" name="ids[]" value="${id}">`);
        });
        $('#bulkAssureBtn, #printSelectedBtn').prop('disabled', selectedAssurances.size === 0);
    }

    let candidatesTable = null;

    $('#openCandidatesModal').on('click', function() {
        $('#candidatesModal').addClass('show').attr('aria-hidden', 'false');
        if (!candidatesTable) {
            initCandidatesTable();
        } else {
            candidatesTable.ajax.reload();
        }
    });

    $('#closeCandidatesModal').on('click', function() {
        $('#candidatesModal').removeClass('show').attr('aria-hidden', 'true');
    });

    $('#candidatesModal').on('click', function(e) {
        if (e.target.id === 'candidatesModal') {
            $('#closeCandidatesModal').click();
        }
    });

    function initCandidatesTable() {
        candidatesTable = $('#candidatesTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            ajax: {
                url: "{{ route('admin.assurances.candidates-data') }}",
                type: 'GET',
                data: function(d) {
                    d.complex_id = $('#filterComplex').val();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Erreur candidats: ' + (xhr.responseJSON?.error || 'Ajax'));
                }
            },
            columns: [
                {data: 'checkbox', orderable: false, searchable: false},
                {data: 'firstname'},
                {data: 'lastname'},
                {data: 'phone'},
                {data: 'birth_date'},
                {data: 'complex'},
                {data: 'reservation_period'},
                {data: 'operation_type'},
                {data: 'reservation_created_at'},
                {data: 'reservation_id'}
            ],
            dom: 'Blfrtip',
            buttons: [
                {extend: 'excelHtml5', text: 'Excel'},
                {extend: 'print', text: 'Print'}
            ],
            language: arLang,
            drawCallback: function() {
                $('.candidate-check').each(function() {
                    this.checked = selectedReservations.has(String(this.value));
                });
                updateCandidateForm();
            }
        });
    }

    $(document).on('change', '.candidate-check', function() {
        this.checked ? selectedReservations.add(String(this.value)) : selectedReservations.delete(String(this.value));
        updateCandidateForm();
    });

    $('#checkAllCandidates').on('change', function() {
        $('.candidate-check').each(function() {
            $(this).prop('checked', $('#checkAllCandidates').prop('checked')).trigger('change');
        });
    });

    function updateCandidateForm() {
        $('#candidateInputs').empty();
        selectedReservations.forEach(id => {
            $('#candidateInputs').append(`<input type="hidden" name="reservation_ids[]" value="${id}">`);
        });
        $('#candidateSelectedCount').text(selectedReservations.size + ' محدد');
        $('#storeCandidatesBtn').prop('disabled', selectedReservations.size === 0);
    }
});
</script>

@endsection