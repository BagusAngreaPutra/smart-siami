@extends('layouts.app')

@section('title', $title.' - SMART SIAMI')
@section('page_title', $title)

@section('content')
    <div class="panel">
        <form class="toolbar" method="get" action="{{ route($scope.'.reports') }}">
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
