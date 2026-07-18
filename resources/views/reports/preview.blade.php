<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report['title'] }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/smart-siami-icon.png') }}">
    @php
        $printSettings ??= reportPrintSettings();
        $letterheadSettings = reportLetterheadSettings();
        $letterheadDisplay = \App\Support\ReportLetterhead::settings();
        $addressLines = \App\Support\ReportLetterhead::addressLines($letterheadDisplay);
        $logoPath = resource_path('assets/logo JDS tanpa company.png');
        $logoDataUri = is_file($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
        $paperSize = $printSettings['paper_size'] ?? 'A4';
        $orientation = $printSettings['orientation'] ?? 'portrait';
        $paperCss = match ($paperSize) {
            'F4' => $orientation === 'landscape' ? '330mm 210mm' : '210mm 330mm',
            'Letter' => 'Letter '.$orientation,
            'Legal' => 'Legal '.$orientation,
            default => 'A4 '.$orientation,
        };
        $tablePadding = match ($printSettings['table_density'] ?? 'normal') {
            'compact' => '5px 6px',
            'loose' => '10px 11px',
            default => '8px',
        };
    @endphp
    <style>
        @page {
            size: {{ $paperCss }};
            margin: {{ $printSettings['margin_top_cm'] ?? 1.8 }}cm {{ $printSettings['margin_right_cm'] ?? 1.6 }}cm {{ $printSettings['margin_bottom_cm'] ?? 1.8 }}cm {{ $printSettings['margin_left_cm'] ?? 1.6 }}cm;
        }
        body {
            color: #172033;
            font-family: "{{ $printSettings['font_family'] ?? 'Arial' }}", Arial, Helvetica, sans-serif;
            font-size: {{ (int) ($printSettings['font_size'] ?? 12) }}px;
            margin: {{ $printSettings['margin_top_cm'] ?? 1.8 }}cm {{ $printSettings['margin_right_cm'] ?? 1.6 }}cm {{ $printSettings['margin_bottom_cm'] ?? 1.8 }}cm {{ $printSettings['margin_left_cm'] ?? 1.6 }}cm;
            line-height: {{ $printSettings['line_height'] ?? 1.45 }};
        }
        .kop { border-bottom: 3px double #172033; margin-bottom: 18px; padding-bottom: 12px; }
        .kop-grid { align-items: center; display: grid; gap: 18px; grid-template-columns: {{ max(70, (int) ($letterheadDisplay['logo_width'] ?? 88) + 18) }}px 1fr; }
        .kop-logo-wrap { align-items: center; display: flex; justify-content: center; min-height: {{ max(70, (int) ($letterheadDisplay['logo_width'] ?? 88)) }}px; }
        .kop-logo { max-height: {{ (int) ($letterheadDisplay['logo_width'] ?? 88) }}px; max-width: {{ (int) ($letterheadDisplay['logo_width'] ?? 88) }}px; object-fit: contain; }
        .kop-text { color: #111827; text-align: center; }
        .kop-title { font-size: {{ (int) ($letterheadDisplay['institution_font_size'] ?? 16) }}px; font-weight: {{ ($letterheadDisplay['institution_bold'] ?? true) ? 800 : 500 }}; letter-spacing: .02em; line-height: 1.15; text-transform: uppercase; }
        .kop-unit { font-size: {{ (int) ($letterheadDisplay['unit_font_size'] ?? 14) }}px; font-weight: {{ ($letterheadDisplay['unit_bold'] ?? true) ? 800 : 500 }}; line-height: 1.18; margin-top: 1px; text-transform: uppercase; }
        .kop-meta { font-size: {{ (int) ($letterheadDisplay['address_font_size'] ?? 11) }}px; font-weight: {{ ($letterheadDisplay['address_bold'] ?? false) ? 700 : 400 }}; line-height: 1.25; margin-top: 2px; }
        .kop-source { background: #f4fbf8; border: 1px dashed #9acdbf; border-radius: 8px; color: #0E6656; grid-column: 1 / -1; margin-top: 8px; padding: 8px 10px; text-align: left; }
        .kop-source a { color: #0E6656; font-weight: 700; }
        .report-heading { margin-bottom: 18px; text-align: center; }
        .report-heading p { margin-top: 4px; }
        h1 { font-size: 22px; margin: 0; }
        h2 { font-size: 18px; margin: 24px 0 10px; }
        p { margin: 4px 0; }
        table { border-collapse: collapse; margin-bottom: 18px; width: 100%; }
        th, td { border: 1px solid #d9dee8; font-size: {{ max(9, (int) ($printSettings['font_size'] ?? 12) - 1) }}px; padding: {{ $tablePadding }}; text-align: left; vertical-align: top; }
        th { background: #0E6656; color: #ffffff; }
        .meta { display: grid; grid-template-columns: 180px 1fr; gap: 6px 12px; margin-bottom: 18px; }
        .print-actions { margin-bottom: 18px; }
        .button { border: 1px solid #d9dee8; border-radius: 8px; color: #0f4d5f; display: inline-block; font-weight: 700; padding: 10px 14px; text-decoration: none; }
        .visuals { display: grid; grid-template-columns: 190px 1fr; gap: 18px; margin: 18px 0 24px; align-items: center; }
        .gauge { width: 150px; height: 150px; border-radius: 50%; display: grid; place-items: center; background: conic-gradient(#0E6656 calc(var(--value) * 1%), #E4F2EE 0); }
        .gauge div { width: 96px; height: 96px; border-radius: 50%; background: #fff; display: grid; place-items: center; font-size: 24px; font-weight: 800; }
        .mini-bars { display: grid; gap: 8px; }
        .mini-bar { display: grid; grid-template-columns: 90px 1fr 46px; gap: 8px; align-items: center; font-size: 12px; }
        .mini-bar span:nth-child(2) { height: 10px; border-radius: 999px; background: #E4F2EE; overflow: hidden; }
        .mini-bar i { display: block; height: 100%; background: #0E6656; }
        @media print { .print-actions { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="print-actions">
        <a class="button" href="#" onclick="window.print(); return false;">Print</a>
        <a class="button" href="{{ url()->previous() }}">Kembali</a>
    </div>

    <header class="kop">
        <div class="kop-grid">
            <div class="kop-logo-wrap">
                @if ($logoDataUri)
                    <img class="kop-logo" src="{{ $logoDataUri }}" alt="Logo JDS">
                @else
                    <strong>JDS</strong>
                @endif
            </div>
            <div class="kop-text">
                <div class="kop-title">{{ $letterheadDisplay['institution'] ?: $report['institution'] }}</div>
                @if (! empty($letterheadDisplay['unit']))
                    <div class="kop-unit">{{ $letterheadDisplay['unit'] }}</div>
                @endif
                @foreach ($addressLines as $line)
                    <div class="kop-meta">{{ $line }}</div>
                @endforeach
            </div>
            @if (($letterheadSettings['mode'] ?? 'default') === 'custom' && ! empty($letterheadSettings['file_path']))
                <div class="kop-source">
                    Kop custom terpasang:
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($letterheadSettings['file_path']) }}" target="_blank">{{ $letterheadSettings['file_name'] ?? 'Lihat file kop' }}</a>
                </div>
            @endif
        </div>
    </header>

    <section class="report-heading">
        <h1>{{ $report['title'] }}</h1>
        <p>{{ $report['subtitle'] }}</p>
    </section>

    <section class="meta">
        @foreach ($report['meta'] as $key => $value)
            <strong>{{ $key }}</strong>
            <span>{{ $value }}</span>
        @endforeach
    </section>

    @if (($printSettings['show_visual_summary'] ?? true) && ! empty($report['visuals']))
        <section class="visuals" aria-label="Visual ringkasan laporan">
            <div class="gauge" style="--value: {{ $report['visuals']['readiness'] ?? 0 }}"><div>{{ $report['visuals']['readiness'] ?? 0 }}%</div></div>
            <div class="mini-bars">
                @foreach (($report['visuals']['radar'] ?? []) as $item)
                    <div class="mini-bar">
                        <strong>{{ $item['label'] }}</strong>
                        <span><i style="width: {{ $item['value'] }}%"></i></span>
                        <em>{{ $item['value'] }}%</em>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @foreach ($report['tables'] as $table)
        <h2>{{ $table['title'] }}</h2>
        <table>
            <thead>
                <tr>
                    @foreach ($table['headers'] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($table['rows'] as $row)
                    <tr>
                        @foreach ($row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($table['headers']) }}">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>
