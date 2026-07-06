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
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>

            <div class="form-field full actions">
                <button type="submit">Reset Password</button>
                <a class="button secondary" href="{{ route('admin.users', ['tab' => 'users']) }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
