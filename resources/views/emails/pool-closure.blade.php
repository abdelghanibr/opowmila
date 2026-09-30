<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إشعار تعطل المسبح</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f4f6f8;
    font-family: Tahoma, Arial, sans-serif;
    direction: rtl;
">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding:30px 15px">

            <!-- Card -->
            <table width="600" cellpadding="0" cellspacing="0" style="
                background:#ffffff;
                border-radius:10px;
                box-shadow:0 6px 18px rgba(0,0,0,0.08);
                overflow:hidden;
            ">

                <!-- Header -->
                <tr>
                    <td style="
                        background: linear-gradient(135deg, #0a4f88, #0a8a67);
                        color: #ffffff;
                        padding: 24px 20px;
                        text-align: center;
                        font-family: Arial, sans-serif;
                    ">
                        <div style="
                            font-size: 14px;
                            opacity: 0.9;
                            margin-bottom: 6px;
                        ">
                            ديوان المركب المتعدد الرياضات لولاية ميلة
                        </div>

                        <div style="
                            font-size: 22px;
                            font-weight: bold;
                            letter-spacing: 0.5px;
                        ">
                            ⚠️ إشعار تعطل المسبح
                        </div>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:25px; color:#333; font-size:15px; line-height:1.9">

                        <p>
                            مرحبًا
                            <strong>{{ $impactedItems[0]['reservation']->user->name ?? 'عميلنا الكريم' }}</strong>،
                        </p>

                        <p>
                            نود إعلامكم أن المسبح سيكون <strong>متوقفًا عن العمل</strong> خلال الفترة التالية:
                        </p>

                        <!-- Closure box -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="
                            background:#f1f8ff;
                            border-right:4px solid #0a6cff;
                            border-radius:8px;
                            overflow:hidden;
                            margin:20px 0;
                        ">
                            <tr>
                                <td style="padding:16px 18px">
                                    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:15px">
                                        <tr>
                                            <td style="padding:6px 0;color:#555;width:140px">المركب</td>
                                            <td style="padding:6px 0;font-weight:bold">
                                                {{ optional($poolClosure->complexActivity->complex)->nom ?? '—' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;color:#555;width:140px">النشاط</td>
                                            <td style="padding:6px 0;font-weight:bold">
                                                {{ optional($poolClosure->complexActivity->activity)->title ?? '—' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;color:#555;width:140px">تاريخ البداية</td>
                                            <td style="padding:6px 0;font-weight:bold">
                                                {{ \Carbon\Carbon::parse($poolClosure->start_date)->format('Y-m-d') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;color:#555;width:140px">وقت البداية</td>
                                            <td style="padding:6px 0;font-weight:bold">
                                                {{ \Carbon\Carbon::parse($poolClosure->start_date)->format('H:i') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;color:#555;width:140px">تاريخ النهاية</td>
                                            <td style="padding:6px 0;font-weight:bold">
                                                {{ \Carbon\Carbon::parse($poolClosure->end_date)->format('Y-m-d') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;color:#555;width:140px">وقت النهاية</td>
                                            <td style="padding:6px 0;font-weight:bold">
                                                {{ \Carbon\Carbon::parse($poolClosure->end_date)->format('H:i') }}
                                            </td>
                                        </tr>
                                        @if(!empty($poolClosure->reason))
                                        <tr>
                                            <td style="padding:6px 0;color:#555;width:140px">السبب</td>
                                            <td style="padding:6px 0;font-weight:bold;color:#b45309">{{ $poolClosure->reason }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        @if(!empty($impactedItems))
                        <p>الحجوزات المتأثرة لديكم (سيتم تعويضكم برصيد عن كل يوم متأثر):</p>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;margin:10px 0">
                            <tr style="background:#f0f0f0">
                                <th style="padding:8px;border:1px solid #ddd;text-align:right">التاريخ المتأثر</th>
                                <th style="padding:8px;border:1px solid #ddd;text-align:right">قيمة الجلسة (دج)</th>
                            </tr>
                            @foreach($impactedItems as $item)
                                @foreach($item['impacted_dates'] as $date)
                                <tr>
                                    <td style="padding:8px;border:1px solid #ddd">{{ $date }}</td>
                                    <td style="padding:8px;border:1px solid #ddd">{{ number_format($item['session_value'],2) }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </table>
                        @endif

                        <p>
                            سيتم إضافة <strong>رصيد تعويضي</strong> تلقائيًا إلى حسابكم، ويمكنكم استعماله عند عودة المسبح للخدمة.
                        </p>

                        <p>
                            في حال كانت لديكم أي استفسارات، لا تترددوا في التواصل معنا.
                        </p>

                        <p style="margin-top:30px">
                            مع فائق الاحترام 🌟<br>
                            <strong>{{ config('app.name') }}</strong>
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="
                        background:#f0f0f0;
                        padding:15px;
                        text-align:center;
                        font-size:12px;
                        color:#777;
                    ">
                        هذا البريد أُرسل تلقائيًا، يرجى عدم الرد عليه
                        <br>
                        © {{ date('Y') }} {{ config('app.name') }}
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
