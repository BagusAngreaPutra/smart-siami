@extends('layouts.app')

@php
    $instrument = $clarification->instrument;
    $assignment = $clarification->assignment;
    $isClosed = $clarification->status === 'selesai';
@endphp

@section('title', 'Percakapan Klarifikasi - SMART SIAMI')
@section('page_title', 'Percakapan Klarifikasi')

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
                <p class="muted">{{ $assignment->auditPeriod->nama }} - {{ $instrument->standard->kode }} / {{ $instrument->kode }}</p>
            </div>
            <span class="badge @if ($isClosed) off @endif">{{ $statusOptions[$clarification->status] }}</span>
        </div>

        <div class="table-wrap">
            <table>
                <tbody>
                    <tr>
                        <th>Standar</th>
                        <td>{{ $instrument->standard->kode }} - {{ $instrument->standard->nama }}</td>
                    </tr>
                    <tr>
                        <th>Instrumen</th>
                        <td>{{ $instrument->kode }} - {{ $instrument->nama_indikator ?? 'Instrumen Audit' }}</td>
                    </tr>
                    <tr>
                        <th>Pertanyaan</th>
                        <td>{{ $instrument->pertanyaan }}</td>
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
                                        @if ($evidence->tipe_sumber === 'file')
                                            <a class="link-button" href="{{ route('auditor.clarifications.evidences.download', $evidence) }}">Unduh</a>
                                        @else
                                            <a class="link-button" href="{{ $evidence->url_tautan }}" target="_blank">Buka</a>
                                        @endif
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
                <h3 class="panel-title">Aksi Klarifikasi</h3>
                <div class="actions">
                    <a class="button secondary" href="{{ route('auditor.clarifications') }}">Kembali</a>
                    @if (! $isClosed)
                        <form method="post" action="{{ route('auditor.clarifications.finish', $clarification) }}" onsubmit="return confirm('Tandai klarifikasi ini selesai?');">
                            @csrf
                            @method('patch')
                            <button type="submit">Tandai Selesai</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('auditor.clarifications.reopen', $clarification) }}" onsubmit="return confirm('Buka kembali klarifikasi ini?');">
                            @csrf
                            @method('patch')
                            <button type="submit">Buka Kembali</button>
                        </form>
                    @endif
                </div>
            </div>

            @if (! $isClosed)
                <div class="panel">
                    <h3 class="panel-title">Kirim Pesan</h3>
                    <form class="form-grid" method="post" action="{{ route('auditor.clarifications.messages.store', $clarification) }}">
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
                    <h3 class="panel-title">Lampirkan Dokumen</h3>
                    <form class="form-grid" method="post" action="{{ route('auditor.clarifications.evidences.store', $clarification) }}" enctype="multipart/form-data">
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
