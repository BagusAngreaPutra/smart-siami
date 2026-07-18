@extends('layouts.app')

@php
    $isEdit = $assignment->exists;
    $members = old('member_auditor_ids', $selectedMemberIds);
@endphp

@section('title', ($isEdit ? 'Ubah Auditor Penugasan' : 'Tambah Penugasan Audit').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Ubah Auditor dan Jadwal' : 'Tambah Penugasan Audit')

@section('content')
    <div class="panel">
        @if ($isEdit)
            <div class="warning">Perubahan di halaman ini hanya memperbarui auditor, jadwal, dan catatan. Data audit yang sudah berjalan tidak direset.</div>
        @endif

        <form class="form-grid" method="post" action="{{ $isEdit ? route('admin.assignments.update', $assignment) : route('admin.assignments.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <div class="form-field">
                <label for="audit_period_id">Periode Audit</label>
                @if ($isEdit)
                    <input value="{{ $assignment->auditPeriod->nama }}" disabled>
                @else
                    <select id="audit_period_id" name="audit_period_id" required>
                        <option value="">Pilih periode</option>
                        @foreach ($periodOptions as $period)
                            <option value="{{ $period->id }}" @selected((string) old('audit_period_id', $assignment->audit_period_id) === (string) $period->id)>{{ $period->nama }} ({{ $period->status }})</option>
                        @endforeach
                    </select>
                    @error('audit_period_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                @endif
            </div>

            <div class="form-field">
                <label for="unit_id">Unit Auditee</label>
                @if ($isEdit)
                    <input value="{{ $assignment->unit->kode }} - {{ $assignment->unit->nama }}" disabled>
                @else
                    <select id="unit_id" name="unit_id" required>
                        <option value="">Pilih unit</option>
                        @foreach ($unitOptions as $unit)
                            <option value="{{ $unit->id }}" @selected((string) old('unit_id', $assignment->unit_id) === (string) $unit->id)>{{ $unit->kode }} - {{ $unit->nama }}</option>
                        @endforeach
                    </select>
                    @error('unit_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                @endif
            </div>

            <div class="form-field">
                <label for="lead_auditor_id">Lead Auditor</label>
                <select id="lead_auditor_id" name="lead_auditor_id" required>
                    <option value="">Pilih lead auditor</option>
                    @foreach ($auditorOptions as $auditor)
                        <option value="{{ $auditor->id }}" @selected((string) old('lead_auditor_id', $assignment->lead_auditor_id) === (string) $auditor->id)>{{ $auditor->name }}</option>
                    @endforeach
                </select>
                @error('lead_auditor_id')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="member_auditor_ids">Auditor Anggota</label>
                <select id="member_auditor_ids" name="member_auditor_ids[]" multiple size="5">
                    @foreach ($auditorOptions as $auditor)
                        <option value="{{ $auditor->id }}" @selected(in_array($auditor->id, array_map('intval', $members), true))>{{ $auditor->name }}</option>
                    @endforeach
                </select>
                <small class="form-hint">Gunakan Ctrl/Cmd untuk memilih lebih dari satu auditor anggota.</small>
                @error('member_auditor_ids')
                    <div class="error">{{ $message }}</div>
                @enderror
                @error('member_auditor_ids.*')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="tanggal_desk_evaluation">Tanggal Desk Evaluation</label>
                <input id="tanggal_desk_evaluation" name="tanggal_desk_evaluation" type="date" value="{{ old('tanggal_desk_evaluation', $assignment->tanggal_desk_evaluation?->format('Y-m-d')) }}">
                @error('tanggal_desk_evaluation')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="jadwal_visitasi">Jadwal Visitasi</label>
                <input id="jadwal_visitasi" name="jadwal_visitasi" type="date" value="{{ old('jadwal_visitasi', $assignment->jadwal_visitasi?->format('Y-m-d')) }}">
                @error('jadwal_visitasi')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="catatan_penugasan">Catatan Penugasan</label>
                <textarea id="catatan_penugasan" name="catatan_penugasan" placeholder="Contoh: Fokus pemeriksaan pada bukti tindak lanjut dan konsistensi dokumen akademik.">{{ old('catatan_penugasan', $assignment->catatan_penugasan) }}</textarea>
                @error('catatan_penugasan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full actions">
                <a class="button secondary" href="{{ $isEdit ? route('admin.assignments.show', $assignment) : route('admin.assignments') }}">Batal</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Data</button>
            </div>
        </form>
    </div>
@endsection
