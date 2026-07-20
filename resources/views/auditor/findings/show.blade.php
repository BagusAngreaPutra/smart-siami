@extends('layouts.app')

@section('title', 'Detail Temuan - SMART SIAMI')
@section('page_title', 'Detail Temuan')

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
                <h3 class="panel-title">{{ $finding->nomor_temuan ?? 'Draft #' . $finding->id }}</h3>
                <p class="muted">{{ $finding->assignment->unit->kode }} - {{ $finding->assignment->unit->nama }} | {{ $finding->assignment->auditPeriod->nama }}</p>
            </div>
            <span class="badge @if (in_array($finding->status, ['draft', 'dibatalkan'], true)) off @endif">{{ $statusOptions[$finding->status] }}</span>
        </div>

        <div class="actions">
            <a class="button secondary" href="{{ route('auditor.findings') }}">Kembali</a>
            @if ($finding->status !== 'dibatalkan')
                <a class="button secondary with-icon" href="{{ route('auditor.findings.edit', $finding) }}"><x-ui-icon name="save" /> Edit</a>
            @endif
            @if ($finding->status === 'draft')
                <form method="post" action="{{ route('auditor.findings.finalize', $finding) }}" onsubmit="return confirm('Finalisasi dan kirim temuan ini ke Auditee?');">
                    @csrf
                    @method('patch')
                    <button class="with-icon" type="submit"><x-ui-icon name="save" /> Finalisasi dan Kirim ke Auditee</button>
                </form>
            @endif
        </div>
    </div>

    <div class="split-panel">
        <div>
            <div class="panel">
                <h3 class="panel-title">Informasi Temuan</h3>
                <div class="table-wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th>Standar</th>
                                <td>{{ $finding->standard->kode }} - {{ $finding->standard->nama }}</td>
                            </tr>
                            <tr>
                                <th>Instrumen</th>
                                <td>{{ $finding->instrument->kode }} - {{ $finding->instrument->pertanyaan }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>{{ $kategoriOptions[$finding->kategori] }}</td>
                            </tr>
                            <tr>
                                <th>Prioritas</th>
                                <td>{{ $prioritasOptions[$finding->prioritas] }}</td>
                            </tr>
                            <tr>
                                <th>Target Penyelesaian</th>
                                <td>{{ $finding->target_penyelesaian->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Kondisi Aktual</th>
                                <td>{{ $finding->kondisi_aktual }}</td>
                            </tr>
                            <tr>
                                <th>Kriteria</th>
                                <td>{{ $finding->kriteria }}</td>
                            </tr>
                            <tr>
                                <th>Bukti Objektif</th>
                                <td>{{ $finding->bukti_objektif }}</td>
                            </tr>
                            <tr>
                                <th>Akar Masalah Awal</th>
                                <td>{{ $finding->akar_masalah_awal ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Rekomendasi Auditor</th>
                                <td>{{ $finding->rekomendasi_auditor }}</td>
                            </tr>
                            <tr>
                                <th>Dibuat Oleh</th>
                                <td>{{ $finding->creator->name }}</td>
                            </tr>
                            <tr>
                                <th>Finalisasi</th>
                                <td>{{ $finding->finalizer?->name ?? '-' }} {{ $finding->waktu_finalisasi ? 'pada '.$finding->waktu_finalisasi->format('d/m/Y H:i') : '' }}</td>
                            </tr>
                            @if ($finding->status === 'dibatalkan')
                                <tr>
                                    <th>Alasan Pembatalan</th>
                                    <td>{{ $finding->alasan_pembatalan }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Riwayat Perubahan</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Perubahan</th>
                                <th>Catatan</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($finding->histories->sortByDesc('created_at') as $history)
                                <tr>
                                    <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($history->field)
                                            {{ $history->field }}: {{ $history->nilai_lama ?? '-' }} -> {{ $history->nilai_baru ?? '-' }}
                                        @else
                                            {{ $history->dari_status ?? '-' }} -> {{ $history->ke_status }}
                                        @endif
                                    </td>
                                    <td>{{ $history->catatan ?? '-' }}</td>
                                    <td>{{ $history->changer?->name ?? 'Sistem' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum ada riwayat perubahan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Tindak Lanjut Auditee</h3>
                <p class="muted">Belum ada tindak lanjut yang diajukan. Modul tindak lanjut akan mengisi bagian ini pada tahap berikutnya.</p>
            </div>
        </div>

        <aside>
            @if ($finding->status !== 'dibatalkan' && ! $finding->hasFollowUps())
                <div class="panel">
                    <h3 class="panel-title">Batalkan Temuan</h3>
                    <form class="form-grid" method="post" action="{{ route('auditor.findings.cancel', $finding) }}" onsubmit="return confirm('Batalkan temuan ini? Alasan akan disimpan permanen.');">
                        @csrf
                        @method('patch')
                        <div class="form-field full">
                            <label for="alasan_pembatalan">Alasan Pembatalan</label>
                            <textarea id="alasan_pembatalan" name="alasan_pembatalan" required></textarea>
                        </div>
                        <button type="submit">Batalkan Temuan</button>
                    </form>
                </div>
            @endif
        </aside>
    </div>
@endsection
