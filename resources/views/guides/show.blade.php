@extends('layouts.app')

@section('title', $eyebrow.' - SMART SIAMI')
@section('page_title', $eyebrow)

@section('content')
    @php
        $accentLabels = $roleLabel === 'Auditor'
            ? ['Fokus utama' => 'Pemeriksaan bukti', 'Output akhir' => 'Temuan & verifikasi', 'Akses data' => 'Sesuai penugasan']
            : ['Fokus utama' => 'Evaluasi unit', 'Output akhir' => 'Tindak lanjut selesai', 'Akses data' => 'Unit sendiri'];
    @endphp

    <section class="guide-hero smart-guide-hero">
        <div class="guide-hero-copy">
            <span class="quick-guide-eyebrow">{{ $eyebrow }}</span>
            <h3>{{ $title }}</h3>
            <p>{{ $description }}</p>
            <div class="hero-actions">
                <a class="hero-action" href="{{ $dashboardRoute }}">Kembali ke Dashboard</a>
            </div>
        </div>
        <div class="guide-illustration">
            <picture class="guide-illustration-frame">
                <source srcset="{{ asset($illustrationWebp) }}" type="image/webp">
                <img
                    src="{{ asset($illustration) }}"
                    alt="{{ $illustrationAlt }}"
                    width="1536"
                    height="1024"
                    decoding="async"
                    fetchpriority="high"
                >
            </picture>
            <div class="guide-hero-card">
                <strong>Prinsip cepat</strong>
                <span>Mulai dari status merah atau kuning, selesaikan langkahnya, lalu cek laporan sebagai arsip.</span>
            </div>
        </div>
    </section>

    <section class="guide-smart-row" aria-label="Ringkasan panduan">
        @foreach ($accentLabels as $label => $value)
            <div class="guide-smart-card">
                <span>{{ $label }}</span>
                <strong>{{ $value }}</strong>
            </div>
        @endforeach
        <div class="guide-smart-card">
            <span>Total fitur dijelaskan</span>
            <strong>{{ count($sections) }} fitur</strong>
        </div>
    </section>

    <section class="guide-flow panel smart-flow-panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Alur Besar Audit</h3>
                <p class="muted">Urutan kerja sederhana agar proses audit tidak terasa membingungkan.</p>
            </div>
        </div>
        <div class="guide-flow-track">
            @foreach ($workflow as $item)
                <div class="guide-flow-step">
                    <span>{{ $loop->iteration }}</span>
                    <strong>{{ $item }}</strong>
                </div>
            @endforeach
        </div>
    </section>

    <div class="guide-layout">
        <aside class="guide-index panel">
            <h3 class="panel-title">Daftar Fitur</h3>
            <p class="muted">Klik salah satu fitur untuk langsung membaca panduannya.</p>
            <div class="guide-mini-tip">
                <strong>Tips baca cepat</strong>
                <span>Baca bagian “Kapan dipakai” dulu. Kalau cocok dengan masalah Anda, lanjutkan ke langkah teknis.</span>
            </div>
            <nav>
                @foreach ($sections as $section)
                    <a href="#guide-{{ $loop->iteration }}">{{ $section['title'] }}</a>
                @endforeach
            </nav>
        </aside>

        <div class="guide-sections">
            @foreach ($sections as $section)
                <article class="guide-card panel" id="guide-{{ $loop->iteration }}">
                    <div class="guide-card-header">
                        <span class="guide-card-number" aria-hidden="true">
                            @switch(($loop->iteration - 1) % 5)
                                @case(0)
                                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h10M4 18h7"></path></svg>
                                    @break
                                @case(1)
                                    <svg viewBox="0 0 24 24"><path d="M12 3v18M5 8h14M7 16h10"></path></svg>
                                    @break
                                @case(2)
                                    <svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path></svg>
                                    @break
                                @case(3)
                                    <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-5"></path><circle cx="12" cy="12" r="9"></circle></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
                            @endswitch
                        </span>
                        <div>
                            <span class="guide-feature-kicker">Fitur {{ $loop->iteration }}</span>
                            <h3 class="panel-title">{{ $section['title'] }}</h3>
                            <p class="muted">{{ $section['purpose'] }}</p>
                        </div>
                        <a class="button secondary" href="{{ $section['url'] }}">Buka Fitur</a>
                    </div>

                    <div class="guide-info-grid">
                        <div>
                            <span class="guide-label">Kapan dipakai</span>
                            <p>{{ $section['when'] }}</p>
                        </div>
                        <div>
                            <span class="guide-label">Hasil akhir</span>
                            <p>{{ $section['result'] }}</p>
                        </div>
                    </div>

                    <div class="guide-steps">
                        <span class="guide-label">Langkah teknis</span>
                        <ol>
                            @foreach ($section['steps'] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="guide-result-ribbon">
                        <span>Yang perlu diingat</span>
                        <strong>{{ $section['result'] }}</strong>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    <section class="guide-notes panel dashboard-panel-accent warning">
        <h3 class="panel-title">Catatan Penting untuk {{ $roleLabel }}</h3>
        <div class="guide-note-grid">
            @foreach ($notes as $note)
                <div class="guide-note">
                    <span aria-hidden="true">!</span>
                    <p>{{ $note }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
