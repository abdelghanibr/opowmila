@php $isPdf = request('format') === 'pdf'; @endphp
@php $onlySummary = request('table') === 'summary'; @endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تصدير الحجوزات</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background:#fff; color:#0f172a; margin:0; padding:24px; }
    h3 { margin:0 0 4px; text-align:center; }
    .subtitle { text-align:center; color:#64748b; font-size:13px; margin-bottom:20px; }
    h4 { background:#0b3a63; color:#fff; padding:8px 12px; border-radius:6px; margin:24px 0 10px; font-size:14px; }
    table { width:100%; border-collapse:collapse; font-size:12px; }
    th { background:#075985; color:#fff; padding:7px 6px; border:1px solid #94a3b8; }
    td { padding:6px; border:1px solid #cbd5e1; text-align:center; }
    tr:nth-child(even) td { background:#f1f5f9; }
    .paid { color:#15803d; font-weight:700; }
    .unpaid { color:#475569; font-weight:700; }
    .btn-bar { text-align:center; margin:18px 0; }
    .btn-bar a, .btn-bar button { display:inline-block; margin:3px; padding:9px 20px; border:none; border-radius:8px; color:#fff; cursor:pointer; text-decoration:none; font-weight:700; }
    @media print {
        .no-print { display: none; }
        body { padding: 0; }
        h4 { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        tr:nth-child(even) td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
</head>
<body>

<div class="no-print btn-bar">
    <button onclick="window.print()" style="background:#dc2626;">🖨️ طباعة / حفظ PDF</button>
    <a href="{{ route('reservations.index') }}" style="background:#475569;">⬅ رجوع</a>
    <a href="{{ route('reservations.export', ['format' => 'xls']) }}" style="background:#16a34a;">📊 Excel</a>
</div>

<h3>ديوان المركب المتعدد الرياضات - ولاية ميلة</h3>
<div class="subtitle">تصدير الحجوزات - {{ now()->format('Y/m/d H:i') }}</div>

@if(!empty($userComplex))
    <div class="subtitle">المركب: {{ $userComplex->nom }}</div>
@endif

@if(!$onlySummary)
<h4>📋 قائمة الحجوزات ({{ $reservations->count() }})</h4>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>المستخدم</th>
            <th>النوع</th>
            <th>الفوج</th>
            <th>النشاط</th>
            <th>المركب</th>
            <th>تاريخ البداية</th>
            <th>تاريخ النهاية</th>
            <th>الأماكن</th>
            <th>السعر (دج)</th>
            <th>الحالة</th>
            <th>الدفع</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reservations as $i => $r)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $r->user->name ?? '—' }}</td>
            <td>{{ match($r->user->type ?? '') { 'club' => 'نادي', 'company' => 'مؤسسة', default => 'فرد' } }}</td>
            <td>{{ $r->schedule->groupe ?? '—' }}</td>
            <td>{{ $r->complexActivity->activity->title ?? '—' }}</td>
            <td>{{ $r->complexActivity->complex->nom ?? '—' }}</td>
            <td>{{ optional($r->start_date)->format('Y-m-d') }}</td>
            <td>{{ optional($r->end_date)->format('Y-m-d') }}</td>
            <td>{{ $r->qty_places ?? 1 }}</td>
            <td>{{ number_format($r->total_price ?? 0, 0, ',', ' ') }}</td>
            <td>{{ $r->statut }}</td>
            <td class="{{ $r->payment_status === 'paid' ? 'paid' : 'unpaid' }}">
                {{ $r->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}
            </td>
        </tr>
        @empty
        <tr><td colspan="12">لا توجد حجوزات</td></tr>
        @endforelse
    </tbody>
</table>
@endif

<h4>💰 المبالغ حسب المركب والنشاط</h4>
<table>
    <thead>
        <tr>
            <th>المركب</th>
            <th>النشاط</th>
            <th>الحجوزات</th>
            <th>المدفوع (دج)</th>
            <th>غير المدفوع (دج)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summary as $g)
        <tr>
            <td>{{ $g->complex }}</td>
            <td>{{ $g->activity }}</td>
            <td>{{ $g->count }}</td>
            <td class="paid">{{ number_format($g->paid, 0, ',', ' ') }}</td>
            <td class="unpaid">{{ number_format($g->unpaid, 0, ',', ' ') }}</td>
        </tr>
        @empty
        <tr><td colspan="5">لا توجد بيانات</td></tr>
        @endforelse
    </tbody>
</table>

@if($isPdf)
<script>window.onload = function () { setTimeout(function () { window.print(); }, 300); };</script>
@endif

</body>
</html>