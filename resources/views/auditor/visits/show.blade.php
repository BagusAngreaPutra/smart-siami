@extends('layouts.app')

@php
    $isApproved = $visit?->status === 'berita_acara_disetujui';
    $canFinishVisit = $visit && ! $visit->tanggal?->isFuture();
@endphp

@section('title', 'Detail Visitasi - SMART SIAMI')
@section('page_title', 'Detail Visitasi')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <div>
                <h3 class="panel-title">{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</h3>
                <p class="muted">{{ $assignment->auditPeriod->nama }} - Lead auditor: {{ $assignment->leadAuditor->name }}</p>
            </div>
            <span class="badge @if (! $visit || $visit->status === 'belum_dijadwalkan') off @endif">{{ $visit ? $statusOptions[$visit->status] : 'Belum Dijadwalkan' }}</span>
        </div>
    </div>

    <div class="split-panel">
        <div>
            <div class="panel">
                <h3 class="panel-title">Pengaturan Jadwal dan Catatan</h3>
                <form class="form-grid" method="post" action="{{ route('auditor.visitations.save', $assignment) }}">
                    @csrf
                    <div class="form-field">
                        <label for="tanggal">Tanggal</label>
                        <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', $visit?->tanggal?->format('Y-m-d')) }}" required @disabled($isApproved)>
                    </div>
                    <div class="form-field">
                        <label for="tipe">Tipe</label>
                        <select id="tipe" name="tipe" required @disabled($isApproved)>
                            @foreach ($tipeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('tipe', $visit?->tipe ?? 'lapangan') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="waktu_mulai">Waktu Mulai</label>
                        <input id="waktu_mulai" name="waktu_mulai" type="time" value="{{ old('waktu_mulai', $visit?->waktu_mulai) }}" @disabled($isApproved)>
                    </div>
                    <div class="form-field">
                        <label for="waktu_selesai">Waktu Selesai</label>
                        <input id="waktu_selesai" name="waktu_selesai" type="time" value="{{ old('waktu_selesai', $visit?->waktu_selesai) }}" @disabled($isApproved)>
                    </div>
                    <div class="form-field full">
                        <label for="lokasi_atau_tautan">Lokasi atau Tautan Rapat</label>
                        <input id="lokasi_atau_tautan" name="lokasi_atau_tautan" value="{{ old('lokasi_atau_tautan', $visit?->lokasi_atau_tautan) }}" @disabled($isApproved)>
                    </div>
                    <div class="form-field full">
                        <label for="agenda">Agenda</label>
                        <textarea id="agenda" name="agenda" @disabled($isApproved)>{{ old('agenda', $visit?->agenda) }}</textarea>
                    </div>
                    <div class="form-field full">
                        <label for="catatan_wawancara">Catatan Wawancara</label>
                        <textarea id="catatan_wawancara" name="catatan_wawancara" @disabled($isApproved)>{{ old('catatan_wawancara', $visit?->catatan_wawancara) }}</textarea>
                    </div>
                    <div class="form-field full">
                        <label for="catatan_observasi">Catatan Observasi</label>
                        <textarea id="catatan_observasi" name="catatan_observasi" @disabled($isApproved)>{{ old('catatan_observasi', $visit?->catatan_observasi) }}</textarea>
                    </div>
                    <div class="form-field full">
                        <label for="kesimpulan">Kesimpulan Visitasi</label>
                        <textarea id="kesimpulan" name="kesimpulan" @disabled($isApproved)>{{ old('kesimpulan', $visit?->kesimpulan) }}</textarea>
                    </div>
                    @unless ($isApproved)
                        <div class="form-field full actions">
                            <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Draft</button>
                        </div>
                    @endunless
                </form>
            </div>

            <div class="panel">
                <h3 class="panel-title">Daftar Peserta</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($visit?->participants ?? [] as $participant)
                                <tr>
                                    <td>{{ $participant->nama_peserta }}</td>
                                    <td>{{ $participant->jabatan ?? '-' }}</td>
                                    <td>{{ $participantTypes[$participant->tipe] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Belum ada peserta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Lampiran Pendukung</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Diunggah Oleh</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($visit?->attachments ?? [] as $attachment)
                                <tr>
                                    <td>{{ $attachment->nama_file }}</td>
                                    <td>{{ $attachment->tipe_sumber === 'file' ? 'File' : 'Tautan' }}</td>
                                    <td>{{ $attachment->uploader->name }}</td>
                                    <td>{{ $attachment->keterangan ?? '-' }}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($attachment->tipe_sumber === 'file')
                                                <x-action-icon :href="route('auditor.visitations.attachments.download', $attachment)" icon="download" label="Unduh file" tone="view" />
                                            @else
                                                <x-action-icon :href="$attachment->url_tautan" icon="external" label="Buka tautan" tone="view" target="_blank" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">Belum ada lampiran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside>
            @if ($visit)
                <div class="panel">
                    <h3 class="panel-title">Aksi Berita Acara</h3>
                    <div class="actions">
                        <a class="button secondary" href="{{ route('auditor.visitations') }}">Kembali</a>
                        <a class="button secondary with-icon" href="{{ route('auditor.visitations.minutes', $assignment) }}" target="_blank"><x-ui-icon name="pdf" /> Buat Berita Acara</a>
                        @if ($visit->status !== 'berita_acara_disetujui')
                            @if ($canFinishVisit)
                                <form method="post" action="{{ route('auditor.visitations.finish', $assignment) }}">
                                    @csrf
                                    @method('patch')
                                    <button class="with-icon" type="submit"><x-ui-icon name="check" /> Tandai Selesai</button>
                                </form>
                            @else
                                <button type="button" disabled title="Aktif setelah tanggal visitasi tiba">Tandai Selesai</button>
                                <p class="muted">Tombol selesai aktif pada tanggal {{ $visit->tanggal->format('d/m/Y') }}.</p>
                            @endif
                            <form method="post" action="{{ route('auditor.visitations.send-minutes', $assignment) }}">
                                @csrf
                                <button class="with-icon" type="submit"><x-ui-icon name="message" /> Kirim Berita Acara ke Auditee</button>
                            </form>
                        @endif
                    </div>
                    <p class="muted">Konfirmasi auditee: {{ $visit->konfirmasi_auditee ? 'Sudah' : 'Belum' }}</p>
                </div>

                <div class="panel">
                    <h3 class="panel-title">Tambah Peserta</h3>
                    <form class="form-grid" method="post" action="{{ route('auditor.visitations.participants.store', $assignment) }}">
                        @csrf
                        <div class="form-field full">
                            <label for="nama_peserta">Nama Peserta</label>
                            <input id="nama_peserta" name="nama_peserta" required>
                        </div>
                        <div class="form-field full">
                            <label for="jabatan">Jabatan</label>
                            <input id="jabatan" name="jabatan">
                        </div>
                        <div class="form-field full">
                            <label for="tipe_peserta">Tipe</label>
                            <select id="tipe_peserta" name="tipe" required>
                                @foreach ($participantTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="with-icon" type="submit"><x-ui-icon name="plus" /> Tambah</button>
                    </form>
                </div>

                <div class="panel">
                    <h3 class="panel-title">Tambah Lampiran</h3>
                    <form class="form-grid" method="post" action="{{ route('auditor.visitations.attachments.store', $assignment) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-field full">
                            <label for="nama_file">Nama File</label>
                            <input id="nama_file" name="nama_file" required>
                        </div>
                        <div class="form-field full">
                            <label for="tipe_sumber">Tipe Sumber</label>
                            <select id="tipe_sumber" name="tipe_sumber" required>
                                <option value="file">File</option>
                                <option value="tautan">Tautan</option>
                            </select>
                        </div>
                        <div class="form-field full">
                            <label for="file">File</label>
                            <input id="file" name="file" type="file" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png">
                        </div>
                        <div class="form-field full">
                            <label for="url_tautan">URL Tautan</label>
                            <input id="url_tautan" name="url_tautan" type="url">
                        </div>
                        <div class="form-field full">
                            <label for="keterangan">Keterangan</label>
                            <textarea id="keterangan" name="keterangan"></textarea>
                        </div>
                        <button class="with-icon" type="submit"><x-ui-icon name="plus" /> Lampirkan</button>
                    </form>
                </div>
            @else
                <div class="panel">
                    <p class="muted">Simpan jadwal visitasi terlebih dahulu sebelum menambah peserta, lampiran, atau membuat berita acara.</p>
                </div>
            @endif
        </aside>
    </div>
@endsection
