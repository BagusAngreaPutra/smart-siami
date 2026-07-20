@extends('layouts.app')

@section('title', $title.' - SMART SIAMI')
@section('page_title', $title)

@section('content')
    @if ($scope === 'auditee')
        <section class="auditee-page-stats" aria-label="Ringkasan laporan unit">
            <x-auditee.metric-card label="Penugasan Tersedia" :value="$assignments->count()" caption="Dapat dilaporkan" tone="teal" icon="building" />
            <x-auditee.metric-card label="Jenis Laporan" :value="collect($reportTypes)->reject(fn ($report) => $report['institution'] ?? false)->count()" caption="Format keluaran" tone="blue" icon="file" />
            <x-auditee.metric-card label="Periode Tersedia" :value="$periodOptions->count()" caption="Riwayat audit" tone="teal" icon="calendar" />
            <x-auditee.metric-card label="Akses Unit" value="1 Unit" caption="Sesuai akun" tone="orange" icon="building" />
        </section>
    @elseif ($scope === 'auditor')
        <section class="auditor-page-stats" aria-label="Ringkasan laporan auditor">
            <article class="auditor-stat-card tone-blue">
                <span class="auditor-stat-icon"><svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path></svg></span>
                <div><span>Penugasan Tersedia</span><strong>{{ $assignments->count() }}</strong><small>Sesuai akses auditor</small></div>
            </article>
            <article class="auditor-stat-card tone-violet">
                <span class="auditor-stat-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h6"></path></svg></span>
                <div><span>Jenis Laporan</span><strong>{{ collect($reportTypes)->reject(fn ($report) => $report['institution'] ?? false)->count() }}</strong><small>PDF dan Excel</small></div>
            </article>
            <article class="auditor-stat-card tone-teal">
                <span class="auditor-stat-icon"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg></span>
                <div><span>Periode Audit</span><strong>{{ $periodOptions->count() }}</strong><small>Riwayat yang tersedia</small></div>
            </article>
            <article class="auditor-stat-card tone-orange">
                <span class="auditor-stat-icon"><svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 8h1M14 8h1M9 12h1M14 12h1"></path></svg></span>
                <div><span>Unit Ditugaskan</span><strong>{{ $assignments->pluck('unit_id')->unique()->count() }}</strong><small>Cakupan laporan</small></div>
            </article>
        </section>
    @endif

    <div class="panel @if ($scope === 'auditee') auditee-list-surface @endif">
        <form class="toolbar @if ($scope === 'auditee') auditee-command-bar @endif" method="get" action="{{ route($scope.'.reports') }}">
            <div class="filters">
                <div class="form-field">
                    <label for="audit_period_id">Periode</label>
                    <select id="audit_period_id" name="audit_period_id">
                        <option value="">Semua Periode</option>
                        @foreach ($periodOptions as $period)
                            <option value="{{ $period->id }}" @selected(request('audit_period_id') == $period->id)>{{ $period->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($scope !== 'auditee')
                    <div class="form-field">
                        <label for="unit_id">Unit</label>
                        <select id="unit_id" name="unit_id">
                            <option value="">Semua Unit</option>
                            @foreach ($unitOptions as $unit)
                                <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button class="with-icon" type="submit"><x-ui-icon name="filter" /> Terapkan</button>
            </div>
        </form>

        @if ($scope === 'admin')
            <div class="panel" style="margin-bottom:18px">
                <div class="toolbar">
                    <div>
                        <h3 class="panel-title">Rekap Audit Institusi</h3>
                        <p class="muted">Ringkasan semua unit untuk periode yang dipilih.</p>
                    </div>
                    <div class="actions">
                        <a class="button secondary with-icon report-action report-preview" target="_blank" href="{{ route('admin.reports.institution.preview', request()->query()) }}"><x-ui-icon name="eye" /> Pratinjau</a>
                        <a class="button secondary with-icon report-action report-pdf" href="{{ route('admin.reports.institution.download', request()->query()) }}"><x-ui-icon name="pdf" /> Unduh PDF</a>
                        <a class="button secondary with-icon report-action report-excel" href="{{ route('admin.reports.institution.excel', request()->query()) }}"><x-ui-icon name="excel" /> Unduh Excel</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Periode</th>
                        <th>Lead Auditor</th>
                        <th>Jenis Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}</td>
                            <td>{{ $assignment->auditPeriod->nama }}</td>
                            <td>{{ $assignment->leadAuditor->name }}</td>
                            <td>
                                <div class="dashboard-list">
                                    @foreach ($reportTypes as $key => $report)
                                        @continue($report['institution'] ?? false)
                                        <div class="list-item">
                                            <div class="actions" style="justify-content:space-between">
                                                <strong>{{ $report['label'] }}</strong>
                                                <div class="actions">
                                                    <a class="link-button with-icon report-action report-preview" target="_blank" href="{{ route($scope.'.reports.preview', array_merge([$key, $assignment], request()->query())) }}"><x-ui-icon name="eye" /> Pratinjau</a>
                                                    <a class="link-button with-icon report-action report-pdf" href="{{ route($scope.'.reports.download', array_merge([$key, $assignment], request()->query())) }}"><x-ui-icon name="pdf" /> Unduh PDF</a>
                                                    @if ($report['excel'])
                                                        <a class="link-button with-icon report-action report-excel" href="{{ route($scope.'.reports.excel', array_merge([$key, $assignment], request()->query())) }}"><x-ui-icon name="excel" /> Unduh Excel</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Tidak ada penugasan audit yang sesuai dengan akses dan filter Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
