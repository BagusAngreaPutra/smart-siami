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

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
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
                            <td>{{ $period->nama }}</td>
                            <td>{{ $jenisAuditOptions[$period->jenis_audit] ?? $period->jenis_audit }}</td>
                            <td>{{ $period->tahun_akademik }}</td>
                            <td>{{ $period->tanggal_mulai->format('d/m/Y') }}</td>
                            <td>{{ $period->batas_evaluasi_diri->format('d/m/Y') }}</td>
                            <td><span class="badge @if ($period->status !== 'aktif') off @endif">{{ $statusOptions[$period->status] ?? $period->status }}</span></td>
                            <td>
                                <div class="actions">
                                    <a class="link-button" href="{{ route('admin.periods.show', $period) }}">Detail</a>
                                    @if ($period->canBeEdited())
                                        <a class="link-button" href="{{ route('admin.periods.edit', $period) }}">Edit</a>
                                    @endif
                                    <form class="inline-form" method="post" action="{{ route('admin.periods.duplicate', $period) }}">
                                        @csrf
                                        <button class="link-button" type="submit">Duplikasi</button>
                                    </form>
                                    @if ($period->canActivate())
                                        <form class="inline-form" method="post" action="{{ route('admin.periods.activate', $period) }}">
                                            @csrf
                                            @method('patch')
                                            <button class="link-button" type="submit">Aktifkan</button>
                                        </form>
                                    @endif
                                    @if ($period->canClose())
                                        <form class="inline-form" method="post" action="{{ route('admin.periods.close', $period) }}" onsubmit="return confirm('Tutup periode audit ini?');">
                                            @csrf
                                            @method('patch')
                                            <input type="hidden" name="force_close" value="1">
                                            <button class="link-button danger-link" type="submit">Tutup</button>
                                        </form>
                                    @endif
                                    @if ($period->canArchive())
                                        <form class="inline-form" method="post" action="{{ route('admin.periods.archive', $period) }}" onsubmit="return confirm('Arsipkan periode audit ini?');">
                                            @csrf
                                            @method('patch')
                                            <button class="link-button danger-link" type="submit">Arsipkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada data periode audit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $periods->links() }}</div>
    </div>
@endsection
