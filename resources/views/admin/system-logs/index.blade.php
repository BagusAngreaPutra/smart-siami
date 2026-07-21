@extends('layouts.app')

@section('title', 'Jejak Sistem - SMART SIAMI')
@section('page_title', 'Jejak Sistem')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    <section class="system-log-overview" aria-label="Ringkasan jejak sistem">
        <div class="system-log-overview-copy">
            <span class="system-log-overview-icon"><x-ui-icon name="history" /></span>
            <div>
                <span class="system-log-eyebrow">Riwayat aktivitas</span>
                <h2>Setiap perubahan penting kini dapat ditelusuri</h2>
                <p>Periksa pelaku, waktu, sumber akses, dan data permintaan dari aktivitas yang tercatat.</p>
            </div>
        </div>
        <div class="system-log-overview-stat">
            <strong>{{ number_format($logs->total()) }}</strong>
            <span>aktivitas ditemukan</span>
        </div>
    </section>

    <div class="panel system-log-list-panel">
        <div class="toolbar system-log-toolbar">
            <form class="filters" method="get" action="{{ route('admin.system-logs') }}">
                <div class="form-field system-log-search-field">
                    <label for="search">Cari Aktivitas</label>
                    <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Contoh: periode, pengguna, atau route">
                </div>

                <div class="form-field">
                    <label for="event">Jenis</label>
                    <select id="event" name="event">
                        <option value="">Semua jenis</option>
                        @foreach ($eventOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('event') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="actor">Pelaku</label>
                    <select id="actor" name="actor">
                        <option value="">Semua pelaku</option>
                        @foreach ($actors as $actor)
                            <option value="{{ $actor->actor_email }}" @selected(request('actor') === $actor->actor_email)>{{ $actor->actor_name ?: $actor->actor_email }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="date_from">Dari Tanggal</label>
                    <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}">
                </div>

                <div class="form-field">
                    <label for="date_to">Sampai Tanggal</label>
                    <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}">
                </div>

                <button class="button-icon-only" type="submit" title="Terapkan filter" aria-label="Terapkan filter"><x-ui-icon name="filter" /></button>
                <a class="button button-reset button-icon-only" href="{{ route('admin.system-logs') }}" title="Reset filter" aria-label="Reset filter"><x-ui-icon name="reset" /></a>
            </form>
        </div>

        <form id="bulk-action-system-logs" class="bulk-action-bar standard-bulk-action-bar" method="post" action="{{ route('admin.system-logs.bulk-action') }}" hidden data-bulk-action-bar>
            @csrf
            <span class="bulk-action-count"><span data-bulk-selected-count>0</span> dipilih</span>
            <button
                class="button secondary bulk-delete-button with-icon"
                type="submit"
                name="action"
                value="delete"
                data-bulk-action-button
                data-danger-confirm
                data-danger-title="Hapus jejak terpilih?"
                data-danger-message="Riwayat yang dihapus tidak dapat dikembalikan."
                data-danger-message-template="Hapus {count} jejak sistem yang dicentang? Riwayat yang dihapus tidak dapat dikembalikan."
                data-danger-confirm-label="Ya, Hapus"
            ><x-ui-icon name="trash" /> Hapus</button>
        </form>

        <div class="table-wrap" data-bulk-container>
            <table class="system-log-table">
                <thead>
                    <tr>
                        <th class="instrument-select-cell"><input type="checkbox" aria-label="Pilih semua jejak pada halaman ini" data-bulk-select-all></th>
                        <th>Waktu</th>
                        <th>Pelaku</th>
                        <th>Aktivitas</th>
                        <th>Jenis</th>
                        <th>Sumber</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="instrument-select-cell">
                                <input type="checkbox" name="system_log_ids[]" value="{{ $log->id }}" form="bulk-action-system-logs" aria-label="Pilih jejak {{ $log->action }}" data-bulk-select>
                            </td>
                            <td class="system-log-time">
                                <strong>{{ $log->created_at->format('d/m/Y') }}</strong>
                                <span>{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td>
                                <div class="system-log-actor">
                                    <span class="system-log-avatar">{{ str($log->actor_name ?? 'S')->substr(0, 1)->upper() }}</span>
                                    <div>
                                        <strong>{{ $log->actor_name ?? 'Sistem' }}</strong>
                                        <span>{{ $log->actorRoleLabel() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="system-log-action-copy">
                                <strong>{{ $log->action }}</strong>
                                <span>{{ $log->route_name ?? 'Aktivitas internal' }}</span>
                            </td>
                            <td><span class="system-log-event tone-{{ $log->eventTone() }}">{{ $log->eventLabel() }}</span></td>
                            <td>
                                <span class="system-log-method">{{ $log->method ?? '-' }}</span>
                                <small>{{ $log->ip_address ?? 'IP tidak tersedia' }}</small>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('admin.system-logs.show', $log)" icon="eye" label="Buka detail jejak" tone="view" />
                                    <x-action-icon
                                        :action="route('admin.system-logs.destroy', $log)"
                                        method="delete"
                                        icon="trash"
                                        label="Hapus jejak"
                                        tone="danger"
                                        :confirm="true"
                                        confirm-title="Hapus jejak sistem?"
                                        confirm-message="Riwayat aktivitas ini akan dihapus permanen."
                                        confirm-label="Ya, Hapus"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="system-log-empty">
                                    <span><x-ui-icon name="history" /></span>
                                    <strong>Belum ada jejak yang sesuai</strong>
                                    <p>Aktivitas perubahan data akan muncul otomatis di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $logs->links() }}</div>
    </div>
@endsection
