@extends('layouts.app')

@section('title', 'Klarifikasi Auditor - SMART SIAMI')
@section('page_title', 'Klarifikasi Auditor')

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
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit">Filter</button>
                <a class="button secondary" href="{{ route('auditee.clarifications') }}">Reset</a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Instrumen</th>
                        <th>Pertanyaan Auditor</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clarifications as $clarification)
                        @php
                            $firstMessage = $clarification->messages->first();
                            $hasNewInfo = in_array((int) $clarification->id, $newClarificationIds, true);
                        @endphp
                        <tr class="@if ($hasNewInfo) unread-row @endif">
                            <td>{{ $clarification->assignment->auditPeriod->nama }}</td>
                            <td>
                                {{ $clarification->instrument->kode }} - {{ $clarification->instrument->standard->nama }}
                                @if ($hasNewInfo)
                                    <span class="new-info-dot" title="Klarifikasi baru"></span>
                                @endif
                            </td>
                            <td>{{ $firstMessage ? str($firstMessage->isi_pesan)->limit(80) : '-' }}</td>
                            <td><span class="badge @if ($clarification->status === 'selesai') off @endif">{{ $statusOptions[$clarification->status] }}</span></td>
                            <td>
                                <a class="link-button" href="{{ route('auditee.clarifications.show', $clarification) }}">
                                    Buka
                                    @if ($hasNewInfo)
                                        <span class="new-info-dot" title="Informasi baru"></span>
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada klarifikasi dari auditor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $clarifications->links() }}</div>
    </div>
@endsection
