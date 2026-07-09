@extends('layouts.app')

@section('title', 'Temuan - SMART SIAMI')
@section('page_title', 'Temuan')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <div class="toolbar">
            <form class="filters" method="get">
                <div class="form-field">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('kategori') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="unit_id">Unit</label>
                    <select id="unit_id" name="unit_id">
                        <option value="">Semua Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="audit_period_id">Periode</label>
                    <select id="audit_period_id" name="audit_period_id">
                        <option value="">Semua Periode</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected((string) request('audit_period_id') === (string) $period->id)>{{ $period->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit">Filter</button>
                <a class="button secondary" href="{{ route('auditor.findings') }}">Reset</a>
            </form>

            <div class="actions">
                <a class="button secondary" href="{{ route('auditor.findings.print', request()->query()) }}" target="_blank">Cetak PDF</a>
                <a class="button" href="{{ route('auditor.findings.create') }}">Tambah Temuan</a>
            </div>
        </div>

        <div class="section-block">
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Kanban Temuan</h3>
                    <p class="muted">Papan visual untuk membaca beban temuan per status. Perubahan status tetap dilakukan dari proses detail, tindak lanjut, dan verifikasi.</p>
                </div>
                <div class="view-toggle">
                    <a class="quick-chip active" href="#kanban-temuan">Kanban</a>
                    <a class="quick-chip" href="#tabel-temuan">Tabel</a>
                </div>
            </div>
            <div id="kanban-temuan" class="kanban-board">
                @foreach (['draft', 'aktif', 'dalam_tindak_lanjut', 'menunggu_verifikasi', 'ditutup', 'terlambat'] as $status)
                    @php($items = $kanbanFindings->get($status, collect()))
                    <section class="kanban-column" data-kanban-column="{{ $status }}">
                        <h3>
                            <span>{{ $statusOptions[$status] ?? $status }}</span>
                            <span class="badge neutral">{{ $items->count() }}</span>
                        </h3>
                        @forelse ($items as $finding)
                            @php($deadline = \App\Support\AuditVisuals::deadlineMeta($finding->target_penyelesaian))
                            <a class="kanban-card" draggable="true" data-kanban-card href="{{ route('auditor.findings.show', $finding) }}">
                                <div class="kanban-meta">
                                    <span class="badge @if ($finding->kategori === 'mayor') danger @elseif ($finding->kategori === 'minor') warning @else neutral @endif">{{ $kategoriOptions[$finding->kategori] }}</span>
                                    <span class="badge {{ $deadline['tone'] }}">{{ $deadline['label'] }}</span>
                                </div>
                                <strong>{{ $finding->nomor_temuan ?? 'Draft #'.$finding->id }}</strong>
                                <span class="muted">{{ $finding->assignment->unit->kode }} · {{ $finding->standard->kode }}</span>
                                <div class="kanban-meta">
                                    <x-visual.avatar :name="$finding->assignment->leadAuditor->name" />
                                    <span class="muted">{{ $finding->assignment->leadAuditor->name }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="empty-compact">Kosong</div>
                        @endforelse
                    </section>
                @endforeach
            </div>
        </div>

        <div id="tabel-temuan" class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nomor Temuan</th>
                        <th>Unit</th>
                        <th>Kategori</th>
                        <th>Standar</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($findings as $finding)
                        <tr>
                            <td>{{ $finding->nomor_temuan ?? 'Draft #' . $finding->id }}</td>
                            <td>{{ $finding->assignment->unit->kode }} - {{ $finding->assignment->unit->nama }}</td>
                            <td>{{ $kategoriOptions[$finding->kategori] }}</td>
                            <td>{{ $finding->standard->kode }} - {{ $finding->standard->nama }}</td>
                            <td>{{ $finding->target_penyelesaian->format('d/m/Y') }}</td>
                            <td><span class="badge @if (in_array($finding->status, ['draft', 'dibatalkan'], true)) off @endif">{{ $statusOptions[$finding->status] }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('auditor.findings.show', $finding)" icon="eye" label="Detail temuan" tone="view" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada temuan audit untuk penugasan Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $findings->links() }}</div>
    </div>
@endsection
