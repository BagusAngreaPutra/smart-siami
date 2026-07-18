@extends('layouts.app')

@section('title', 'Reset Password - SMART SIAMI')
@section('page_title', 'Reset Password')

@section('content')
    <div class="panel">
        <h3 class="panel-title">{{ $managedUser->name }}</h3>
        <p class="muted">{{ $managedUser->email }}</p>

        <form class="form-grid" method="post" action="{{ route('admin.managed-users.password.update', $managedUser) }}">
            @csrf
            @method('patch')

            <div class="form-field">
                <label for="password">Password Baru</label>
                <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                <small class="form-hint">Gunakan kombinasi huruf, angka, dan simbol yang sulit ditebak.</small>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ketik ulang password baru" required>
            </div>

            <div class="form-field full actions">
                <a class="button secondary" href="{{ route('admin.users', ['tab' => 'users']) }}">Batal</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Password</button>
            </div>
        </form>
    </div>
@endsection
