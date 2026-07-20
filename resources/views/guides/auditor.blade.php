@extends('layouts.app')

@section('title', $eyebrow.' - SMART SIAMI')
@section('page_title', $eyebrow)
@section('body_class', 'crm-route-auditee-guide')

@section('content')
    @php
        $mainSteps = [
            [
                'tone' => 'blue',
                'title' => 'Kenali penugasan',
                'description' => 'Periksa unit, periode, tim, dan jadwal audit yang menjadi tanggung jawab Anda.',
                'url' => route('auditor.tasks'),
                'action' => 'Buka tugas audit',
                'icon' => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14H4V6a2 2 0 0 1 2-2h2M8 12h8M8 16h6"/>',
            ],
            [
                'tone' => 'violet',
                'title' => 'Periksa dan verifikasi',
                'description' => 'Nilai evaluasi diri, validasi bukti, lakukan klarifikasi, dan siapkan visitasi.',
                'url' => route('auditor.desk-evaluation'),
                'action' => 'Mulai pemeriksaan',
                'icon' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3M8 11l2 2 4-4"/>',
            ],
            [
                'tone' => 'teal',
                'title' => 'Tuntaskan hasil audit',
                'description' => 'Finalisasi temuan, verifikasi perbaikan, lalu simpan laporan hasil audit.',
                'url' => route('auditor.follow-up-verifications'),
                'action' => 'Verifikasi perbaikan',
                'icon' => '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="m8 12 2.5 2.5L16 9"/>',
            ],
        ];

        $featureMeta = [
            'Tugas Audit' => ['tone' => 'blue', 'icon' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>'],
            'Desk Evaluation' => ['tone' => 'violet', 'icon' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3M8 11l2 2 4-4"/>'],
            'Klarifikasi' => ['tone' => 'orange', 'icon' => '<path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4zM8 9h8M8 13h5"/>'],
            'Visitasi' => ['tone' => 'violet', 'icon' => '<path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>'],
            'Temuan' => ['tone' => 'red', 'icon' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>'],
            'Verifikasi Perbaikan' => ['tone' => 'teal', 'icon' => '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="m8 12 2.5 2.5L16 9"/>'],
            'Laporan Saya' => ['tone' => 'blue', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h6"/>'],
        ];
    @endphp

    <section class="auditee-guide-hero auditee-guide-hero-simple auditor-guide-hero-latest" aria-labelledby="auditor-guide-hero-title">
        <div class="auditee-guide-hero-copy">
            <span class="auditee-guide-kicker">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12l2 2 4-5"></path><circle cx="12" cy="12" r="9"></circle></svg>
                Panduan singkat auditor
            </span>
            <h2 id="auditor-guide-hero-title">Selesaikan audit dalam 3 tahap yang jelas.</h2>
            <p>Kenali penugasan, periksa bukti secara objektif, lalu tuntaskan temuan dan verifikasi perbaikan.</p>
            <div class="auditee-guide-actions">
                <a class="auditee-guide-primary" href="{{ route('auditor.tasks') }}">
                    Buka Tugas Audit
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                </a>
                <a class="auditee-guide-secondary" href="#bantuan-menu-auditor">Cari Bantuan Menu</a>
            </div>
        </div>

        <div class="auditee-guide-visual">
            <picture>
                <source srcset="{{ asset($illustrationWebp) }}" type="image/webp">
                <img src="{{ asset($illustration) }}" alt="{{ $illustrationAlt }}" width="1536" height="1024" decoding="async" fetchpriority="high">
            </picture>
        </div>
    </section>

    <section class="auditee-guide-main-steps" id="alur-audit-auditor" aria-labelledby="auditor-guide-main-title">
        <header>
            <span>Mulai cepat</span>
            <h2 id="auditor-guide-main-title">Tiga tahap utama Auditor</h2>
            <p>Ikuti urutan ini agar pemeriksaan tetap terarah.</p>
        </header>

        <ol>
            @foreach ($mainSteps as $step)
                <li class="tone-{{ $step['tone'] }}">
                    <div class="auditee-guide-main-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">{!! $step['icon'] !!}</svg>
                    </div>
                    <span>Tahap {{ $loop->iteration }}</span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                    <a href="{{ $step['url'] }}">{{ $step['action'] }} <span aria-hidden="true">&rarr;</span></a>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="auditee-guide-help" id="bantuan-menu-auditor" aria-labelledby="auditor-guide-help-title">
        <header>
            <div>
                <span>Bantuan per menu</span>
                <h2 id="auditor-guide-help-title">Pilih proses yang ingin dipahami</h2>
            </div>
            <p>Buka panduan sesuai tahap audit yang sedang Anda kerjakan.</p>
        </header>

        <div class="auditee-guide-topics" aria-label="Penjelasan fitur auditor">
            @foreach ($sections as $section)
                @php
                    $meta = $featureMeta[$section['title']] ?? ['tone' => 'blue', 'icon' => '<circle cx="12" cy="12" r="8"/>'];
                @endphp
                <details class="auditee-guide-topic" id="panduan-auditor-{{ $loop->iteration }}">
                    <summary>
                        <span class="auditee-guide-topic-number tone-{{ $meta['tone'] }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true">{!! $meta['icon'] !!}</svg>
                        </span>
                        <span class="auditee-guide-topic-title">
                            <strong>{{ $section['title'] }}</strong>
                            <small>{{ $section['purpose'] }}</small>
                        </span>
                        <span class="auditee-guide-topic-toggle" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"></path></svg>
                        </span>
                    </summary>

                    <div class="auditee-guide-topic-body auditee-guide-topic-body-simple">
                        <div class="auditee-guide-when">
                            <span>Kapan dipakai</span>
                            <p>{{ $section['when'] }}</p>
                        </div>
                        <div class="auditee-guide-steps-simple">
                            <span>Yang perlu dilakukan</span>
                            <ol>
                                @foreach ($section['steps'] as $step)
                                    <li><b>{{ $loop->iteration }}</b><p>{{ $step }}</p></li>
                                @endforeach
                            </ol>
                        </div>
                        <footer>
                            <div>
                                <span>Setelah selesai</span>
                                <strong>{{ $section['result'] }}</strong>
                            </div>
                            <a href="{{ $section['url'] }}">Buka menu <span aria-hidden="true">&rarr;</span></a>
                        </footer>
                    </div>
                </details>
            @endforeach
        </div>
    </section>

    <section class="auditee-guide-reminder" aria-labelledby="auditor-guide-reminder-title">
        <div>
            <span aria-hidden="true">!</span>
            <h2 id="auditor-guide-reminder-title">Prinsip Auditor</h2>
        </div>
        <ul>
            @foreach ($notes as $note)
                <li>{{ $note }}</li>
            @endforeach
        </ul>
    </section>
@endsection
