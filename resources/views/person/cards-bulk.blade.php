<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بطاقات لاعبي {{ $entityName }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    @include('person.partials.card-css')

    <style>
        body { background: #eef2f0; }

        .entity-title {
            margin-top: 10px;
            font-size: 15px;
            font-weight: 800;
            color: #1b5e20;
            background: #fff;
            border: 1px solid #cfe7d2;
            border-radius: 12px;
            padding: 10px 18px;
            text-align: center;
            max-width: 520px;
            width: 100%;
        }

        .summary {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #4b6b4d;
            text-align: center;
        }

        /* ===== Écran : aperçu des feuilles A4 ===== */
        .bulk-page {
            display: grid;
            grid-template-columns: repeat(2, 95mm);
            grid-template-rows: repeat(4, 55mm);
            gap: 2mm;
            justify-content: center;
            align-content: center;
            background: #fff;
            border-radius: 12px;
            padding: 8mm 6mm;
            box-shadow: 0 8px 22px rgba(0,0,0,.10);
            margin: 14px auto;
            width: max-content;
            max-width: 100%;
        }

        /* ===== Impression A4 : 2 colonnes × 4 lignes = 8 cartes par page ===== */
        @media print {
            @page { size: A4 portrait; margin: 3mm; }
            body { background: #fff !important; padding: 0 !important; }
            .actions, .note-box, .entity-title, .summary { display: none !important; }

            .bulk-page {
                display: grid;
                grid-template-columns: repeat(2, 95mm);
                grid-template-rows: repeat(4, 55mm);
                gap: 2mm;
                justify-content: center;
                align-content: center;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                margin: 0 auto;
                width: fit-content;
                page-break-after: always;
                break-after: page;
            }

            .bulk-page:last-child { page-break-after: auto; break-after: auto; }

            .bulk-page .card-piece { box-shadow: none; border-radius: 2.5mm; }

            .badge, .badge-top, .badge-body, .badge-foot {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="actions">
        <a href="{{ $backUrl }}">🔙 رجوع</a>
        <button class="btn-outline" onclick="window.print()">🖨️ طباعة كل البطاقات</button>
        <button onclick="savePdf()">⬇ تحميل PDF</button>
    </div>

    <div class="entity-title">🏷️ بطاقات {{ $entityName }} — الموسم {{ $seasonLabel }}</div>
    <p class="summary">عدد البطاقات: {{ $cards->count() }} — كل صفحة A4 تحتوي 8 بطاقات (عمودان × 4 أسطر)</p>

    @if($cards->isEmpty())
        <div class="note-box">⚠️ لا توجد بطاقات قابلة للعرض حالياً.</div>
    @else
        @foreach($cards->chunk(8) as $pageCards)
            <div class="bulk-page">
                @foreach($pageCards as $card)
                    @include('person.partials.card-piece', ['card' => $card])
                @endforeach
            </div>
        @endforeach
    @endif

    <script>
        function savePdf() {
            window.print();
        }
    </script>

</body>
</html>