@extends('layouts.app')

@section('title', 'Monitoring - SMART SIAMI')
@section('page_title', 'Monitoring')

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    <div class="panel">
        <form class="toolbar" method="get" action="{{ route('admin.monitoring') }}">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="filters">
                <div class="form-field">
                    <label for="audit_period_id">Periode Audit</label>
                    <select id="audit_period_id" name="audit_period_id">
                        @foreach ($periodOptions as $period)
                            <option value="{{ $period->id }}" @selected((string) $selectedPeriodId === (string) $period->id)>{{ $period->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="unit_id">Unit</label>
                    <select id="unit_id" name="unit_id">
                        <option value="">Semua Unit</option>
                        @foreach ($unitOptions as $unit)
                            <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="auditor_id">Auditor</label>
                    <select id="auditor_id" name="auditor_id">
                        <option value="">Semua Auditor</option>
                        @foreach ($auditorOptions as $auditor)
                            <option value="{{ $auditor->id }}" @selected(request('auditor_id') == $auditor->id)>{{ $auditor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="standard_id">Standar</label>
                    <select id="standard_id" name="standard_id">
                        <option value="">Semua Standar</option>
                        @foreach ($standardOptions as $standard)
                            <option value="{{ $standard->id }}" @selected(request('standard_id') == $standard->id)>{{ $standard->kode }} - {{ $standard->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="button-icon-only" type="submit" title="Terapkan filter" aria-label="Terapkan filter"><x-ui-icon name="filter" /></button>
            </div>
        </form>

        <nav class="tabs" aria-label="Tab monitoring">
            <a class="tab-link @if ($activeTab === 'progress') active @endif" href="{{ route('admin.monitoring', [...request()->query(), 'tab' => 'progress']) }}">Progres Unit</a>
            <a class="tab-link @if ($activeTab === 'visual') active @endif" href="{{ route('admin.monitoring', [...request()->query(), 'tab' => 'visual']) }}">Visual Audit</a>
            <a class="tab-link @if ($activeTab === 'self-evaluation') active @endif" href="{{ route('admin.monitoring', [...request()->query(), 'tab' => 'self-evaluation']) }}">Evaluasi Diri Belum Selesai</a>
            <a class="tab-link @if ($activeTab === 'late-findings') active @endif" href="{{ route('admin.monitoring', [...request()->query(), 'tab' => 'late-findings']) }}">Temuan Terlambat</a>
            <a class="tab-link @if ($activeTab === 'pending-followups') active @endif" href="{{ route('admin.monitoring', [...request()->query(), 'tab' => 'pending-followups']) }}">Tindak Lanjut Menunggu Verifikasi</a>
        </nav>

        @if ($activeTab === 'progress')
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Progres Unit</h3>
                    <p class="muted">Baris merah menandakan evaluasi diri atau temuan yang melewati batas waktu.</p>
                </div>
                <x-excel-action :href="route('admin.monitoring.export.progress', request()->query())" mode="export" label="Ekspor Excel" />
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Lead Auditor</th>
                            <th>Status Evaluasi Diri</th>
                            <th>Status Desk Evaluation</th>
                            <th>Jadwal Visitasi</th>
                            <th>Jumlah Temuan</th>
                            <th>Status Tindak Lanjut</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($progressRows as $row)
                            <tr @if ($row['is_late']) style="background:#fff1f0" @endif>
                                <td>{{ $row['unit'] }}</td>
                                <td>{{ $row['lead_auditor'] }}</td>
                                <td><x-status-badge :tone="$row['self_evaluation_badge']">{{ $row['self_evaluation_status'] }}</x-status-badge></td>
                                <td><x-status-badge :tone="$row['desk_evaluation_badge']">{{ $row['desk_evaluation_status'] }}</x-status-badge></td>
                                <td>{{ $row['visit_schedule'] }}</td>
                                <td>{{ $row['findings_count'] }}</td>
                                <td><x-status-badge :tone="$row['follow_up_badge']">{{ $row['follow_up_status'] }}</x-status-badge></td>
                                <td>
                                    <div class="table-actions">
                                        <x-action-icon :href="route('admin.assignments.show', $row['assignment'])" icon="eye" label="Lihat detail" tone="view" />
                                        <x-action-icon :action="route('admin.monitoring.reminder', $row['assignment'])" icon="bell" label="Kirim pengingat" tone="warning">
                                            <input type="hidden" name="process" value="{{ $row['pending_process'] }}">
                                        </x-action-icon>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">Tidak ada penugasan audit untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif ($activeTab === 'visual')
            <div class="dashboard-grid">
                <div class="panel" style="grid-column: 1 / -1">
                    <h3 class="panel-title">Heatmap Unit x Standar</h3>
                    <p class="muted">Gunakan tampilan ini untuk membaca standar yang paling sering lemah pada periode terpilih.</p>
                    <x-visual.heatmap :standards="$heatmapStandards" :rows="$heatmapRows" />
                </div>
                <div class="panel" style="grid-column: 1 / -1">
                    <h3 class="panel-title">Timeline Siklus Audit</h3>
                    <p class="muted">Setiap baris adalah unit, setiap segmen menunjukkan progres aktual per tahapan.</p>
                    <x-visual.timeline :rows="$timelineRows" :markers="$timelineMarkers" />
                </div>
            </div>
        @elseif ($activeTab === 'self-evaluation')
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Evaluasi Diri Belum Selesai</h3>
                    <p class="muted">Daftar unit yang melewati batas evaluasi diri dan belum final.</p>
                </div>
                <form method="post" action="{{ route('admin.monitoring.self-evaluation-reminders', request()->query()) }}">
                    @csrf
                    <button type="submit">Kirim Pengingat Massal</button>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Lead Auditor</th>
                            <th>Batas Evaluasi Diri</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unfinishedSelfEvaluations as $row)
                            <tr style="background:#fff1f0">
                                <td>{{ $row['unit'] }}</td>
                                <td>{{ $row['lead_auditor'] }}</td>
                                <td>{{ $row['assignment']->auditPeriod->batas_evaluasi_diri->format('d/m/Y') }}</td>
                                <td><x-status-badge tone="danger">{{ $row['self_evaluation_status'] }}</x-status-badge></td>
                                <td>
                                    <div class="table-actions">
                                        <x-action-icon :action="route('admin.monitoring.reminder', $row['assignment'])" icon="bell" label="Kirim pengingat" tone="warning">
                                        <input type="hidden" name="process" value="evaluasi diri">
                                        </x-action-icon>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Tidak ada evaluasi diri yang terlambat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif ($activeTab === 'late-findings')
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Temuan Terlambat</h3>
                    <p class="muted">Temuan berstatus terlambat dan belum ditutup.</p>
                </div>
                <x-excel-action :href="route('admin.monitoring.export.late-findings', request()->query())" mode="export" label="Ekspor Excel" />
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nomor Temuan</th>
                            <th>Unit</th>
                            <th>Kategori</th>
                            <th>Prioritas</th>
                            <th>Target Awal</th>
                            <th>Hari Terlambat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lateFindings as $finding)
                            <tr style="background:#fff1f0">
                                <td>{{ $finding->nomor_temuan }}</td>
                                <td>{{ $finding->assignment->unit->kode }} - {{ $finding->assignment->unit->nama }}</td>
                                <td>{{ \App\Models\Finding::kategoriOptions()[$finding->kategori] ?? $finding->kategori }}</td>
                                <td><x-status-badge tone="danger">{{ \App\Models\Finding::prioritasOptions()[$finding->prioritas] ?? $finding->prioritas }}</x-status-badge></td>
                                <td>{{ $finding->target_penyelesaian->format('d/m/Y') }}</td>
                                <td>{{ $finding->target_penyelesaian->diffInDays(now()) }} hari</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Tidak ada temuan terlambat untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="toolbar">
                <div>
                    <h3 class="panel-title">Tindak Lanjut Menunggu Verifikasi</h3>
                    <p class="muted">Tindak lanjut yang sudah diajukan auditee dan belum diverifikasi auditor.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nomor Temuan</th>
                            <th>Unit</th>
                            <th>Auditor</th>
                            <th>Tanggal Diajukan</th>
                            <th>Hari Menunggu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingFollowUps as $followUp)
                            <tr>
                                <td>{{ $followUp->finding->nomor_temuan }}</td>
                                <td>{{ $followUp->assignment->unit->kode }} - {{ $followUp->assignment->unit->nama }}</td>
                                <td>{{ $followUp->assignment->leadAuditor->name }}</td>
                                <td>{{ $followUp->updated_at->format('d/m/Y H:i') }}</td>
                                <td><x-status-badge tone="warning">{{ $followUp->updated_at->diffInDays(now()) }} hari</x-status-badge></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Tidak ada tindak lanjut yang menunggu verifikasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
