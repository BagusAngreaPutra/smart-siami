@extends('layouts.app')

@php($isEdit = $standard->exists)

@section('title', ($isEdit ? 'Edit Standar' : 'Tambah Standar').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Edit Standar' : 'Tambah Standar')

@section('content')
    <div class="panel">
        <form class="form-grid" method="post" action="{{ $isEdit ? route('admin.quality-standards.update', $standard) : route('admin.quality-standards.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <div class="form-field">
                <label for="kode">Kode</label>
                <input id="kode" name="kode" value="{{ old('kode', $standard->kode) }}" required>
                @error('kode')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="urutan">Urutan</label>
                <input id="urutan" name="urutan" type="number" min="0" value="{{ old('urutan', $standard->urutan) }}" required>
                @error('urutan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="nama">Nama</label>
                <input id="nama" name="nama" value="{{ old('nama', $standard->nama) }}" required>
                @error('nama')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi">{{ old('deskripsi', $standard->deskripsi) }}</textarea>
                @error('deskripsi')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="target">Target</label>
                <textarea id="target" name="target">{{ old('target', $standard->target) }}</textarea>
                @error('target')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $standard->is_active))>
                Aktif
            </label>

            <div class="form-field full actions">
                <button type="submit">Simpan</button>
                <a class="button secondary" href="{{ route('admin.standards', ['tab' => 'standards']) }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
