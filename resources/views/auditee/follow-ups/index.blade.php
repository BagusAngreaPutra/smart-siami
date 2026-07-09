@extends('layouts.app')

@section('title', 'Tindak Lanjut Temuan - SMART SIAMI')
@section('page_title', 'Tindak Lanjut Temuan')

@section('content')
    @php
        $newFindingIds = unreadNotificationObjectIds('finding');
        $newVerificationIds = unreadNotificationObjectIds('follow_up_verification');
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
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('kategori') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="status">Status Temuan</label>
                    <select id="status" name="status">
                        <option value="">Semua Status</option>
                        @foreach ($findingStatusOptions as $value => $label)
                            @if (! in_array($value, ['draft', 'dibatalkan'], true))
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <button class="button-icon-only" type="submit" title="Filter" aria-label="Filter"><x-ui-icon name="filter" /></button>
                <a class="button button-reset button-icon-only" href="{{ route('auditee.findings-followups') }}" title="Reset" aria-label="Reset"><x-ui-icon name="reset" /></a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nomor Temuan</th>
                        <th>Kategori</th>
                        <th>Rekomendasi Auditor</th>
                        <th>Target</th>
                        <th>Status TL</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($findings as $finding)
                        @php
                            $followUp = $finding->latestFollowUp;
                            $latestVerification = $followUp?->latestVerification;
                            $hasNewInfo = in_array((int) $finding->id, $newFindingIds, true) || ($latestVerification && in_array((int) $latestVerification->id, $newVerificationIds, true));
                        @endphp
                        <tr class="@if ($hasNewInfo) unread-row @endif">
                            <td>
                                {{ $finding->nomor_temuan }}
                                @if ($hasNewInfo)
                                    <span class="new-info-dot" title="Informasi temuan baru"></span>
                                @endif
                            </td>
                            <td>{{ $kategoriOptions[$finding->kategori] }}</td>
                            <td>{{ str($finding->rekomendasi_auditor)->limit(80) }}</td>
                            <td>{{ $finding->target_penyelesaian->format('d/m/Y') }}</td>
                            <td><span class="badge @if (! $followUp || $followUp->status === 'draft') off @endif">{{ $followUp ? $followUpStatusOptions[$followUp->status] : $followUpStatusOptions['belum_dibuat'] }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('auditee.findings-followups.show', $finding)" icon="eye" label="Buka tindak lanjut" tone="view" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada temuan audit yang dikirim ke unit Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $findings->links() }}</div>
    </div>
@endsection
