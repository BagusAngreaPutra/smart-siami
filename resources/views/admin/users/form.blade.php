@extends('layouts.app')

@php($isEdit = $managedUser->exists)

@section('title', ($isEdit ? 'Edit Pengguna' : 'Tambah Pengguna').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
    <div class="panel">
        <form class="form-grid" method="post" action="{{ $isEdit ? route('admin.managed-users.update', $managedUser) : route('admin.managed-users.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <div class="form-field">
                <label for="name">Nama</label>
                <input id="name" name="name" value="{{ old('name', $managedUser->name) }}" placeholder="Contoh: Dr. Rina Puspitasari" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="nip_nidn">NIP/NIDN</label>
                <input id="nip_nidn" name="nip_nidn" value="{{ old('nip_nidn', $managedUser->nip_nidn) }}" placeholder="Contoh: 198901152015032001">
                <small class="form-hint">Isi NIP atau NIDN tanpa spasi jika tersedia.</small>
                @error('nip_nidn')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" placeholder="Contoh: rina@universitas.ac.id" required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" name="phone" value="{{ old('phone', $managedUser->phone) }}" placeholder="Contoh: 0812-3456-7890">
                @error('phone')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="role">Peran</label>
                <select id="role" name="role" required>
                    @foreach ($roleOptions as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $managedUser->role?->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                @error('role')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="unit_id">Unit</label>
                <select id="unit_id" name="unit_id">
                    <option value="">Pilih unit jika diperlukan</option>
                    @foreach ($unitOptions as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id', $managedUser->unit_id) === (string) $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
                    @endforeach
                </select>
                @error('unit_id')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            @unless ($isEdit)
                <div class="form-field">
                    <label for="password">Password Awal</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter">
                    <small class="form-hint">Buat password awal yang kemudian dapat diganti pengguna.</small>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            @endunless

            <div class="form-field full">
                <label class="crm-toggle-card">
                    <span class="crm-toggle-copy">
                        <span class="crm-toggle-icon tone-violet" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle><path d="m16.5 14.5 1.5 1.5 3-3"></path></svg>
                        </span>
                        <span>
                            <strong>Status Pengguna</strong>
                            <small>Pengguna aktif dapat masuk dan menggunakan fitur sesuai perannya.</small>
                        </span>
                    </span>
                    <span class="crm-toggle-control">
                        <input class="crm-toggle-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $managedUser->is_active))>
                        <span class="crm-toggle-track" aria-hidden="true"><i></i></span>
                        <span class="crm-toggle-state" aria-hidden="true">
                            <span class="state-on">Aktif</span>
                            <span class="state-off">Nonaktif</span>
                        </span>
                    </span>
                </label>
            </div>

            <div class="form-field full actions">
                <a class="button secondary" href="{{ route('admin.users', ['tab' => 'users']) }}">Batal</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Data</button>
            </div>
        </form>
    </div>
@endsection
