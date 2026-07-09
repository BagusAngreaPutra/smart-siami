@extends('layouts.app')

@section('title', 'Notifikasi - SMART SIAMI')
@section('page_title', 'Notifikasi')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <div class="filters">
                <a class="button secondary" href="{{ route('notifications.index') }}">Semua</a>
                <a class="button secondary" href="{{ route('notifications.index', ['status' => 'unread']) }}">Belum Dibaca</a>
                <a class="button secondary" href="{{ route('notifications.index', ['status' => 'read']) }}">Sudah Dibaca</a>
            </div>
            <form method="post" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('patch')
                <button type="submit">Tandai Semua Sudah Dibaca</button>
            </form>
        </div>

        <div class="dashboard-list">
            @forelse ($notifications as $notification)
                <div class="list-item notification-list-row">
                    <a class="notification-list-link" href="{{ route('notifications.open', $notification) }}">
                        <div class="actions" style="justify-content:space-between">
                            <strong>{{ $notification->judul }}</strong>
                            <span class="badge {{ $notification->is_read ? 'neutral' : 'danger' }}">{{ $notification->is_read ? 'Sudah Dibaca' : 'Belum Dibaca' }}</span>
                        </div>
                        <div>{{ $notification->isi }}</div>
                        <p class="muted">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </a>
                    <form method="post" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf
                        @method('delete')
                        <button class="button secondary notification-delete-button" type="submit">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="muted">Tidak ada notifikasi aktif.</p>
            @endforelse
        </div>

        <div class="pagination">{{ $notifications->links() }}</div>
    </div>
@endsection
