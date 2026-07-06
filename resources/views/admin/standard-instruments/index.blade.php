@extends('layouts.app')

@section('title', 'Standar dan Instrumen - SMART SIAMI')
@section('page_title', 'Standar dan Instrumen')

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
        <a class="tab-link @if ($activeTab === 'standards') active @endif" href="{{ route('admin.standards', ['tab' => 'standards']) }}">Standar</a>
        <a class="tab-link @if ($activeTab === 'instruments') active @endif" href="{{ route('admin.standards', ['tab' => 'instruments']) }}">Instrumen</a>
    </div>

    @if ($activeTab === 'instruments')
        <div class="panel">
            <div class="toolbar">
                <form class="filters" method="get" action="{{ route('admin.standards') }}">
                    <input type="hidden" name="tab" value="instruments">

                    <div class="form-field">
                        <label for="instrument_standard_id">Standar</label>
                        <select id="instrument_standard_id" name="instrument_standard_id">
                            <option value="">Semua</option>
                            @foreach ($standardOptions as $standard)
                                <option value="{{ $standard->id }}" @selected((string) request('instrument_standard_id') === (string) $standard->id)>{{ $standard->kode }} - {{ $standard->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="instrument_status">Status</label>
                        <select id="instrument_status" name="instrument_status">
                            <option value="">Semua</option>
                            <option value="aktif" @selected(request('instrument_status') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(request('instrument_status') === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                    <button type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('admin.standards', ['tab' => 'instruments']) }}">Reset</a>
                </form>

                <div class="actions">
                    <a class="button secondary" href="{{ route('admin.instruments.template') }}">Template</a>
                    <a class="button secondary" href="{{ route('admin.instruments.export', request()->query()) }}">Ekspor Excel</a>
                    <a class="button" href="{{ route('admin.instruments.create', ['standard_id' => request('instrument_standard_id')]) }}">Tambah Instrumen</a>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Standar</th>
                            <th>Pertanyaan</th>
                            <th>Jenis Jawaban</th>
                            <th>Bobot</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($instruments as $instrument)
                            <tr>
                                <td>{{ $instrument->kode }}</td>
                                <td>{{ $instrument->standard?->kode }} - {{ $instrument->standard?->nama }}</td>
                                <td>{{ $truncate($instrument->pertanyaan) }}</td>
                                <td>{{ $jenisJawabanOptions[$instrument->jenis_jawaban] ?? $instrument->jenis_jawaban }}</td>
                                <td>{{ $instrument->bobot ?? '-' }}</td>
                                <td><span class="badge @if (! $instrument->is_active) off @endif">{{ $instrument->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>
                                    <div class="actions">
                                        <a class="link-button" href="{{ route('admin.instruments.edit', $instrument) }}">Edit</a>
                                        <form class="inline-form" method="post" action="{{ route('admin.instruments.duplicate', $instrument) }}">
                                            @csrf
                                            <button class="link-button" type="submit">Salin</button>
                                        </form>
                                        <form class="inline-form" method="post" action="{{ route('admin.instruments.toggle-active', $instrument) }}">
                                            @csrf
                                            @method('patch')
                                            <button class="link-button danger-link" type="submit">{{ $instrument->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Belum ada data instrumen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $instruments->links() }}</div>

            <form class="import-box" method="post" action="{{ route('admin.instruments.import') }}" enctype="multipart/form-data">
                @csrf
                <label for="instrument_file">Import instrumen</label>
                <input id="instrument_file" type="file" name="file" accept=".xls,.xml,.csv" required>
                <button type="submit">Import Excel</button>
                @error('file')
                    <div class="error">{{ $message }}</div>
                @enderror
            </form>
        </div>
    @else
        <div class="panel">
            <div class="toolbar">
                <form class="filters" method="get" action="{{ route('admin.standards') }}">
                    <input type="hidden" name="tab" value="standards">

                    <div class="form-field">
                        <label for="standard_status">Status</label>
                        <select id="standard_status" name="standard_status">
                            <option value="">Semua</option>
                            <option value="aktif" @selected(request('standard_status') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(request('standard_status') === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                    <button type="submit">Filter</button>
                    <a class="button secondary" href="{{ route('admin.standards', ['tab' => 'standards']) }}">Reset</a>
                </form>

                <a class="button" href="{{ route('admin.quality-standards.create') }}">Tambah Standar</a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Urutan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($standards as $standard)
                            <tr>
                                <td>{{ $standard->kode }}</td>
                                <td>{{ $standard->nama }}</td>
                                <td>{{ $standard->target ? $truncate($standard->target) : '-' }}</td>
                                <td><span class="badge @if (! $standard->is_active) off @endif">{{ $standard->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>
                                    <div class="actions">
                                        <span>{{ $standard->urutan }}</span>
                                        <form class="inline-form" method="post" action="{{ route('admin.quality-standards.move', [$standard, 'up']) }}">
                                            @csrf
                                            @method('patch')
                                            <button class="link-button" type="submit">Naik</button>
                                        </form>
                                        <form class="inline-form" method="post" action="{{ route('admin.quality-standards.move', [$standard, 'down']) }}">
                                            @csrf
                                            @method('patch')
                                            <button class="link-button" type="submit">Turun</button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="link-button" href="{{ route('admin.quality-standards.edit', $standard) }}">Edit</a>
                                        <form class="inline-form" method="post" action="{{ route('admin.quality-standards.toggle-active', $standard) }}">
                                            @csrf
                                            @method('patch')
                                            <button class="link-button danger-link" type="submit">{{ $standard->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                        <a class="link-button" href="{{ route('admin.standards', ['tab' => 'instruments', 'instrument_standard_id' => $standard->id]) }}">Atur Instrumen</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Belum ada data standar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $standards->links() }}</div>
        </div>
    @endif
@endsection
