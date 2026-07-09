@extends('layouts.app')

@section('title', 'Bukti Dokumen - SMART SIAMI')
@section('page_title', 'Bukti Dokumen')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <form class="filters" method="get" action="{{ route('auditee.documents') }}">
                <div class="form-field">
                    <label for="standard_id">Standar</label>
                    <select id="standard_id" name="standard_id">
                        <option value="">Semua</option>
                        @foreach ($standards as $standard)
                            <option value="{{ $standard->id }}" @selected((string) request('standard_id') === (string) $standard->id)>{{ $standard->kode }} - {{ $standard->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="status_verifikasi">Status Verifikasi</label>
                    <select id="status_verifikasi" name="status_verifikasi">
                        <option value="">Semua</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status_verifikasi') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="tahun_dokumen">Tahun</label>
                    <select id="tahun_dokumen" name="tahun_dokumen">
                        <option value="">Semua</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected((string) request('tahun_dokumen') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit">Filter</button>
                <a class="button secondary" href="{{ route('auditee.documents') }}">Reset</a>
            </form>

            <a class="button" href="{{ route('auditee.documents.create') }}">Unggah Bukti Baru</a>
        </div>

        <div class="section-block">
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Galeri Bukti</h3>
                    <p class="muted">Preview visual untuk PDF dan gambar; tabel detail tetap tersedia di bawah.</p>
                </div>
            </div>
            <div class="evidence-grid">
                @forelse ($evidences as $evidence)
                    <x-visual.evidence-card
                        :evidence="$evidence"
                        :preview-url="$evidence->tipe_sumber === 'file' ? route('auditee.documents.preview', $evidence) : $evidence->url_tautan"
                        :download-url="$evidence->tipe_sumber === 'file' ? route('auditee.documents.download', $evidence) : null"
                    />
                @empty
                    <x-visual.empty-state title="Belum ada bukti dokumen" message="Bukti yang diunggah akan tampil sebagai galeri di sini." icon="document" />
                @endforelse
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama Dokumen</th>
                        <th>Jenis</th>
                        <th>Standar</th>
                        <th>Instrumen Terkait</th>
                        <th>Tahun</th>
                        <th>Tipe</th>
                        <th>Status Verifikasi</th>
                        <th>Tanggal Unggah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($evidences as $evidence)
                        <tr>
                            <td>{{ $evidence->nama_dokumen }}</td>
                            <td>{{ $evidence->jenis_dokumen ?? '-' }}</td>
                            <td>{{ $evidence->selfAssessment?->instrument?->standard?->kode ?? '-' }}</td>
                            <td>{{ $evidence->instrumen_terkait ?? '-' }}</td>
                            <td>{{ $evidence->tahun_dokumen ?? '-' }}</td>
                            <td>{{ $evidence->tipe_sumber === 'file' ? 'File' : 'Tautan' }}</td>
                            <td>
                                <span class="badge @if ($evidence->status_verifikasi === 'valid') @elseif ($evidence->status_verifikasi === 'perlu_klarifikasi') danger @else off @endif">
                                    {{ $statusOptions[$evidence->status_verifikasi] ?? humanStatus($evidence->status_verifikasi) }}
                                </span>
                            </td>
                            <td>{{ $evidence->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="table-actions">
                                    @if ($evidence->tipe_sumber === 'file')
                                        <x-action-icon :href="route('auditee.documents.download', $evidence)" icon="download" label="Unduh file" tone="view" />
                                    @else
                                        <x-action-icon :href="$evidence->url_tautan" icon="external" label="Buka tautan" tone="view" target="_blank" />
                                    @endif
                                    @if ($evidence->status_verifikasi === 'belum_diperiksa')
                                        <x-action-icon
                                            :action="route('auditee.documents.destroy', $evidence)"
                                            method="delete"
                                            icon="trash"
                                            label="Hapus bukti"
                                            tone="danger"
                                            :confirm="true"
                                            confirm-title="Hapus bukti dokumen?"
                                            confirm-message="Bukti dokumen yang belum diperiksa akan dihapus dari repositori unit."
                                            confirm-label="Ya, Hapus"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">Belum ada bukti dokumen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $evidences->links() }}</div>
    </div>
@endsection
