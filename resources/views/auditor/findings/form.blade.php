@extends('layouts.app')

@section('title', ($finding->exists ? 'Edit Temuan' : 'Tambah Temuan') . ' - SMART SIAMI')
@section('page_title', $finding->exists ? 'Edit Temuan' : 'Tambah Temuan')

@section('content')
    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    @if ($isLocked)
        <div class="warning">Temuan yang sudah aktif hanya dapat diubah pada kategori dan target penyelesaian.</div>
    @endif

    <div class="panel">
        <form class="sectioned-form" method="post" action="{{ $finding->exists ? route('auditor.findings.update', $finding) : route('auditor.findings.store') }}">
            @csrf
            @if ($finding->exists)
                @method('put')
            @endif

            <section class="form-section">
                <div class="form-section-title">
                    <span>1</span>
                    <div>
                        <h3>Konteks Audit</h3>
                        <p>Pilih unit, standar, dan instrumen agar temuan punya jejak audit yang jelas.</p>
                    </div>
                </div>

                <div class="form-field full">
                    <label for="assignment_id">Assignment / Unit</label>
                    <select id="assignment_id" name="assignment_id" required @disabled($isLocked)>
                        <option value="">Pilih penugasan</option>
                        @foreach ($assignments as $assignment)
                            <option value="{{ $assignment->id }}" @selected((string) old('assignment_id', $finding->assignment_id) === (string) $assignment->id)>
                                {{ $assignment->unit->kode }} - {{ $assignment->unit->nama }} | {{ $assignment->auditPeriod->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="standard_id">Standar</label>
                    <select id="standard_id" name="standard_id" required @disabled($isLocked)>
                        <option value="">Pilih standar</option>
                        @foreach ($standards as $standard)
                            <option value="{{ $standard->id }}" @selected((string) old('standard_id', $finding->standard_id) === (string) $standard->id)>{{ $standard->kode }} - {{ $standard->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="instrument_id">Instrumen</label>
                    <select id="instrument_id" name="instrument_id" required @disabled($isLocked)>
                        <option value="">Pilih instrumen</option>
                        @foreach ($standards as $standard)
                            @foreach ($standard->instruments as $instrument)
                                <option value="{{ $instrument->id }}" data-standard="{{ $standard->id }}" data-kriteria="{{ e($instrument->target_kriteria) }}" @selected((string) old('instrument_id', $finding->instrument_id) === (string) $instrument->id)>
                                    {{ $instrument->kode }} - {{ str($instrument->pertanyaan)->limit(70) }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-title">
                    <span>2</span>
                    <div>
                        <h3>Klasifikasi</h3>
                        <p>Tentukan tingkat temuan dan prioritasnya sebelum menulis uraian detail.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" required>
                        @foreach ($kategoriOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('kategori', $finding->kategori) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="prioritas">Prioritas</label>
                    <select id="prioritas" name="prioritas" required @disabled($isLocked)>
                        @foreach ($prioritasOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('prioritas', $finding->prioritas ?? 'sedang') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-title">
                    <span>3</span>
                    <div>
                        <h3>Isi Temuan</h3>
                        <p>Uraikan kondisi aktual, kriteria, bukti, akar masalah, dan rekomendasi auditor.</p>
                    </div>
                </div>

                <div class="form-field full">
                    <label for="kondisi_aktual">Kondisi Aktual</label>
                    <textarea id="kondisi_aktual" name="kondisi_aktual" required @disabled($isLocked)>{{ old('kondisi_aktual', $finding->kondisi_aktual) }}</textarea>
                </div>

                <div class="form-field full">
                    <label for="kriteria">Kriteria</label>
                    <textarea id="kriteria" name="kriteria" required @disabled($isLocked)>{{ old('kriteria', $finding->kriteria) }}</textarea>
                </div>

                <div class="form-field full">
                    <label for="bukti_objektif">Bukti Objektif</label>
                    <textarea id="bukti_objektif" name="bukti_objektif" required @disabled($isLocked)>{{ old('bukti_objektif', $finding->bukti_objektif) }}</textarea>
                </div>

                <div class="form-field full">
                    <label for="akar_masalah_awal">Akar Masalah Awal</label>
                    <textarea id="akar_masalah_awal" name="akar_masalah_awal" @disabled($isLocked)>{{ old('akar_masalah_awal', $finding->akar_masalah_awal) }}</textarea>
                </div>

                <div class="form-field full">
                    <label for="rekomendasi_auditor">Rekomendasi Auditor</label>
                    <textarea id="rekomendasi_auditor" name="rekomendasi_auditor" required @disabled($isLocked)>{{ old('rekomendasi_auditor', $finding->rekomendasi_auditor) }}</textarea>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-title">
                    <span>4</span>
                    <div>
                        <h3>Target Penyelesaian</h3>
                        <p>Tentukan batas waktu tindak lanjut agar auditee punya acuan yang jelas.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="target_penyelesaian">Target Penyelesaian</label>
                    <input id="target_penyelesaian" name="target_penyelesaian" type="date" value="{{ old('target_penyelesaian', $finding->target_penyelesaian?->format('Y-m-d')) }}" required>
                </div>
            </section>

            <div class="form-actions-sticky">
                <a class="button secondary" href="{{ $finding->exists ? route('auditor.findings.show', $finding) : route('auditor.findings') }}">Kembali</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Draft</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const standardSelect = document.getElementById('standard_id');
            const instrumentSelect = document.getElementById('instrument_id');
            const criteriaField = document.getElementById('kriteria');
            const instrumentOptions = Array.from(instrumentSelect.options);

            function syncInstruments() {
                const standardId = standardSelect.value;
                instrumentOptions.forEach((option) => {
                    if (!option.value) return;
                    option.hidden = option.dataset.standard !== standardId;
                });
                const selected = instrumentSelect.selectedOptions[0];
                if (selected && selected.hidden) {
                    instrumentSelect.value = '';
                }
            }

            standardSelect.addEventListener('change', syncInstruments);
            instrumentSelect.addEventListener('change', () => {
                const selected = instrumentSelect.selectedOptions[0];
                if (selected && selected.dataset.kriteria && !criteriaField.value.trim()) {
                    criteriaField.value = selected.dataset.kriteria;
                }
            });
            syncInstruments();
        </script>
    @endpush
@endsection
