@extends('layouts.app')

@php
    $assignment = $visit->assignment;
    $canDownloadMinutes = in_array($visit->status, ['selesai', 'berita_acara_disetujui'], true);
@endphp

@section('title', 'Detail Jadwal Visitasi - SMART SIAMI')
@section('page_title', 'Detail Jadwal Visitasi')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="split-panel">
        <div>
            <div class="panel">
                <div class="toolbar">
                    <div>
                        <h3 class="panel-title">{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</h3>
                        <p class="muted">{{ $assignment->auditPeriod->nama }}</p>
                    </div>
                    <span class="badge @if ($visit->status === 'belum_dijadwalkan') off @endif">{{ $statusOptions[$visit->status] }}</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th>Tanggal</th>
                                <td>{{ $visit->tanggal->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Waktu</th>
                                <td>{{ $visit->waktu_mulai ?? '-' }} - {{ $visit->waktu_selesai ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tipe</th>
                                <td>{{ $tipeOptions[$visit->tipe] }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi/Tautan</th>
                                <td>{{ $visit->lokasi_atau_tautan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Agenda</th>
                                <td>{{ $visit->agenda ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Daftar Auditor</th>
                                <td>
                                    {{ $assignment->leadAuditor->name }} (Lead)
                                    @foreach ($assignment->auditors->where('id', '!=', $assignment->lead_auditor_id) as $auditor)
                                        <br>{{ $auditor->name }} (Anggota)
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th>Konfirmasi Kehadiran</th>
                                <td>{{ $visit->konfirmasi_auditee ? 'Sudah dikonfirmasi pada '.$visit->waktu_konfirmasi_auditee?->format('d/m/Y H:i') : 'Belum dikonfirmasi' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Peserta Visitasi</h3>
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
                            @forelse ($visit->participants as $participant)
                                <tr>
                                    <td>{{ $participant->nama_peserta }}</td>
                                    <td>{{ $participant->jabatan ?? '-' }}</td>
                                    <td>{{ ucfirst($participant->tipe) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Belum ada peserta yang dicatat auditor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Lampiran Visitasi</h3>
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
                            @forelse ($visit->attachments as $attachment)
                                <tr>
                                    <td>{{ $attachment->nama_file }}</td>
                                    <td>{{ $attachment->tipe_sumber === 'file' ? 'File' : 'Tautan' }}</td>
                                    <td>{{ $attachment->uploader->name }}</td>
                                    <td>{{ $attachment->keterangan ?? '-' }}</td>
                                    <td>
                                        @if ($attachment->tipe_sumber === 'file')
                                            <a class="link-button" href="{{ route('auditee.visit-schedules.attachments.download', $attachment) }}">Unduh</a>
                                        @else
                                            <a class="link-button" href="{{ $attachment->url_tautan }}" target="_blank">Buka</a>
                                        @endif
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
            <div class="panel">
                <h3 class="panel-title">Aksi</h3>
                <div class="actions">
                    <a class="button secondary" href="{{ route('auditee.visit-schedules') }}">Kembali</a>
                    @if (! $visit->konfirmasi_auditee)
                        <form method="post" action="{{ route('auditee.visit-schedules.confirm', $visit) }}">
                            @csrf
                            @method('patch')
                            <button type="submit">Konfirmasi Kehadiran</button>
                        </form>
                    @endif
                    @if ($canDownloadMinutes)
                        <a class="button secondary" href="{{ route('auditee.visit-schedules.minutes', $visit) }}" target="_blank">Unduh Berita Acara</a>
                    @endif
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Unggah Dokumen Tambahan</h3>
                <form class="form-grid" method="post" action="{{ route('auditee.visit-schedules.attachments.store', $visit) }}" enctype="multipart/form-data">
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
                    <button type="submit">Unggah</button>
                </form>
            </div>
        </aside>
    </div>
@endsection
