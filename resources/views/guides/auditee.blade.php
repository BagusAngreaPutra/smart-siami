@extends('layouts.app')

@section('title', $eyebrow.' - SMART SIAMI')
@section('page_title', $eyebrow)

@section('content')
    @php
        $mainSteps = [
            [
                'tone' => 'blue',
                'title' => 'Lengkapi data unit',
                'description' => 'Isi evaluasi diri dan unggah bukti yang sesuai.',
                'url' => route('auditee.self-evaluations'),
                'action' => 'Mulai evaluasi',
                'icon' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            ],
            [
                'tone' => 'orange',
                'title' => 'Tanggapi auditor',
                'description' => 'Jawab klarifikasi dan periksa jadwal visitasi.',
                'url' => route('auditee.clarifications'),
                'action' => 'Lihat klarifikasi',
                'icon' => '<path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4zM8 9h8M8 13h5"/>',
            ],
            [
                'tone' => 'teal',
                'title' => 'Selesaikan hasil audit',
                'description' => 'Kerjakan tindak lanjut lalu simpan laporan unit.',
                'url' => route('auditee.findings-followups'),
                'action' => 'Lihat tindak lanjut',
                'icon' => '<path d="M21 12a9 9 0 0 1-15.5 6.2L3 16M3 21v-5h5M3 12A9 9 0 0 1 18.5 5.8L21 8M21 3v5h-5"/>',
            ],
        ];

        $featureMeta = [
            'Profil Unit' => ['tone' => 'violet', 'icon' => '<path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h1M14 7h1M9 11h1M14 11h1"/>'],
            'Evaluasi Diri' => ['tone' => 'blue', 'icon' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>'],
            'Bukti Dokumen' => ['tone' => 'teal', 'icon' => '<path d="m21.4 11.6-8.5 8.5a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"/>'],
            'Klarifikasi Auditor' => ['tone' => 'orange', 'icon' => '<path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4zM8 9h8M8 13h5"/>'],
            'Jadwal Visitasi' => ['tone' => 'violet', 'icon' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>'],
            'Tindak Lanjut Temuan' => ['tone' => 'red', 'icon' => '<path d="M21 12a9 9 0 0 1-15.5 6.2L3 16M3 21v-5h5M3 12A9 9 0 0 1 18.5 5.8L21 8M21 3v5h-5"/>'],
            'Laporan Unit' => ['tone' => 'teal', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h6"/>'],
        ];
    @endphp

    <section class="auditee-guide-hero auditee-guide-hero-simple" aria-labelledby="auditee-guide-hero-title">
        <div class="auditee-guide-hero-copy">
            <span class="auditee-guide-kicker">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12l2 2 4-5"></path><circle cx="12" cy="12" r="9"></circle></svg>
                Panduan singkat
            </span>
            <h2 id="auditee-guide-hero-title">Selesaikan audit unit dalam 3 langkah.</h2>
            <p>Lengkapi data, tanggapi auditor, lalu selesaikan tindak lanjut. Mulai dari pekerjaan yang paling penting.</p>
            <div class="auditee-guide-actions">
                <a class="auditee-guide-primary" href="{{ route('auditee.self-evaluations') }}">
                    Mulai Evaluasi Diri
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                </a>
                <a class="auditee-guide-secondary" href="#bantuan-menu">Cari Bantuan Menu</a>
            </div>
        </div>

        <div class="auditee-guide-visual">
            <picture>
                <source srcset="{{ asset($illustrationWebp) }}" type="image/webp">
                <img src="{{ asset($illustration) }}" alt="{{ $illustrationAlt }}" width="1536" height="1024" decoding="async" fetchpriority="high">
            </picture>
        </div>
    </section>

    <section class="auditee-guide-main-steps" id="alur-audit" aria-labelledby="auditee-guide-main-title">
        <header>
            <span>Mulai cepat</span>
            <h2 id="auditee-guide-main-title">Tiga langkah utama</h2>
            <p>Kerjakan dari kiri ke kanan.</p>
        </header>

        <ol>
            @foreach ($mainSteps as $step)
                <li class="tone-{{ $step['tone'] }}">
                    <div class="auditee-guide-main-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">{!! $step['icon'] !!}</svg>
                    </div>
                    <span>Langkah {{ $loop->iteration }}</span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                    <a href="{{ $step['url'] }}">{{ $step['action'] }} <span aria-hidden="true">&rarr;</span></a>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="auditee-guide-help" id="bantuan-menu" aria-labelledby="auditee-guide-help-title">
        <header>
            <div>
                <span>Bantuan per menu</span>
                <h2 id="auditee-guide-help-title">Pilih menu yang ingin dipahami</h2>
            </div>
            <p>Buka satu panduan sesuai kebutuhan Anda.</p>
        </header>

        <div class="auditee-guide-topics" aria-label="Penjelasan fitur auditee">
            @foreach ($sections as $section)
                @php
                    $meta = $featureMeta[$section['title']];
                @endphp
                <details class="auditee-guide-topic" id="panduan-{{ $loop->iteration }}">
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

    <section class="auditee-guide-reminder" aria-labelledby="auditee-guide-reminder-title">
        <div>
            <span aria-hidden="true">!</span>
            <h2 id="auditee-guide-reminder-title">Ingat</h2>
        </div>
        <ul>
            @foreach ($notes as $note)
                <li>{{ $note }}</li>
            @endforeach
        </ul>
    </section>
@endsection
