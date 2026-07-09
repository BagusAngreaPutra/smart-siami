@extends('layouts.app')

@section('title', 'Klarifikasi - SMART SIAMI')
@section('page_title', 'Klarifikasi')

@section('content')
    @php
        $newClarificationIds = unreadNotificationObjectIds('clarification');
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
                    <label for="audit_period_id">Periode</label>
                    <select id="audit_period_id" name="audit_period_id">
                        <option value="">Semua Periode</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected((string) request('audit_period_id') === (string) $period->id)>{{ $period->nama }}</option>
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
                <button type="submit">Filter</button>
                <a class="button secondary" href="{{ route('auditor.clarifications') }}">Reset</a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Periode</th>
                        <th>Instrumen</th>
                        <th>Pesan Terakhir</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clarifications as $clarification)
                        @php
                            $lastMessage = $clarification->messages->last();
                            $hasNewInfo = in_array((int) $clarification->id, $newClarificationIds, true);
                        @endphp
                        <tr class="@if ($hasNewInfo) unread-row @endif">
                            <td>{{ $clarification->assignment->unit->kode }} - {{ $clarification->assignment->unit->nama }}</td>
                            <td>{{ $clarification->assignment->auditPeriod->nama }}</td>
                            <td>
                                {{ $clarification->instrument->kode }} - {{ $clarification->instrument->standard->nama }}
                                @if ($hasNewInfo)
                                    <span class="new-info-dot" title="Balasan klarifikasi baru"></span>
                                @endif
                            </td>
                            <td>
                                {{ $lastMessage ? str($lastMessage->isi_pesan)->limit(70) : '-' }}
                                @if ($lastMessage)
                                    <div class="muted">{{ $lastMessage->sender->name }} - {{ $lastMessage->created_at->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td><span class="badge @if ($clarification->status === 'selesai') off @endif">{{ $statusOptions[$clarification->status] }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('auditor.clarifications.show', $clarification)" icon="eye" label="Buka klarifikasi" tone="view" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada klarifikasi untuk penugasan Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $clarifications->links() }}</div>
    </div>
@endsection
