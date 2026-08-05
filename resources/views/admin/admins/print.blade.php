<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قائمة الأشخاص المؤمنين</title>
    <style>
        body{font-family:DejaVu Sans,Arial,Tahoma,sans-serif;direction:rtl;color:#111827;margin:24px;background:#fff}.print-btn{background:#082f57;color:#fff;border:0;border-radius:8px;padding:8px 14px;font-weight:bold;cursor:pointer;margin-bottom:14px}.header{display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #082f57;padding-bottom:12px;margin-bottom:16px}.logo-box{width:90px;height:90px;border:1px solid #dbe3ea;border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden}.logo-box img{max-width:78px;max-height:78px}.head-center{text-align:center;flex:1}.head-center h2{margin:0;color:#082f57;font-size:22px;font-weight:900}.head-center p{margin:5px 0;font-size:13px;font-weight:bold}.meta{font-size:12px;line-height:1.8;color:#374151;text-align:left}table{width:100%;border-collapse:collapse;font-size:12px;margin-top:14px}th,td{border:1px solid #111827;padding:7px;text-align:center;vertical-align:middle}th{background:#eef5f8;color:#082f57;font-weight:bold}.footer{display:flex;justify-content:space-between;margin-top:32px;font-size:13px;font-weight:bold}.signature{width:220px;text-align:center;border-top:1px solid #111827;padding-top:8px}.badge{font-weight:bold;color:#15803d}@media print{.print-btn{display:none}body{margin:12mm}.header{break-inside:avoid}table{page-break-inside:auto}tr{page-break-inside:avoid;page-break-after:auto}}
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨️ طباعة القائمة</button>

<div class="header">
    <div class="logo-box">
        <img src="{{ asset('images/djs-logo.png') }}" alt="Logo">
    </div>

    <div class="head-center">
        <p>الجمهورية الجزائرية الديمقراطية الشعبية</p>
        <p>وزارة الرياضة</p>
        <h2>ديوان المركب المتعدد الرياضات لولاية ميلة</h2>
        <p>قائمة الأشخاص المؤمنين</p>
        <p>الموقع: www.opowmila.dz</p>
    </div>

    <div class="meta">
        <div>تاريخ الطباعة: {{ now()->format('Y/m/d H:i') }}</div>
        <div>عدد الأشخاص: {{ $persons->count() }}</div>
        <div>الحالة: {{ $status ?? 'pending' }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>الرقم</th>
            <th>الاسم واللقب</th>
            <th>تاريخ الميلاد</th>
            <th>الهاتف</th>
            <th>بداية التأمين</th>
            <th>نهاية التأمين</th>
            <th>الحالة</th>
            <th>أمّن من طرف</th>
        </tr>
    </thead>
    <tbody>
        @forelse($persons as $person)
            @php
                $fullName = $person->name ?? trim(($person->first_name ?? '') . ' ' . ($person->last_name ?? ''));
                $st = $person->assurance_status ?? 'not_assured';
                $label = match($st) {
                    'pending' => 'في انتظار الطباعة',
                    'assured' => 'مؤمن',
                    'expired' => 'منتهي',
                    default => 'غير مؤمن',
                };
            @endphp
            <tr>
                <td>{{ $person->id }}</td>
                <td>{{ $fullName ?: '---' }}</td>
                <td>{{ $person->birth_date ? \Carbon\Carbon::parse($person->birth_date)->format('Y/m/d') : '---' }}</td>
                <td>{{ $person->phone ?? '---' }}</td>
                <td>{{ $person->assurance_start_date ? \Carbon\Carbon::parse($person->assurance_start_date)->format('Y/m/d') : '---' }}</td>
                <td>{{ $person->assurance_end_date ? \Carbon\Carbon::parse($person->assurance_end_date)->format('Y/m/d') : '---' }}</td>
                <td><span class="badge">{{ $label }}</span></td>
                <td>{{ optional($person->assuredBy)->name ?? '---' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">لا توجد بيانات للطباعة.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <div>حرر بميلة في: {{ now()->format('Y/m/d') }}</div>
    <div class="signature">الإمضاء والختم</div>
</div>

<script>
    window.addEventListener('load', function(){
        setTimeout(function(){ window.print(); }, 500);
    });
</script>

</body>
</html>
