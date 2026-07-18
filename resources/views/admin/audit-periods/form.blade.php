@extends('layouts.app')

@php($isEdit = $period->exists)

@section('title', ($isEdit ? 'Edit Periode Audit' : 'Tambah Periode Audit').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Edit Periode Audit' : 'Tambah Periode Audit')

@section('content')
    <div class="panel">
        <form class="form-grid" method="post" action="{{ $isEdit ? route('admin.periods.update', $period) : route('admin.periods.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <div class="form-field full">
                <label for="nama">Nama Periode</label>
                <input id="nama" name="nama" value="{{ old('nama', $period->nama) }}" placeholder="Contoh: AMI Semester Ganjil 2026/2027" required>
                <small class="form-hint">Gunakan nama yang mencerminkan jenis dan waktu pelaksanaan audit.</small>
                @error('nama')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="tahun_akademik">Tahun Akademik</label>
                <input id="tahun_akademik" name="tahun_akademik" value="{{ old('tahun_akademik', $period->tahun_akademik) }}" placeholder="Contoh: 2026/2027" required>
                @error('tahun_akademik')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="jenis_audit">Jenis Audit</label>
                <select id="jenis_audit" name="jenis_audit" required>
                    @foreach ($jenisAuditOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('jenis_audit', $period->jenis_audit) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis_audit')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="tanggal_mulai">Tanggal Mulai</label>
                <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', $period->tanggal_mulai?->format('Y-m-d')) }}" required>
                @error('tanggal_mulai')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="batas_evaluasi_diri">Batas Evaluasi Diri</label>
                <input id="batas_evaluasi_diri" name="batas_evaluasi_diri" type="date" value="{{ old('batas_evaluasi_diri', $period->batas_evaluasi_diri?->format('Y-m-d')) }}" required>
                @error('batas_evaluasi_diri')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="batas_desk_evaluation">Batas Desk Evaluation</label>
                <input id="batas_desk_evaluation" name="batas_desk_evaluation" type="date" value="{{ old('batas_desk_evaluation', $period->batas_desk_evaluation?->format('Y-m-d')) }}" required>
                @error('batas_desk_evaluation')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="batas_tindak_lanjut">Batas Tindak Lanjut</label>
                <input id="batas_tindak_lanjut" name="batas_tindak_lanjut" type="date" value="{{ old('batas_tindak_lanjut', $period->batas_tindak_lanjut?->format('Y-m-d')) }}" required>
                @error('batas_tindak_lanjut')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="visitasi_mulai">Visitasi Mulai</label>
                <input id="visitasi_mulai" name="visitasi_mulai" type="date" value="{{ old('visitasi_mulai', $period->visitasi_mulai?->format('Y-m-d')) }}">
                @error('visitasi_mulai')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="visitasi_selesai">Visitasi Selesai</label>
                <input id="visitasi_selesai" name="visitasi_selesai" type="date" value="{{ old('visitasi_selesai', $period->visitasi_selesai?->format('Y-m-d')) }}">
                @error('visitasi_selesai')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="catatan">Catatan</label>
                <textarea id="catatan" name="catatan" placeholder="Contoh: Periode difokuskan pada evaluasi pemenuhan standar akademik dan tindak lanjut temuan tahun sebelumnya.">{{ old('catatan', $period->catatan) }}</textarea>
                @error('catatan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full actions">
                <a class="button secondary" href="{{ $isEdit ? route('admin.periods.show', $period) : route('admin.periods') }}">Batal</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Data</button>
            </div>
        </form>
    </div>
@endsection
