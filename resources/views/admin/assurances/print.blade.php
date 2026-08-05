<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>قائمة المؤمنين</title>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;direction:rtl;color:#111827;margin:24px}.print-btn{background:#082f57;color:#fff;border:0;border-radius:8px;padding:9px 14px;margin-bottom:15px;cursor:pointer}.head{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #082f57;padding-bottom:12px;margin-bottom:18px}.logo{width:72px;height:72px;object-fit:contain}.title{text-align:center;flex:1}.title h2{margin:0;color:#082f57;font-size:22px}.title p{margin:5px 0 0;font-size:13px;color:#374151}.meta{font-size:12px;line-height:1.8}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #111827;padding:8px;text-align:center}th{background:#eef5f8;color:#082f57;font-weight:bold}.foot{display:flex;justify-content:space-between;margin-top:28px;font-size:13px}.sign{min-width:180px;text-align:center}@media print{.print-btn{display:none}body{margin:12mm}}
</style>
</head>
<body>
<button onclick="window.print()" class="print-btn">طباعة</button>

<div class="head">
    <div><img src="{{ asset('images/djs-logo.png') }}" class="logo" alt="logo"></div>
    <div class="title">
        <h2>قائمة المؤمنين</h2>
        <p>ديوان المركب المتعدد الرياضات لولاية ميلة</p>
        <p>www.opowmila.dz</p>
    </div>
    <div class="meta">
        <div>تاريخ الطباعة: {{ now()->format('Y/m/d H:i') }}</div>
        <div>عدد السجلات: {{ $assurances->count() }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>اللقب</th>
            <th>تاريخ الميلاد</th>
            <th>المنشأة</th>
            <th>بداية التأمين</th>
            <th>نهاية التأمين</th>
        </tr>
    </thead>
    <tbody>
        @foreach($assurances as $assurance)
            @php
                $person = $assurance->person;
                $complexName = '---';
                if($person && $person->user_id){
                    $complexName = \Illuminate\Support\Facades\DB::table('persons')
                        ->leftJoin('users','users.id','=','persons.user_id')
                        ->leftJoin('complexes','complexes.id','=','users.complex_id')
                        ->where('persons.id',$person->id)
                        ->value('complexes.nom') ?? '---';
                }
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $person->firstname ?? '---' }}</td>
                <td>{{ $person->lastname ?? '---' }}</td>
                <td>{{ $person && $person->birth_date ? \Carbon\Carbon::parse($person->birth_date)->format('Y/m/d') : '---' }}</td>
                <td>{{ $complexName }}</td>
                <td>{{ $assurance->start_date ? \Carbon\Carbon::parse($assurance->start_date)->format('Y/m/d') : '---' }}</td>
                <td>{{ $assurance->end_date ? \Carbon\Carbon::parse($assurance->end_date)->format('Y/m/d') : '---' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="foot">
    <div>حرر بميلة في: {{ now()->format('Y/m/d') }}</div>
    <div class="sign">الإمضاء والختم</div>
</div>
</body>
</html>
