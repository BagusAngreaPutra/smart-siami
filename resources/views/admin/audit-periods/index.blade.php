@extends('layouts.app')

@section('title', 'Periode Audit - SMART SIAMI')
@section('page_title', 'Periode Audit')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <form class="filters" method="get" action="{{ route('admin.periods') }}">
                <div class="form-field">
                    <label for="tahun_akademik">Tahun</label>
                    <select id="tahun_akademik" name="tahun_akademik">
                        <option value="">Semua</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected(request('tahun_akademik') === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="jenis_audit">Jenis</label>
                    <select id="jenis_audit" name="jenis_audit">
                        <option value="">Semua</option>
                        @foreach ($jenisAuditOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('jenis_audit') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit">Filter</button>
                <a class="button secondary" href="{{ route('admin.periods') }}">Reset</a>
            </form>

            <a class="button" href="{{ route('admin.periods.create') }}">Tambah Periode</a>
        </div>

        <form id="bulk-action-periods" class="bulk-action-bar" method="post" action="{{ route('admin.periods.bulk-action') }}" hidden data-bulk-action-bar>
            @csrf
            <span class="bulk-action-count"><span data-bulk-selected-count>0</span> dipilih</span>
            <button
                class="button secondary bulk-delete-button"
                type="submit"
                name="action"
                value="delete"
                data-bulk-action-button
                data-danger-confirm
                data-danger-title="Hapus periode terpilih?"
                data-danger-message="Periode aktif atau periode yang sudah memiliki penugasan tidak akan dihapus."
                data-danger-message-template="Hapus {count} periode yang dicentang? Periode aktif atau yang sudah memiliki penugasan tidak akan dihapus."
                data-danger-confirm-label="Ya, Hapus"
            >Hapus</button>
        </form>

        <div class="table-wrap" data-bulk-container>
            <table>
                <thead>
                    <tr>
                        <th class="instrument-select-cell">
                            <input type="checkbox" aria-label="Pilih semua periode di halaman ini" data-bulk-select-all>
                        </th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Tahun</th>
                        <th>Tanggal Mulai</th>
                        <th>Batas Evaluasi Diri</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td class="instrument-select-cell">
                                <input type="checkbox" name="period_ids[]" value="{{ $period->id }}" form="bulk-action-periods" aria-label="Pilih periode {{ $period->nama }}" data-bulk-select>
                            </td>
                            <td>{{ $period->nama }}</td>
                            <td>{{ $jenisAuditOptions[$period->jenis_audit] ?? $period->jenis_audit }}</td>
                            <td>{{ $period->tahun_akademik }}</td>
                            <td>{{ $period->tanggal_mulai->format('d/m/Y') }}</td>
                            <td>{{ $period->batas_evaluasi_diri->format('d/m/Y') }}</td>
                            <td><span class="badge @if ($period->status !== 'aktif') off @endif">{{ $statusOptions[$period->status] ?? $period->status }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('admin.periods.show', $period)" icon="eye" label="Detail periode" tone="view" />
                                    @if ($period->canBeEdited())
                                        <x-action-icon :href="route('admin.periods.edit', $period)" icon="edit" label="Edit periode" tone="edit" />
                                    @endif
                                    <x-action-icon :action="route('admin.periods.duplicate', $period)" icon="copy" label="Duplikasi periode" tone="neutral" />
                                    @if ($period->canActivate())
                                        <x-action-icon :action="route('admin.periods.activate', $period)" method="patch" icon="check" label="Aktifkan periode" tone="success" />
                                    @endif
                                    @if ($period->canClose())
                                        <x-action-icon
                                            :action="route('admin.periods.close', $period)"
                                            method="patch"
                                            icon="x"
                                            label="Tutup periode"
                                            tone="warning"
                                            :confirm="true"
                                            confirm-title="Tutup periode audit?"
                                            confirm-message="Periode aktif akan ditutup. Pastikan proses audit sudah siap ditutup."
                                            confirm-label="Ya, Tutup"
                                        >
                                            <input type="hidden" name="force_close" value="1">
                                        </x-action-icon>
                                    @endif
                                    @if ($period->canArchive())
                                        <x-action-icon
                                            :action="route('admin.periods.archive', $period)"
                                            method="patch"
                                            icon="archive"
                                            label="Arsipkan periode"
                                            tone="warning"
                                            :confirm="true"
                                            confirm-title="Arsipkan periode audit?"
                                            confirm-message="Periode yang diarsipkan tidak bisa diedit lagi."
                                            confirm-label="Ya, Arsipkan"
                                        />
                                    @endif
                                    <x-action-icon
                                        :action="route('admin.periods.destroy', $period)"
                                        method="delete"
                                        icon="trash"
                                        label="Hapus periode"
                                        tone="danger"
                                        :confirm="true"
                                        confirm-title="Hapus periode?"
                                        confirm-message="Periode hanya akan terhapus jika tidak aktif dan belum memiliki penugasan."
                                        confirm-label="Ya, Hapus"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Belum ada data periode audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $periods->links() }}</div>
    </div>
@endsection
