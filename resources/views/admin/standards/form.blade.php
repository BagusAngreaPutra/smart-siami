@extends('layouts.app')

@php($isEdit = $standard->exists)

@section('title', ($isEdit ? 'Edit Kriteria/Standar' : 'Tambah Kriteria/Standar').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Edit Kriteria/Standar' : 'Tambah Kriteria/Standar')

@section('content')
    <div class="panel">
        <form class="form-grid" method="post" action="{{ $isEdit ? route('admin.quality-standards.update', $standard) : route('admin.quality-standards.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <div class="form-field">
                <label for="kode">Kode</label>
                <input id="kode" name="kode" value="{{ old('kode', $standard->kode) }}" placeholder="Contoh: STD-01 atau C.1" required>
                @error('kode')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="urutan">Urutan</label>
                <input id="urutan" name="urutan" type="number" min="0" value="{{ old('urutan', $standard->urutan) }}" placeholder="Contoh: 1" required>
                @error('urutan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="nama">Nama</label>
                <input id="nama" name="nama" value="{{ old('nama', $standard->nama) }}" placeholder="Contoh: Standar Tata Pamong dan Tata Kelola" required>
                @error('nama')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" placeholder="Contoh: Standar ini menilai efektivitas tata kelola, kepemimpinan, dan sistem penjaminan mutu.">{{ old('deskripsi', $standard->deskripsi) }}</textarea>
                @error('deskripsi')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="target">Target</label>
                <textarea id="target" name="target" placeholder="Contoh: Seluruh unit memiliki dokumen tata kelola yang ditinjau minimal satu kali setiap tahun.">{{ old('target', $standard->target) }}</textarea>
                @error('target')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label class="crm-toggle-card">
                    <span class="crm-toggle-copy">
                        <span class="crm-toggle-icon tone-orange" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"></path><path d="M8 7h8M8 11h8M8 15h5"></path></svg>
                        </span>
                        <span>
                            <strong>Status Kriteria/Standar</strong>
                            <small>Kriteria aktif dapat dipilih saat menyusun instrumen AMI.</small>
                        </span>
                    </span>
                    <span class="crm-toggle-control">
                        <input class="crm-toggle-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $standard->is_active))>
                        <span class="crm-toggle-track" aria-hidden="true"><i></i></span>
                        <span class="crm-toggle-state" aria-hidden="true">
                            <span class="state-on">Aktif</span>
                            <span class="state-off">Nonaktif</span>
                        </span>
                    </span>
                </label>
            </div>

            <div class="form-field full actions">
                <a class="button secondary" href="{{ route('admin.standards') }}">Batal</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Data</button>
            </div>
        </form>
    </div>
@endsection
