@extends('layouts.app')

@section('title', 'Pengaturan - SMART SIAMI')
@section('page_title', 'Pengaturan')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="warning">{{ $errors->first() }}</div>
    @endif

    <div class="panel">
        <nav class="tabs" aria-label="Tab pengaturan">
            <a class="tab-link @if ($activeTab === 'identity') active @endif" href="{{ route('admin.settings', ['tab' => 'identity']) }}">Identitas Institusi</a>
            <a class="tab-link @if ($activeTab === 'report_format') active @endif" href="{{ route('admin.settings', ['tab' => 'report_format']) }}">Format Laporan</a>
            <a class="tab-link @if ($activeTab === 'categories') active @endif" href="{{ route('admin.settings', ['tab' => 'categories']) }}">Kategori Temuan</a>
            <a class="tab-link @if ($activeTab === 'uploads') active @endif" href="{{ route('admin.settings', ['tab' => 'uploads']) }}">Batas Unggah File</a>
            <a class="tab-link @if ($activeTab === 'templates') active @endif" href="{{ route('admin.settings', ['tab' => 'templates']) }}">Template Pesan Notifikasi</a>
            <a class="tab-link @if ($activeTab === 'advanced') active @endif" href="{{ route('admin.settings', ['tab' => 'advanced']) }}">Pengaturan Lanjutan</a>
        </nav>

        @if ($activeTab === 'identity')
            <form class="form-grid" method="post" action="{{ route('admin.settings.identity') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')
                <div class="form-field">
                    <label for="nama_institusi">Nama Institusi</label>
                    <input id="nama_institusi" name="nama_institusi" value="{{ old('nama_institusi', $settings['nama_institusi'] ?? '') }}" required>
                </div>
                <div class="form-field">
                    <label for="nama_lpm">Nama Unit Penjaminan Mutu</label>
                    <input id="nama_lpm" name="nama_lpm" value="{{ old('nama_lpm', $settings['nama_lpm'] ?? '') }}">
                </div>
                <div class="form-field">
                    <label for="email_lpm">Email Kontak LPM</label>
                    <input id="email_lpm" type="email" name="email_lpm" value="{{ old('email_lpm', $settings['email_lpm'] ?? '') }}">
                </div>
                <div class="form-field">
                    <label for="logo">Logo Institusi</label>
                    <input id="logo" type="file" name="logo" accept=".png,.jpg,.jpeg">
                    @if (! empty($settings['logo_path']))
                        <span class="muted">Logo tersimpan: {{ $settings['logo_path'] }}</span>
                    @endif
                </div>
                <div class="form-field full">
                    <button type="submit">Simpan Identitas</button>
                </div>
            </form>
        @elseif ($activeTab === 'report_format')
            @php
                $printSettings = reportPrintSettings();
                $letterheadSettings = reportLetterheadSettings();
            @endphp
            <form class="form-grid" method="post" action="{{ route('admin.settings.report-format') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')
                <div class="form-field">
                    <label for="report_paper_size">Ukuran Kertas</label>
                    <select id="report_paper_size" name="report_paper_size" required>
                        @foreach ($paperSizes as $paperSize)
                            <option value="{{ $paperSize }}" @selected(old('report_paper_size', $printSettings['paper_size']) === $paperSize)>{{ $paperSize }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="report_orientation">Orientasi</label>
                    <select id="report_orientation" name="report_orientation" required>
                        <option value="portrait" @selected(old('report_orientation', $printSettings['orientation']) === 'portrait')>Portrait</option>
                        <option value="landscape" @selected(old('report_orientation', $printSettings['orientation']) === 'landscape')>Landscape</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="report_font_family">Jenis Font</label>
                    <select id="report_font_family" name="report_font_family" required>
                        @foreach ($fontFamilies as $fontFamily)
                            <option value="{{ $fontFamily }}" @selected(old('report_font_family', $printSettings['font_family']) === $fontFamily)>{{ $fontFamily }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="report_font_size">Ukuran Font</label>
                    <input id="report_font_size" type="number" min="9" max="16" name="report_font_size" value="{{ old('report_font_size', $printSettings['font_size']) }}" required>
                </div>
                <div class="form-field">
                    <label for="report_line_height">Spasi Baris</label>
                    <input id="report_line_height" type="number" min="1.15" max="1.8" step="0.05" name="report_line_height" value="{{ old('report_line_height', $printSettings['line_height']) }}" required>
                </div>
                <div class="form-field">
                    <label for="report_table_density">Kepadatan Tabel</label>
                    <select id="report_table_density" name="report_table_density" required>
                        <option value="compact" @selected(old('report_table_density', $printSettings['table_density']) === 'compact')>Compact</option>
                        <option value="normal" @selected(old('report_table_density', $printSettings['table_density']) === 'normal')>Normal</option>
                        <option value="loose" @selected(old('report_table_density', $printSettings['table_density']) === 'loose')>Lega</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="report_margin_top_cm">Margin Atas (cm)</label>
                    <input id="report_margin_top_cm" type="number" min="0.5" max="5" step="0.1" name="report_margin_top_cm" value="{{ old('report_margin_top_cm', $printSettings['margin_top_cm']) }}" required>
                </div>
                <div class="form-field">
                    <label for="report_margin_right_cm">Margin Kanan (cm)</label>
                    <input id="report_margin_right_cm" type="number" min="0.5" max="5" step="0.1" name="report_margin_right_cm" value="{{ old('report_margin_right_cm', $printSettings['margin_right_cm']) }}" required>
                </div>
                <div class="form-field">
                    <label for="report_margin_bottom_cm">Margin Bawah (cm)</label>
                    <input id="report_margin_bottom_cm" type="number" min="0.5" max="5" step="0.1" name="report_margin_bottom_cm" value="{{ old('report_margin_bottom_cm', $printSettings['margin_bottom_cm']) }}" required>
                </div>
                <div class="form-field">
                    <label for="report_margin_left_cm">Margin Kiri (cm)</label>
                    <input id="report_margin_left_cm" type="number" min="0.5" max="5" step="0.1" name="report_margin_left_cm" value="{{ old('report_margin_left_cm', $printSettings['margin_left_cm']) }}" required>
                </div>
                <div class="form-field full">
                    <label class="remember">
                        <input type="checkbox" name="report_show_visual_summary" value="1" @checked(old('report_show_visual_summary', $settings['report_show_visual_summary'] ?? '1') === '1')>
                        Tampilkan ringkasan visual di laporan
                    </label>
                    <p class="muted">Pengaturan format ini dipakai permanen untuk pratinjau browser dan unduhan PDF laporan semua peran.</p>
                </div>
                <div class="form-field full">
                    <hr>
                </div>
                <div class="form-field">
                    <label for="report_letterhead_mode">Kop Surat Laporan</label>
                    <select id="report_letterhead_mode" name="report_letterhead_mode" required>
                        <option value="default" @selected(old('report_letterhead_mode', $letterheadSettings['mode']) === 'default')>Gunakan kop bawaan sistem</option>
                        <option value="custom" @selected(old('report_letterhead_mode', $letterheadSettings['mode']) === 'custom')>Gunakan kop custom</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>Template Kop Contoh</label>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.settings.report-format.template.pdf') }}">Unduh PDF</a>
                        <a class="button secondary" href="{{ route('admin.settings.report-format.template.docx') }}">Unduh DOCX</a>
                    </div>
                </div>
                <div class="form-field">
                    <label for="report_letterhead_institution">Nama Institusi di Kop</label>
                    <input id="report_letterhead_institution" name="report_letterhead_institution" value="{{ old('report_letterhead_institution', $letterheadSettings['institution']) }}" required>
                </div>
                <div class="form-field">
                    <label for="report_letterhead_unit">Nama Unit di Kop</label>
                    <input id="report_letterhead_unit" name="report_letterhead_unit" value="{{ old('report_letterhead_unit', $letterheadSettings['unit']) }}">
                </div>
                <div class="form-field full">
                    <label for="report_letterhead_address">Alamat Kop</label>
                    <textarea id="report_letterhead_address" name="report_letterhead_address">{{ old('report_letterhead_address', $letterheadSettings['address']) }}</textarea>
                </div>
                <div class="form-field full">
                    <label for="report_letterhead_contact">Kontak Kop</label>
                    <input id="report_letterhead_contact" name="report_letterhead_contact" value="{{ old('report_letterhead_contact', $letterheadSettings['contact']) }}">
                </div>
                <div class="form-field">
                    <label for="report_letterhead_logo_width">Lebar Logo Kop (px)</label>
                    <input id="report_letterhead_logo_width" type="number" min="50" max="130" name="report_letterhead_logo_width" value="{{ old('report_letterhead_logo_width', $letterheadSettings['logo_width'] ?? 88) }}" required>
                </div>
                <div class="form-field">
                    <label for="report_letterhead_institution_font_size">Ukuran Font Nama Institusi</label>
                    <input id="report_letterhead_institution_font_size" type="number" min="12" max="24" name="report_letterhead_institution_font_size" value="{{ old('report_letterhead_institution_font_size', $letterheadSettings['institution_font_size'] ?? 16) }}" required>
                    <label class="remember" style="margin-top:8px">
                        <input type="checkbox" name="report_letterhead_institution_bold" value="1" @checked(old('report_letterhead_institution_bold', $letterheadSettings['institution_bold'] ?? '1') === '1')>
                        Bold
                    </label>
                </div>
                <div class="form-field">
                    <label for="report_letterhead_unit_font_size">Ukuran Font Nama Unit</label>
                    <input id="report_letterhead_unit_font_size" type="number" min="10" max="20" name="report_letterhead_unit_font_size" value="{{ old('report_letterhead_unit_font_size', $letterheadSettings['unit_font_size'] ?? 14) }}" required>
                    <label class="remember" style="margin-top:8px">
                        <input type="checkbox" name="report_letterhead_unit_bold" value="1" @checked(old('report_letterhead_unit_bold', $letterheadSettings['unit_bold'] ?? '1') === '1')>
                        Bold
                    </label>
                </div>
                <div class="form-field">
                    <label for="report_letterhead_address_font_size">Ukuran Font Alamat/Kontak</label>
                    <input id="report_letterhead_address_font_size" type="number" min="9" max="16" name="report_letterhead_address_font_size" value="{{ old('report_letterhead_address_font_size', $letterheadSettings['address_font_size'] ?? 11) }}" required>
                    <label class="remember" style="margin-top:8px">
                        <input type="checkbox" name="report_letterhead_address_bold" value="1" @checked(old('report_letterhead_address_bold', $letterheadSettings['address_bold'] ?? '0') === '1')>
                        Bold
                    </label>
                </div>
                <div class="form-field full">
                    <label for="report_letterhead_file">Import File Kop Surat (PDF/DOCX)</label>
                    <input id="report_letterhead_file" type="file" name="report_letterhead_file" accept=".pdf,.docx">
                    @if (! empty($letterheadSettings['file_path']))
                        <span class="muted">File tersimpan: {{ $letterheadSettings['file_name'] ?? $letterheadSettings['file_path'] }}</span>
                    @endif
                    <p class="muted">Jika mengunggah DOCX, isi teks kop akan dibaca otomatis dan diterapkan ke laporan. PDF disimpan sebagai arsip sumber kop resmi.</p>
                </div>
                <div class="form-field full">
                    <button type="submit">Simpan Format Laporan</button>
                </div>
            </form>
        @elseif ($activeTab === 'categories')
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Warna</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td colspan="5">
                                    <form class="filters" method="post" action="{{ route('admin.settings.categories.update', $category) }}">
                                        @csrf
                                        @method('put')
                                        <input name="nama" value="{{ $category->nama }}" required>
                                        <input name="warna_hex" type="color" value="{{ $category->warna_hex }}" required>
                                        <input name="urutan" type="number" min="0" value="{{ $category->urutan }}" required>
                                        <label class="remember"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Aktif</label>
                                        <button type="submit">Simpan</button>
                                    </form>
                                    <div class="table-actions" style="margin-top: 10px">
                                        <x-action-icon
                                            :action="route('admin.settings.categories.toggle', $category)"
                                            method="patch"
                                            icon="power"
                                            :label="$category->is_active ? 'Nonaktifkan kategori' : 'Aktifkan kategori'"
                                            :tone="$category->is_active ? 'warning' : 'success'"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form class="form-grid panel" method="post" action="{{ route('admin.settings.categories.store') }}">
                @csrf
                <div class="form-field">
                    <label for="nama">Nama Kategori Baru</label>
                    <input id="nama" name="nama" required>
                </div>
                <div class="form-field">
                    <label for="warna_hex">Warna Badge</label>
                    <input id="warna_hex" type="color" name="warna_hex" value="#667085" required>
                </div>
                <div class="form-field">
                    <label for="urutan">Urutan</label>
                    <input id="urutan" type="number" name="urutan" min="0" value="10" required>
                </div>
                <div class="form-field">
                    <label class="remember"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
                </div>
                <div class="form-field full">
                    <button type="submit">Tambah Kategori</button>
                </div>
            </form>
        @elseif ($activeTab === 'uploads')
            <form class="form-grid" method="post" action="{{ route('admin.settings.upload') }}">
                @csrf
                @method('patch')
                <div class="form-field">
                    <label for="max_file_size_mb">Ukuran Maksimum per Unggahan (MB)</label>
                    <input id="max_file_size_mb" type="number" min="1" max="100" name="max_file_size_mb" value="{{ old('max_file_size_mb', $settings['max_file_size_mb'] ?? 10) }}" required>
                </div>
                <div class="form-field full">
                    <label>Jenis File Diizinkan</label>
                    <div class="actions">
                        @foreach ($fileTypes as $type)
                            <label class="remember">
                                <input type="checkbox" name="allowed_file_types[]" value="{{ $type }}" @checked(in_array($type, old('allowed_file_types', $allowedFileTypes), true))>
                                {{ strtoupper($type) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-field full">
                    <button type="submit">Simpan Batas Unggah</button>
                </div>
            </form>
        @elseif ($activeTab === 'advanced')
            <section class="advanced-settings-grid">
                <div class="danger-zone-card">
                    <div class="danger-confirm-icon">!</div>
                    <div>
                        <h3>Reset Public</h3>
                        <p class="muted">Menghapus data audit, data master, notifikasi, lampiran audit, dan semua pengguna selain admin yang sedang login. Konfigurasi sistem tetap dipertahankan.</p>
                    </div>

                    <div class="reset-impact-list">
                        <span>Terhapus: periode, penugasan, unit, standar, instrumen, evaluasi, bukti, klarifikasi, visitasi, temuan, tindak lanjut, notifikasi.</span>
                        <span>Tersisa: akun admin ini, pengaturan sistem, kategori temuan, template notifikasi.</span>
                    </div>

                    <form class="form-grid" method="post" action="{{ route('admin.settings.advanced.reset-public') }}">
                        @csrf
                        <div class="form-field full">
                            <label for="reset_public_confirmation">Ketik RESET PUBLIC</label>
                            <input id="reset_public_confirmation" name="confirmation" placeholder="RESET PUBLIC" required>
                        </div>
                        <div class="form-field full">
                            <button
                                class="danger-confirm-submit"
                                type="submit"
                                data-danger-confirm
                                data-danger-title="Reset data public?"
                                data-danger-message="Semua data audit dan semua user selain admin ini akan dihapus. Tindakan ini tidak bisa dibatalkan."
                                data-danger-confirm-label="Ya, Reset"
                                data-danger-countdown="10"
                            >Reset Public</button>
                        </div>
                    </form>
                </div>
            </section>
        @else
            <form method="post" action="{{ route('admin.settings.templates') }}">
                @csrf
                @method('patch')
                <p class="muted">Placeholder tersedia: {nama_unit}, {nama_periode}, {nomor_temuan}, {nama_auditor}, {batas_waktu}</p>
                <div class="panel" style="margin:16px 0">
                    <label class="remember">
                        <input type="checkbox" name="email_notifications_enabled" value="1" @checked(old('email_notifications_enabled', $settings['email_notifications_enabled'] ?? '1') === '1')>
                        Kirim email untuk notifikasi penting ke Auditor dan Auditee
                    </label>
                    <p class="muted" style="margin:8px 0 0">Email memakai konfigurasi SMTP pada file .env. Jika MAIL_MAILER=log, email akan masuk ke log aplikasi untuk testing.</p>
                </div>
                <div class="dashboard-list">
                    @foreach ($templates as $template)
                        <div class="list-item">
                            <h3 class="panel-title">{{ $template->tipe }}</h3>
                            <div class="form-grid">
                                <div class="form-field">
                                    <label>Judul Template</label>
                                    <input name="templates[{{ $template->id }}][judul_template]" value="{{ old("templates.{$template->id}.judul_template", $template->judul_template) }}" required>
                                </div>
                                <div class="form-field full">
                                    <label>Isi Template</label>
                                    <textarea name="templates[{{ $template->id }}][isi_template]" required>{{ old("templates.{$template->id}.isi_template", $template->isi_template) }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top:16px">
                    <button type="submit">Simpan Template</button>
                </div>
            </form>
        @endif
    </div>
@endsection
