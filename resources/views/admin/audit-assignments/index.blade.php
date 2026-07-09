@extends('layouts.app')

@section('title', 'Penugasan Audit - SMART SIAMI')
@section('page_title', 'Penugasan Audit')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <form class="filters" method="get" action="{{ route('admin.assignments') }}">
                <div class="form-field">
                    <label for="audit_period_id">Periode</label>
                    <select id="audit_period_id" name="audit_period_id">
                        <option value="">Semua</option>
                        @foreach ($periodOptions as $period)
                            <option value="{{ $period->id }}" @selected((string) $selectedPeriodId === (string) $period->id)>{{ $period->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="auditor_id">Auditor</label>
                    <select id="auditor_id" name="auditor_id">
                        <option value="">Semua</option>
                        @foreach ($auditorOptions as $auditor)
                            <option value="{{ $auditor->id }}" @selected((string) request('auditor_id') === (string) $auditor->id)>{{ $auditor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="unit_id">Unit</label>
                    <select id="unit_id" name="unit_id">
                        <option value="">Semua</option>
                        @foreach ($unitOptions as $unit)
                            <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
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

                <button class="with-icon" type="submit"><x-ui-icon name="filter" /> Filter</button>
                <a class="button button-reset with-icon" href="{{ route('admin.assignments') }}"><x-ui-icon name="reset" /> Reset</a>
            </form>

            <a class="button with-icon" href="{{ route('admin.assignments.create', ['audit_period_id' => $activePeriod?->id]) }}"><x-ui-icon name="plus" /> Tambah Penugasan</a>
        </div>

        <form id="bulk-action-assignments" class="bulk-action-bar" method="post" action="{{ route('admin.assignments.bulk-action') }}" hidden data-bulk-action-bar>
            @csrf
            <span class="bulk-action-count"><span data-bulk-selected-count>0</span> dipilih</span>
            <button class="button secondary bulk-deactivate-button" type="submit" name="action" value="cancel" data-bulk-action-button>Batalkan</button>
            <button
                class="button secondary bulk-delete-button"
                type="submit"
                name="action"
                value="delete"
                data-bulk-action-button
                data-danger-confirm
                data-danger-title="Hapus penugasan terpilih?"
                data-danger-message="Penugasan yang sudah memiliki data audit tidak akan dihapus."
                data-danger-message-template="Hapus {count} penugasan yang dicentang? Penugasan yang sudah memiliki data audit tidak akan dihapus."
                data-danger-confirm-label="Ya, Hapus"
            >Hapus</button>
        </form>

        <div class="table-wrap" data-bulk-container>
            <table>
                <thead>
                    <tr>
                        <th class="instrument-select-cell">
                            <input type="checkbox" aria-label="Pilih semua penugasan di halaman ini" data-bulk-select-all>
                        </th>
                        <th>Unit</th>
                        <th>Lead Auditor</th>
                        <th>Periode</th>
                        <th>Tanggal Visitasi</th>
                        <th>Progres Evaluasi Diri</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td class="instrument-select-cell">
                                <input type="checkbox" name="assignment_ids[]" value="{{ $assignment->id }}" form="bulk-action-assignments" aria-label="Pilih penugasan {{ $assignment->unit->kode }}" data-bulk-select>
                            </td>
                            <td>{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</td>
                            <td>{{ $assignment->leadAuditor->name }}</td>
                            <td>{{ $assignment->auditPeriod->nama }}</td>
                            <td>{{ $assignment->jadwal_visitasi?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $assignment->progressEvaluasiDiri() }}%</td>
                            <td><span class="badge @if ($assignment->status !== 'aktif') off @endif">{{ $statusOptions[$assignment->status] ?? $assignment->status }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('admin.assignments.show', $assignment)" icon="eye" label="Detail penugasan" tone="view" />
                                    @if ($assignment->status === 'aktif')
                                        <x-action-icon :href="route('admin.assignments.edit', $assignment)" icon="edit" label="Ubah auditor" tone="edit" />
                                        <x-action-icon :href="route('admin.assignments.print-letter', $assignment)" icon="printer" label="Cetak surat tugas" tone="neutral" target="_blank" />
                                        <x-action-icon :action="route('admin.assignments.notify', $assignment)" icon="bell" label="Kirim notifikasi" tone="success" />
                                        <x-action-icon
                                            :action="route('admin.assignments.cancel', $assignment)"
                                            method="patch"
                                            icon="x"
                                            label="Batalkan penugasan"
                                            tone="warning"
                                            :confirm="true"
                                            confirm-title="Batalkan penugasan?"
                                            confirm-message="Data audit yang sudah ada tidak akan dihapus, tetapi penugasan akan ditandai dibatalkan."
                                            confirm-label="Ya, Batalkan"
                                        />
                                    @endif
                                    <x-action-icon
                                        :action="route('admin.assignments.destroy', $assignment)"
                                        method="delete"
                                        icon="trash"
                                        label="Hapus penugasan"
                                        tone="danger"
                                        :confirm="true"
                                        confirm-title="Hapus penugasan?"
                                        confirm-message="Penugasan hanya akan terhapus jika belum memiliki evaluasi diri, desk evaluation, visitasi, klarifikasi, atau temuan. Jika sudah dipakai, sistem akan menolak dan menyarankan batalkan."
                                        confirm-label="Ya, Hapus"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Belum ada data penugasan audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $assignments->links() }}</div>
    </div>
@endsection
