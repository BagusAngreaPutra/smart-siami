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

                    <button type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('admin.users', ['tab' => 'users']) }}">Reset</a>
                </form>

                <div class="actions">
                    <a class="button secondary" href="{{ route('admin.managed-users.template') }}">Template</a>
                    <a class="button secondary" href="{{ route('admin.managed-users.export', request()->query()) }}">Ekspor Excel</a>
                    <a class="button" href="{{ route('admin.managed-users.create') }}">Tambah Pengguna</a>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
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
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->nip_nidn ?? '-' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->label() }}</td>
                                <td>{{ $user->unit?->kode ? $user->unit->kode.' - '.$user->unit->nama : '-' }}</td>
                                <td><span class="badge @if (! $user->is_active) off @endif">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>
                                    <div class="actions">
                                        <a class="link-button" href="{{ route('admin.managed-users.edit', $user) }}">Edit</a>
                                        <a class="link-button" href="{{ route('admin.managed-users.password.edit', $user) }}">Reset Password</a>
                                        @if (! $user->is(auth()->user()))
                                            <form method="post" action="{{ route('admin.managed-users.toggle-active', $user) }}">
                                                @csrf
                                                @method('patch')
                                                <button class="link-button danger-link" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Belum ada data pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $users->links() }}</div>

            <form class="import-box" method="post" action="{{ route('admin.managed-users.import') }}" enctype="multipart/form-data">
                @csrf
                <label for="user_file">Import pengguna</label>
                <input id="user_file" type="file" name="file" accept=".xls,.xml,.csv" required>
                <button type="submit">Import Excel</button>
                @error('file')
                    <div class="error">{{ $message }}</div>
                @enderror
            </form>
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

                    <button type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('admin.users', ['tab' => 'units']) }}">Reset</a>
                </form>

                <div class="actions">
                    <a class="button secondary" href="{{ route('admin.units.template') }}">Template</a>
                    <a class="button secondary" href="{{ route('admin.units.export', request()->query()) }}">Ekspor Excel</a>
                    <a class="button" href="{{ route('admin.units.create') }}">Tambah Unit</a>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
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
                                <td>{{ $unit->kode }}</td>
                                <td>{{ $unit->nama }}</td>
                                <td>{{ $jenisUnitOptions[$unit->jenis_unit] ?? $unit->jenis_unit }}</td>
                                <td>{{ $unit->nama_pimpinan ?? '-' }}</td>
                                <td>{{ $unit->email ?? '-' }}</td>
                                <td><span class="badge @if (! $unit->is_active) off @endif">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>
                                    <div class="actions">
                                        <a class="link-button" href="{{ route('admin.units.edit', $unit) }}">Edit</a>
                                        <form method="post" action="{{ route('admin.units.toggle-active', $unit) }}" onsubmit="return confirm('Ubah status aktif unit ini? Jika unit memiliki penugasan aktif, pastikan sudah dikonfirmasi.');">
                                            @csrf
                                            @method('patch')
                                            <input type="hidden" name="confirm_active_assignments" value="1">
                                            <button class="link-button danger-link" type="submit">{{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Belum ada data unit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $units->links() }}</div>

            <form class="import-box" method="post" action="{{ route('admin.units.import') }}" enctype="multipart/form-data">
                @csrf
                <label for="unit_file">Import unit</label>
                <input id="unit_file" type="file" name="file" accept=".xls,.xml,.csv" required>
                <button type="submit">Import Excel</button>
                @error('file')
                    <div class="error">{{ $message }}</div>
                @enderror
            </form>
        </div>
    @endif
@endsection
