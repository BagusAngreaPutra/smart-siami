@extends('layouts.app')

@section('title', 'Unit dan Pengguna - SMART SIAMI')
@section('page_title', 'Unit dan Pengguna')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    @if (session('import_errors'))
        <div class="warning">
            <strong>Beberapa baris gagal diimpor.</strong>
            <ul>
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tabs">
        <a class="tab-link @if ($activeTab === 'units') active @endif" href="{{ route('admin.users', ['tab' => 'units']) }}">Data Unit</a>
        <a class="tab-link @if ($activeTab === 'users') active @endif" href="{{ route('admin.users', ['tab' => 'users']) }}">Data Pengguna</a>
    </div>

    @if ($activeTab === 'users')
        <div class="panel">
            <div class="toolbar">
                <form class="filters" method="get" action="{{ route('admin.users') }}">
                    <input type="hidden" name="tab" value="users">

                    <div class="form-field">
                        <label for="user_role">Peran</label>
                        <select id="user_role" name="user_role">
                            <option value="">Semua</option>
                            @foreach ($roleOptions as $role)
                                <option value="{{ $role->value }}" @selected(request('user_role') === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="user_status">Status</label>
                        <select id="user_status" name="user_status">
                            <option value="">Semua</option>
                            <option value="aktif" @selected(request('user_status') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(request('user_status') === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="user_unit_id">Unit</label>
                        <select id="user_unit_id" name="user_unit_id">
                            <option value="">Semua</option>
                            @foreach ($unitOptions as $unit)
                                <option value="{{ $unit->id }}" @selected((string) request('user_unit_id') === (string) $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="button-icon-only" type="submit" title="Filter" aria-label="Filter"><x-ui-icon name="filter" /></button>
                    <a class="button button-reset button-icon-only" href="{{ route('admin.users', ['tab' => 'users']) }}" title="Reset" aria-label="Reset"><x-ui-icon name="reset" /></a>
                </form>

                <div class="actions">
                    <a class="button button-template with-icon" href="{{ route('admin.managed-users.template') }}"><x-ui-icon name="template" /> Template</a>
                    <div class="excel-action-group" aria-label="Import dan export pengguna">
                        <x-excel-action mode="import" label="Import Excel" data-import-modal-open="users-import" />
                        <x-excel-action :href="route('admin.managed-users.export', request()->query())" mode="export" label="Ekspor Excel" />
                    </div>
                    <a class="button button-add with-icon" href="{{ route('admin.managed-users.create') }}"><x-ui-icon name="plus" /> Tambah Pengguna</a>
                </div>
            </div>

            <form id="bulk-action-users" class="bulk-action-bar" method="post" action="{{ route('admin.managed-users.bulk-action') }}" hidden data-bulk-action-bar>
                @csrf
                <span class="bulk-action-count"><span data-bulk-selected-count>0</span> dipilih</span>
                <button class="button secondary bulk-deactivate-button" type="submit" name="action" value="deactivate" data-bulk-action-button>Nonaktifkan</button>
                <button
                    class="button secondary bulk-delete-button"
                    type="submit"
                    name="action"
                    value="delete"
                    data-bulk-action-button
                    data-danger-confirm
                    data-danger-title="Hapus pengguna terpilih?"
                    data-danger-message="Pengguna yang sudah memiliki jejak aktivitas audit tidak akan dihapus."
                    data-danger-message-template="Hapus {count} pengguna yang dicentang? Pengguna yang sudah memiliki jejak aktivitas audit tidak akan dihapus."
                    data-danger-confirm-label="Ya, Hapus"
                >Hapus</button>
            </form>

            <div class="table-wrap" data-bulk-container>
                <table>
                    <thead>
                        <tr>
                            <th class="instrument-select-cell">
                                <input type="checkbox" aria-label="Pilih semua pengguna di halaman ini" data-bulk-select-all>
                            </th>
                            <th>Nama</th>
                            <th>NIP/NIDN</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="instrument-select-cell">
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" form="bulk-action-users" aria-label="Pilih pengguna {{ $user->name }}" data-bulk-select @disabled($user->is(auth()->user()))>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->nip_nidn ?? '-' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->label() }}</td>
                                <td>{{ $user->unit?->kode ? $user->unit->kode.' - '.$user->unit->nama : '-' }}</td>
                                <td><span class="badge @if (! $user->is_active) off @endif">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>
                                    <div class="table-actions">
                                        <x-action-icon :href="route('admin.managed-users.edit', $user)" icon="edit" label="Edit pengguna" tone="edit" />
                                        <x-action-icon :href="route('admin.managed-users.password.edit', $user)" icon="key" label="Reset password" tone="warning" />
                                        @if (! $user->is(auth()->user()))
                                            <x-action-icon
                                                :action="route('admin.managed-users.toggle-active', $user)"
                                                method="patch"
                                                icon="power"
                                                :label="$user->is_active ? 'Nonaktifkan pengguna' : 'Aktifkan pengguna'"
                                                :tone="$user->is_active ? 'warning' : 'success'"
                                            />
                                            <x-action-icon
                                                :action="route('admin.managed-users.destroy', $user)"
                                                method="delete"
                                                icon="trash"
                                                label="Hapus pengguna"
                                                tone="danger"
                                                :confirm="true"
                                                confirm-title="Hapus pengguna?"
                                                confirm-message="Pengguna hanya akan terhapus jika belum memiliki jejak aktivitas audit. Jika sudah dipakai, sistem akan menolak dan menyarankan nonaktifkan."
                                                confirm-label="Ya, Hapus"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Belum ada data pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $users->links() }}</div>

            <x-import-modal
                id="users-import"
                title="Import Data Pengguna"
                description="Upload file pengguna sesuai template. Data valid akan ditambahkan atau diperbarui."
                :action="route('admin.managed-users.import')"
                input-id="user_file"
                accept=".xlsx,.xls,.xml,.csv"
            />
        </div>
    @else
        <div class="panel">
            <div class="toolbar">
                <form class="filters" method="get" action="{{ route('admin.users') }}">
                    <input type="hidden" name="tab" value="units">

                    <div class="form-field">
                        <label for="unit_jenis_unit">Jenis Unit</label>
                        <select id="unit_jenis_unit" name="unit_jenis_unit">
                            <option value="">Semua</option>
                            @foreach ($jenisUnitOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('unit_jenis_unit') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="unit_status">Status</label>
                        <select id="unit_status" name="unit_status">
                            <option value="">Semua</option>
                            <option value="aktif" @selected(request('unit_status') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(request('unit_status') === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                    <button class="button-icon-only" type="submit" title="Filter" aria-label="Filter"><x-ui-icon name="filter" /></button>
                    <a class="button button-reset button-icon-only" href="{{ route('admin.users', ['tab' => 'units']) }}" title="Reset" aria-label="Reset"><x-ui-icon name="reset" /></a>
                </form>

                <div class="actions">
                    <a class="button button-template with-icon" href="{{ route('admin.units.template') }}"><x-ui-icon name="template" /> Template</a>
                    <div class="excel-action-group" aria-label="Import dan export unit">
                        <x-excel-action mode="import" label="Import Excel" data-import-modal-open="units-import" />
                        <x-excel-action :href="route('admin.units.export', request()->query())" mode="export" label="Ekspor Excel" />
                    </div>
                    <a class="button button-add with-icon" href="{{ route('admin.units.create') }}"><x-ui-icon name="plus" /> Tambah Unit</a>
                </div>
            </div>

            <form id="bulk-action-units" class="bulk-action-bar" method="post" action="{{ route('admin.units.bulk-action') }}" hidden data-bulk-action-bar>
                @csrf
                <span class="bulk-action-count"><span data-bulk-selected-count>0</span> dipilih</span>
                <button class="button secondary bulk-deactivate-button" type="submit" name="action" value="deactivate" data-bulk-action-button>Nonaktifkan</button>
                <button
                    class="button secondary bulk-delete-button"
                    type="submit"
                    name="action"
                    value="delete"
                    data-bulk-action-button
                    data-danger-confirm
                    data-danger-title="Hapus unit terpilih?"
                    data-danger-message="Unit yang sudah terhubung dengan pengguna atau penugasan tidak akan dihapus."
                    data-danger-message-template="Hapus {count} unit yang dicentang? Unit yang sudah terhubung dengan pengguna atau penugasan tidak akan dihapus."
                    data-danger-confirm-label="Ya, Hapus"
                >Hapus</button>
            </form>

            <div class="table-wrap" data-bulk-container>
                <table>
                    <thead>
                        <tr>
                            <th class="instrument-select-cell">
                                <input type="checkbox" aria-label="Pilih semua unit di halaman ini" data-bulk-select-all>
                            </th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Jenis Unit</th>
                            <th>Nama Pimpinan</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($units as $unit)
                            <tr>
                                <td class="instrument-select-cell">
                                    <input type="checkbox" name="unit_ids[]" value="{{ $unit->id }}" form="bulk-action-units" aria-label="Pilih unit {{ $unit->kode }}" data-bulk-select>
                                </td>
                                <td>{{ $unit->kode }}</td>
                                <td>{{ $unit->nama }}</td>
                                <td>{{ $jenisUnitOptions[$unit->jenis_unit] ?? $unit->jenis_unit }}</td>
                                <td>{{ $unit->nama_pimpinan ?? '-' }}</td>
                                <td>{{ $unit->email ?? '-' }}</td>
                                <td><span class="badge @if (! $unit->is_active) off @endif">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>
                                    <div class="table-actions">
                                        <x-action-icon :href="route('admin.units.edit', $unit)" icon="edit" label="Edit unit" tone="edit" />
                                        <x-action-icon
                                            :action="route('admin.units.toggle-active', $unit)"
                                            method="patch"
                                            icon="power"
                                            :label="$unit->is_active ? 'Nonaktifkan unit' : 'Aktifkan unit'"
                                            :tone="$unit->is_active ? 'warning' : 'success'"
                                            :confirm="true"
                                            confirm-title="Ubah status unit?"
                                            confirm-message="Jika unit memiliki penugasan aktif, pastikan perubahan ini sudah dikonfirmasi."
                                            confirm-label="Ya, Lanjutkan"
                                        >
                                            <input type="hidden" name="confirm_active_assignments" value="1">
                                        </x-action-icon>
                                        <x-action-icon
                                            :action="route('admin.units.destroy', $unit)"
                                            method="delete"
                                            icon="trash"
                                            label="Hapus unit"
                                            tone="danger"
                                            :confirm="true"
                                            confirm-title="Hapus unit?"
                                            confirm-message="Unit hanya akan terhapus jika belum terhubung dengan pengguna atau penugasan audit. Jika sudah dipakai, sistem akan menolak dan menyarankan nonaktifkan."
                                            confirm-label="Ya, Hapus"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Belum ada data unit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $units->links() }}</div>

            <x-import-modal
                id="units-import"
                title="Import Data Unit"
                description="Upload file unit sesuai template. Sistem akan membaca kode, nama, jenis unit, dan kontak unit."
                :action="route('admin.units.import')"
                input-id="unit_file"
                accept=".xlsx,.xls,.xml,.csv"
            />
        </div>
    @endif
@endsection
