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
                <input id="kode" name="kode" value="{{ old('kode', $unit->kode) }}" required>
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
                <input id="nama" name="nama" value="{{ old('nama', $unit->nama) }}" required>
                @error('nama')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="fakultas_induk">Fakultas Induk</label>
                <input id="fakultas_induk" name="fakultas_induk" value="{{ old('fakultas_induk', $unit->fakultas_induk) }}">
                @error('fakultas_induk')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="nama_pimpinan">Nama Pimpinan</label>
                <input id="nama_pimpinan" name="nama_pimpinan" value="{{ old('nama_pimpinan', $unit->nama_pimpinan) }}">
                @error('nama_pimpinan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $unit->email) }}">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" name="phone" value="{{ old('phone', $unit->phone) }}">
                @error('phone')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $unit->is_active))>
                Aktif
            </label>

            <div class="form-field full actions">
                <button type="submit">Simpan</button>
                <a class="button secondary" href="{{ route('admin.users', ['tab' => 'units']) }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
