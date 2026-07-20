@extends('layouts.app')

@php
    $instrument = $clarification->instrument;
    $assignment = $clarification->assignment;
    $isClosed = $clarification->status === 'selesai';
@endphp

@section('title', 'Percakapan Klarifikasi Auditor - SMART SIAMI')
@section('page_title', 'Percakapan Klarifikasi Auditor')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <nav class="auditee-stage-track" aria-label="Tahap klarifikasi auditor">
        <span class="is-complete"><b>✓</b><em>Pertanyaan diterima</em></span>
        <span class="{{ $clarification->messages->count() > 1 ? 'is-complete' : 'is-current' }}"><b>{{ $clarification->messages->count() > 1 ? '✓' : '2' }}</b><em>Respons auditee</em></span>
        <span class="{{ $clarification->evidences->isNotEmpty() ? 'is-complete' : '' }}"><b>{{ $clarification->evidences->isNotEmpty() ? '✓' : '3' }}</b><em>Lampiran pendukung</em></span>
        <span class="{{ $isClosed ? 'is-complete' : '' }}"><b>{{ $isClosed ? '✓' : '4' }}</b><em>Klarifikasi selesai</em></span>
    </nav>

    <div class="panel">
        <div class="toolbar">
            <div>
                <h3 class="panel-title">{{ $instrument->standard->kode }} - {{ $instrument->standard->nama }}</h3>
                <p class="muted">{{ $assignment->auditPeriod->nama }} - Instrumen {{ $instrument->kode }}</p>
            </div>
            <span class="badge @if ($isClosed) off @endif">{{ $statusOptions[$clarification->status] }}</span>
        </div>

        <div class="table-wrap">
            <table>
                <tbody>
                    <tr>
                        <th>Instrumen</th>
                        <td>{{ $instrument->kode }} - {{ $instrument->nama_indikator ?? 'Instrumen Audit' }}</td>
                    </tr>
                    <tr>
                        <th>Pertanyaan</th>
                        <td>{{ $instrument->pertanyaan }}</td>
                    </tr>
                    <tr>
                        <th>Dibuka Oleh</th>
                        <td>{{ $clarification->openedBy->name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="split-panel">
        <div>
            <div class="panel">
                <h3 class="panel-title">Riwayat Pesan</h3>

                @forelse ($clarification->messages->sortBy('created_at') as $message)
                    <div class="section-block">
                        <div class="toolbar">
                            <div>
                                <strong>{{ $message->sender->name }}</strong>
                                <div class="muted">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <p>{{ $message->isi_pesan }}</p>
                    </div>
                @empty
                    <p class="muted">Belum ada pesan.</p>
                @endforelse
            </div>

            <div class="panel">
                <h3 class="panel-title">Lampiran Klarifikasi</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Dokumen</th>
                                <th>Tipe</th>
                                <th>Diunggah Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clarification->evidences as $evidence)
                                <tr>
                                    <td>{{ $evidence->nama_dokumen }}</td>
                                    <td>{{ $evidence->tipe_sumber === 'file' ? 'File' : 'Tautan' }}</td>
                                    <td>{{ $evidence->uploader->name }}</td>
                                    <td>
                                        <div class="table-actions">
                                            @if ($evidence->tipe_sumber === 'file')
                                                <x-action-icon :href="route('auditee.clarifications.evidences.download', $evidence)" icon="download" label="Unduh file" tone="view" />
                                            @else
                                                <x-action-icon :href="$evidence->url_tautan" icon="external" label="Buka tautan" tone="view" target="_blank" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum ada lampiran.</td>
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
                    <a class="button secondary" href="{{ route('auditee.clarifications') }}">Kembali</a>
                    @if ($assessment)
                        <a class="button secondary" href="{{ route('auditee.self-assessments.edit', $assessment) }}">Buka Instrumen Terkait</a>
                    @endif
                    @if (! $isClosed)
                        <form method="post" action="{{ route('auditee.clarifications.answered', $clarification) }}">
                            @csrf
                            @method('patch')
                            <button type="submit">Tandai Sudah Dijawab</button>
                        </form>
                    @endif
                </div>
            </div>

            @if (! $isClosed)
                <div class="panel">
                    <h3 class="panel-title">Balas Pesan</h3>
                    <form class="form-grid" method="post" action="{{ route('auditee.clarifications.messages.store', $clarification) }}">
                        @csrf
                        <div class="form-field full">
                            <label for="isi_pesan">Pesan</label>
                            <textarea id="isi_pesan" name="isi_pesan" required>{{ old('isi_pesan') }}</textarea>
                            @error('isi_pesan')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit">Kirim</button>
                    </form>
                </div>

                <div class="panel">
                    <h3 class="panel-title">Tambah Lampiran</h3>
                    <form class="form-grid" method="post" action="{{ route('auditee.clarifications.evidences.store', $clarification) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-field full">
                            <label for="nama_dokumen">Nama Dokumen</label>
                            <input id="nama_dokumen" name="nama_dokumen" required>
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
                        <button type="submit">Lampirkan</button>
                    </form>
                </div>
            @endif
        </aside>
    </div>
@endsection
