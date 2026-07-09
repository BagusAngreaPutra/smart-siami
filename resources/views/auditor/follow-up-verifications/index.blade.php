@extends('layouts.app')

@section('title', 'Verifikasi Perbaikan - SMART SIAMI')
@section('page_title', 'Verifikasi Perbaikan')

@section('content')
    @php
        $newFollowUpIds = unreadNotificationObjectIds('follow_up');
    @endphp

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    <div class="panel">
        <form class="toolbar" method="get">
            <div class="filters">
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
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Menunggu Verifikasi</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
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
                <button class="button-icon-only" type="submit" title="Filter" aria-label="Filter"><x-ui-icon name="filter" /></button>
                <a class="button button-reset button-icon-only" href="{{ route('auditor.follow-up-verifications') }}" title="Reset" aria-label="Reset"><x-ui-icon name="reset" /></a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nomor Temuan</th>
                        <th>Unit</th>
                        <th>Rencana Tindakan</th>
                        <th>Target</th>
                        <th>Tanggal Diajukan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($followUps as $followUp)
                        @php
                            $hasNewInfo = in_array((int) $followUp->id, $newFollowUpIds, true);
                        @endphp
                        <tr class="@if ($hasNewInfo) unread-row @endif">
                            <td>
                                {{ $followUp->finding->nomor_temuan }}
                                @if ($hasNewInfo)
                                    <span class="new-info-dot" title="Tindak lanjut baru diajukan"></span>
                                @endif
                            </td>
                            <td>{{ $followUp->assignment->unit->kode }} - {{ $followUp->assignment->unit->nama }}</td>
                            <td>{{ str($followUp->rencana_tindakan)->limit(90) }}</td>
                            <td>{{ $followUp->target_penyelesaian->format('d/m/Y') }}</td>
                            <td>{{ $followUp->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('auditor.follow-up-verifications.show', $followUp)" icon="check" label="Verifikasi tindak lanjut" tone="success" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada tindak lanjut yang menunggu verifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $followUps->links() }}</div>
    </div>
@endsection
