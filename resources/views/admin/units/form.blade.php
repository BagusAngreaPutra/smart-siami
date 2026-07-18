@extends('layouts.app')

@php($isEdit = $unit->exists)

@section('title', ($isEdit ? 'Edit Unit' : 'Tambah Unit').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Edit Unit' : 'Tambah Unit')

@section('content')
    <div class="panel">
        <form class="form-grid" method="post" action="{{ $isEdit ? route('admin.units.update', $unit) : route('admin.units.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <div class="form-field">
                <label for="kode">Kode</label>
                <input id="kode" name="kode" value="{{ old('kode', $unit->kode) }}" placeholder="Contoh: FTI, PRODI-TI, atau LPM" required>
                <small class="form-hint">Gunakan kode singkat yang unik dan mudah dikenali.</small>
                @error('kode')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="jenis_unit">Jenis Unit</label>
                <select id="jenis_unit" name="jenis_unit" required>
                    @foreach ($jenisUnitOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('jenis_unit', $unit->jenis_unit) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis_unit')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="nama">Nama</label>
                <input id="nama" name="nama" value="{{ old('nama', $unit->nama) }}" placeholder="Contoh: Program Studi Teknik Informatika" required>
                @error('nama')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="fakultas_induk">Fakultas Induk</label>
                <input id="fakultas_induk" name="fakultas_induk" value="{{ old('fakultas_induk', $unit->fakultas_induk) }}" placeholder="Contoh: Fakultas Teknologi Informasi">
                @error('fakultas_induk')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="nama_pimpinan">Nama Pimpinan</label>
                <input id="nama_pimpinan" name="nama_pimpinan" value="{{ old('nama_pimpinan', $unit->nama_pimpinan) }}" placeholder="Contoh: Dr. Budi Santoso, M.Kom.">
                @error('nama_pimpinan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $unit->email) }}" placeholder="Contoh: informatika@universitas.ac.id">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" name="phone" value="{{ old('phone', $unit->phone) }}" placeholder="Contoh: 021-555-0123 atau 0812-3456-7890">
                @error('phone')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label class="crm-toggle-card">
                    <span class="crm-toggle-copy">
                        <span class="crm-toggle-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"></path></svg>
                        </span>
                        <span>
                            <strong>Status Unit</strong>
                            <small>Unit aktif dapat digunakan pada pengguna dan penugasan audit.</small>
                        </span>
                    </span>
                    <span class="crm-toggle-control">
                        <input class="crm-toggle-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $unit->is_active))>
                        <span class="crm-toggle-track" aria-hidden="true"><i></i></span>
                        <span class="crm-toggle-state" aria-hidden="true">
                            <span class="state-on">Aktif</span>
                            <span class="state-off">Nonaktif</span>
                        </span>
                    </span>
                </label>
            </div>

            <div class="form-field full actions">
                <a class="button secondary" href="{{ route('admin.users', ['tab' => 'units']) }}">Batal</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Data</button>
            </div>
        </form>
    </div>
@endsection
