@extends('layouts.app')

@section('title', $title . ' - SMART SIAMI')
@section('page_title', $title)

@section('content')
    <div class="panel">
        <h3 class="panel-title">Selamat datang, {{ auth()->user()->name }}</h3>
        <p class="muted">
            Anda masuk sebagai {{ auth()->user()->role->label() }}. Menu di sisi kiri sudah dibatasi sesuai peran akun.
        </p>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Role</div>
                <div class="summary-value">{{ auth()->user()->role->label() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">NIP/NIDN</div>
                <div class="summary-value">{{ auth()->user()->nip_nidn }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Unit</div>
                <div class="summary-value">{{ auth()->user()->unit?->nama ?? '-' }}</div>
            </div>
        </div>
    </div>
@endsection
