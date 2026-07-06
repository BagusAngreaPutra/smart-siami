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
                <input id="name" name="name" value="{{ old('name', $managedUser->name) }}" required>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="nip_nidn">NIP/NIDN</label>
                <input id="nip_nidn" name="nip_nidn" value="{{ old('nip_nidn', $managedUser->nip_nidn) }}">
                @error('nip_nidn')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" name="phone" value="{{ old('phone', $managedUser->phone) }}">
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
                    <option value="">-</option>
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
                    <input id="password" name="password" type="password" autocomplete="new-password">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            @endunless

            <label class="remember">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $managedUser->is_active))>
                Aktif
            </label>

            <div class="form-field full actions">
                <button type="submit">Simpan</button>
                <a class="button secondary" href="{{ route('admin.users', ['tab' => 'users']) }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
