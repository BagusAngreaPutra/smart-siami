@extends('layouts.app')

@section('title', 'Unggah Bukti Dokumen - SMART SIAMI')
@section('page_title', 'Unggah Bukti Dokumen')

@section('content')
    <div class="panel">
        <form class="form-grid" method="post" action="{{ route('auditee.documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-field">
                <label for="assignment_id">Penugasan</label>
                <select id="assignment_id" name="assignment_id" required>
                    <option value="">Pilih penugasan</option>
                    @foreach ($assignments as $assignment)
                        <option value="{{ $assignment->id }}" @selected((string) old('assignment_id') === (string) $assignment->id)>{{ $assignment->auditPeriod->nama }} - {{ $assignment->unit->nama }}</option>
                    @endforeach
                </select>
                @error('assignment_id')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="nama_dokumen">Nama Dokumen</label>
                <input id="nama_dokumen" name="nama_dokumen" value="{{ old('nama_dokumen') }}" required>
                @error('nama_dokumen')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="jenis_dokumen">Jenis Dokumen</label>
                <input id="jenis_dokumen" name="jenis_dokumen" value="{{ old('jenis_dokumen') }}">
            </div>

            <div class="form-field">
                <label for="tahun_dokumen">Tahun Dokumen</label>
                <input id="tahun_dokumen" name="tahun_dokumen" type="number" min="1900" max="2100" value="{{ old('tahun_dokumen') }}">
            </div>

            <div class="form-field full">
                <label for="instrument_ids">Instrumen Terkait</label>
                <select id="instrument_ids" name="instrument_ids[]" multiple size="8" required>
                    @foreach ($instruments as $instrument)
                        <option value="{{ $instrument->id }}" @selected(in_array($instrument->id, array_map('intval', old('instrument_ids', [])), true))>{{ $instrument->standard->kode }} / {{ $instrument->kode }} - {{ $instrument->nama_indikator ?? $instrument->pertanyaan }}</option>
                    @endforeach
                </select>
                @error('instrument_ids')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="tipe_sumber">Tipe Sumber</label>
                <select id="tipe_sumber" name="tipe_sumber" required>
                    <option value="file" @selected(old('tipe_sumber') === 'file')>File</option>
                    <option value="tautan" @selected(old('tipe_sumber') === 'tautan')>Tautan</option>
                </select>
            </div>

            <div class="form-field">
                <label for="file">File</label>
                <input id="file" name="file" type="file" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png">
                @error('file')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="url_tautan">URL Tautan</label>
                <input id="url_tautan" name="url_tautan" type="url" value="{{ old('url_tautan') }}">
                @error('url_tautan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="form-field full actions">
                <button type="submit">Unggah Bukti</button>
                <a class="button secondary" href="{{ route('auditee.documents') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
