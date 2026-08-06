<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل المنشآت</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Cairo", "DejaVu Sans", sans-serif;
            margin: 30px;
            direction: rtl;
            text-align: right;
            color: #000;
            font-size: 13px;
        }

        .header-box {
            text-align: center;
            margin-bottom: 25px;
        }

        .header-box h2,
        .header-box h3 {
            margin: 4px 0;
        }

        .logo {
            width: 80px;
            margin-bottom: 8px;
        }

        .info-box {
            margin-top: 10px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12.5px;
        }

        th, td {
            border: 1px solid #444;
            padding: 7px 6px;
            text-align: center;
        }

        th {
            background: #efefef;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background: #f7f7f7;
        }

        tfoot td {
            background: #e8e8e8;
            font-weight: bold;
        }

        .footer {
            margin-top: 35px;
            font-size: 13px;
            text-align: center;
            color: #555;
        }

        @media print {
            body {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

@php
    $logo = asset('images/djs-logo.png');
@endphp

<!-- 🔰 Header -->
<div class="header-box">
    <img src="{{ $logo }}" class="logo">

    <h2>ديوان المركب المتعدد الرياضات لولاية ميلة</h2>
    <h3>تفاصيل المنشآت – إحصائيات عامة</h3>

    <div class="info-box">
        <strong>تاريخ الطباعة:</strong>
        {{ ($printedAt ?? now())->format('Y-m-d H:i') }}
    </div>
</div>

<!-- 📋 Table -->
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>المنشأة</th>
            <th>المنخرطون</th>
            <th>ذكور</th>
            <th>إناث</th>
            <th>غير محدد</th>
            <th>الحجوزات</th>
            <th>ملفات مقبولة</th>
            <th>المبالغ المدفوعة (دج)</th>
        </tr>
    </thead>

    <tbody>
        @forelse($complexStats as $index => $cs)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $cs->nom }}</td>
            <td>{{ number_format($cs->subscribers) }}</td>
            <td>{{ number_format($cs->gender['ذكر'] ?? 0) }}</td>
            <td>{{ number_format($cs->gender['أنثى'] ?? 0) }}</td>
            <td>{{ number_format($cs->gender['غير محدد'] ?? 0) }}</td>
            <td>{{ number_format($cs->reservations) }}</td>
            <td>{{ number_format($cs->approved) }}</td>
            <td>{{ number_format($cs->paidAmount, 0, ',', ' ') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9">لا توجد منشآت</td>
        </tr>
        @endforelse
    </tbody>

    <tfoot>
        <tr>
            <th colspan="2">الإجمالي</th>
            <th>{{ number_format($complexStats->sum('subscribers')) }}</th>
            <th>{{ number_format($genderGlobal['ذكر'] ?? 0) }}</th>
            <th>{{ number_format($genderGlobal['أنثى'] ?? 0) }}</th>
            <th>{{ number_format($genderGlobal['غير محدد'] ?? 0) }}</th>
            <th>{{ number_format($complexStats->sum('reservations')) }}</th>
            <th>{{ number_format($complexStats->sum('approved')) }}</th>
            <th>{{ number_format($complexStats->sum('paidAmount'), 0, ',', ' ') }}</th>
        </tr>
    </tfoot>
</table>

<!-- 🖨 Footer -->
<div class="footer">
    تم إنشاء هذه الوثيقة إلكترونيًا من منصة <strong>OPOW Mila</strong><br>
    {{ ($printedAt ?? now())->format('Y-m-d H:i') }}
</div>

<script>
    window.print();
</script>

</body>
</html>
