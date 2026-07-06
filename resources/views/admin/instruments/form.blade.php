@extends('layouts.app')

@php
    $isEdit = $instrument->exists;
    $oldJenis = old('jenis_jawaban', $instrument->jenis_jawaban);
    $oldOptions = old('opsi_jawaban', implode("\n", $instrument->opsi_jawaban ?? []));
    $oldKombinasi = old('kombinasi_jawaban', $instrument->kombinasi_jawaban ?? []);
@endphp

@section('title', ($isEdit ? 'Edit Instrumen' : 'Tambah Instrumen').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Edit Instrumen' : 'Tambah Instrumen')

@section('content')
    <div class="panel">
        <form class="form-grid" method="post" action="{{ $isEdit ? route('admin.instruments.update', $instrument) : route('admin.instruments.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <div class="form-field">
                <label for="standard_id">Standar</label>
                <select id="standard_id" name="standard_id" required>
                    <option value="">Pilih standar</option>
                    @foreach ($standardOptions as $standard)
                        <option value="{{ $standard->id }}" @selected((string) old('standard_id', $instrument->standard_id) === (string) $standard->id)>{{ $standard->kode }} - {{ $standard->nama }}</option>
                    @endforeach
                </select>
                @error('standard_id')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="kode">Kode</label>
                <input id="kode" name="kode" value="{{ old('kode', $instrument->kode) }}" required>
                @error('kode')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="nama_indikator">Nama Indikator</label>
                <input id="nama_indikator" name="nama_indikator" value="{{ old('nama_indikator', $instrument->nama_indikator) }}">
                @error('nama_indikator')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="urutan">Urutan</label>
                <input id="urutan" name="urutan" type="number" min="0" value="{{ old('urutan', $instrument->urutan) }}" required>
                @error('urutan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="pertanyaan">Pertanyaan</label>
                <textarea id="pertanyaan" name="pertanyaan" required>{{ old('pertanyaan', $instrument->pertanyaan) }}</textarea>
                @error('pertanyaan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="jenis_jawaban">Jenis Jawaban</label>
                <select id="jenis_jawaban" name="jenis_jawaban" required>
                    @foreach ($jenisJawabanOptions as $value => $label)
                        <option value="{{ $value }}" @selected($oldJenis === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis_jawaban')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="bobot">Bobot</label>
                <input id="bobot" name="bobot" type="number" step="0.01" min="0" value="{{ old('bobot', $instrument->bobot) }}">
                @error('bobot')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full conditional-field" data-visible-for="pilihan">
                <label for="opsi_jawaban">Opsi Jawaban</label>
                <textarea id="opsi_jawaban" name="opsi_jawaban">{{ $oldOptions }}</textarea>
                @error('opsi_jawaban')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field conditional-field" data-visible-for="skor">
                <label for="skor_min">Skor Minimum</label>
                <input id="skor_min" name="skor_min" type="number" value="{{ old('skor_min', $instrument->skor_min) }}">
                @error('skor_min')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field conditional-field" data-visible-for="skor">
                <label for="skor_max">Skor Maksimum</label>
                <input id="skor_max" name="skor_max" type="number" value="{{ old('skor_max', $instrument->skor_max) }}">
                @error('skor_max')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full conditional-field" data-visible-for="kombinasi">
                <label>Jenis Jawaban Kombinasi</label>
                <div class="actions">
                    @foreach ($kombinasiOptions as $value => $label)
                        <label class="remember">
                            <input type="checkbox" name="kombinasi_jawaban[]" value="{{ $value }}" @checked(in_array($value, $oldKombinasi, true))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('kombinasi_jawaban')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="target_kriteria">Target Kriteria</label>
                <textarea id="target_kriteria" name="target_kriteria" required>{{ old('target_kriteria', $instrument->target_kriteria) }}</textarea>
                @error('target_kriteria')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="panduan_pengisian">Panduan Pengisian</label>
                <textarea id="panduan_pengisian" name="panduan_pengisian">{{ old('panduan_pengisian', $instrument->panduan_pengisian) }}</textarea>
                @error('panduan_pengisian')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field full">
                <label for="bukti_diperlukan">Bukti Diperlukan</label>
                <textarea id="bukti_diperlukan" name="bukti_diperlukan" required>{{ old('bukti_diperlukan', $instrument->bukti_diperlukan) }}</textarea>
                @error('bukti_diperlukan')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $instrument->is_active))>
                Aktif
            </label>

            <div class="form-field full actions">
                <button type="submit">Simpan</button>
                <a class="button secondary" href="{{ route('admin.standards', ['tab' => 'instruments']) }}">Batal</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const jenisJawaban = document.querySelector('#jenis_jawaban');
        const conditionalFields = document.querySelectorAll('[data-visible-for]');

        function syncConditionalFields() {
            conditionalFields.forEach((field) => {
                field.hidden = field.dataset.visibleFor !== jenisJawaban.value;
            });
        }

        jenisJawaban.addEventListener('change', syncConditionalFields);
        syncConditionalFields();
    </script>
@endpush
