@extends('layouts.app')

@php
    $isEdit = $instrument->exists;
    $oldMatrix = old('matriks_skor', $instrument->matriks_skor ?? []);
@endphp

@section('title', ($isEdit ? 'Edit Instrumen AMI' : 'Tambah Instrumen AMI').' - SMART SIAMI')
@section('page_title', $isEdit ? 'Edit Instrumen AMI' : 'Tambah Instrumen AMI')

@section('content')
    <div class="panel">
        <form class="sectioned-form" method="post" action="{{ $isEdit ? route('admin.instruments.update', $instrument) : route('admin.instruments.store') }}">
            @csrf
            @if ($isEdit)
                @method('put')
            @endif

            <section class="form-section">
                <div class="form-section-title">
                    <span>1</span>
                    <div>
                        <h3>Identitas Instrumen</h3>
                        <p>Tentukan nomor, kode, lembaga, dan kriteria tempat instrumen ini berada.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="urutan">No</label>
                    <input id="urutan" name="urutan" type="number" min="0" value="{{ old('urutan', $instrument->urutan) }}" placeholder="Contoh: 1" required>
                    @error('urutan')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="kode">Kode</label>
                    <input id="kode" name="kode" value="{{ old('kode', $instrument->kode) }}" required placeholder="BAN-PT S1">
                    @error('kode')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="accreditation_body">Lembaga Akreditasi</label>
                    <input id="accreditation_body" name="accreditation_body" value="{{ old('accreditation_body', $instrument->accreditation_body) }}" placeholder="BAN-PT S1">
                    @error('accreditation_body')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="standard_id">Kriteria/Standar Akreditasi</label>
                    <select id="standard_id" name="standard_id" required>
                        <option value="">Pilih kriteria/standar</option>
                        @foreach ($standardOptions as $standard)
                            <option value="{{ $standard->id }}" @selected((string) old('standard_id', $instrument->standard_id) === (string) $standard->id)>{{ $standard->nama }}</option>
                        @endforeach
                    </select>
                    @error('standard_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-title">
                    <span>2</span>
                    <div>
                        <h3>Kode Indikator</h3>
                        <p>Isi kode hierarki jika tersedia dari template akreditasi. Bagian ini boleh kosong jika tidak dipakai.</p>
                    </div>
                </div>

                <div class="form-field">
                    <label for="sasaran_strategi_kode">Sasaran Strategi (SS)</label>
                    <input id="sasaran_strategi_kode" name="sasaran_strategi_kode" value="{{ old('sasaran_strategi_kode', $instrument->sasaran_strategi_kode) }}" placeholder="1.1">
                    @error('sasaran_strategi_kode')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="ikss_kode">IKSS</label>
                    <input id="ikss_kode" name="ikss_kode" value="{{ old('ikss_kode', $instrument->ikss_kode) }}" placeholder="1.1.1">
                    @error('ikss_kode')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="indikator_kegiatan_kode">Indikator Kegiatan (IK)</label>
                    <input id="indikator_kegiatan_kode" name="indikator_kegiatan_kode" value="{{ old('indikator_kegiatan_kode', $instrument->indikator_kegiatan_kode) }}" placeholder="1.1.1.1">
                    @error('indikator_kegiatan_kode')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="kode_indikator_akreditasi">Kode Indikator Akreditasi</label>
                    <input id="kode_indikator_akreditasi" name="kode_indikator_akreditasi" value="{{ old('kode_indikator_akreditasi', $instrument->kode_indikator_akreditasi) }}" placeholder="1.1">
                    @error('kode_indikator_akreditasi')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-title">
                    <span>3</span>
                    <div>
                        <h3>Isi Instrumen</h3>
                        <p>Tulis standar universitas, indikator akreditasi, dan matriks skor yang akan dinilai auditor.</p>
                    </div>
                </div>

                <div class="form-field full">
                    <label for="standar_universitas">Standar Universitas</label>
                    <input id="standar_universitas" name="standar_universitas" value="{{ old('standar_universitas', $instrument->standar_universitas) }}" placeholder="Visi, misi, tujuan, strategi">
                    @error('standar_universitas')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field full">
                    <label for="pertanyaan">Indikator Akreditasi</label>
                    <textarea id="pertanyaan" name="pertanyaan" required placeholder="Contoh: Apakah unit telah menetapkan, melaksanakan, dan mengevaluasi kebijakan sesuai standar yang berlaku?">{{ old('pertanyaan', $instrument->pertanyaan) }}</textarea>
                    @error('pertanyaan')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field full">
                    <label>Matriks Penilaian</label>
                    <div class="form-grid">
                        @foreach ([4, 3, 2, 1] as $score)
                            <div class="form-field">
                                <label for="matriks_skor_{{ $score }}">Skor {{ $score }}</label>
                                <textarea id="matriks_skor_{{ $score }}" name="matriks_skor[{{ $score }}]" placeholder="Contoh kriteria untuk nilai {{ $score }}">{{ $oldMatrix[$score] ?? $oldMatrix[(string) $score] ?? '' }}</textarea>
                            </div>
                        @endforeach
                    </div>
                    @error('matriks_skor')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-title">
                    <span>4</span>
                    <div>
                        <h3>Status</h3>
                        <p>Nonaktifkan hanya jika instrumen belum siap digunakan dalam audit.</p>
                    </div>
                </div>

                <label class="crm-toggle-card">
                    <span class="crm-toggle-copy">
                        <span class="crm-toggle-icon tone-teal" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"></path><path d="M9 7h6M9 11h6"></path></svg>
                        </span>
                        <span>
                            <strong>Status Instrumen</strong>
                            <small>Instrumen aktif tersedia untuk evaluasi diri dan proses audit AMI.</small>
                        </span>
                    </span>
                    <span class="crm-toggle-control">
                        <input class="crm-toggle-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $instrument->is_active))>
                        <span class="crm-toggle-track" aria-hidden="true"><i></i></span>
                        <span class="crm-toggle-state" aria-hidden="true">
                            <span class="state-on">Aktif</span>
                            <span class="state-off">Nonaktif</span>
                        </span>
                    </span>
                </label>
            </section>

            <div class="form-actions-sticky">
                <a class="button secondary" href="{{ route('admin.standards', ['tab' => 'instruments']) }}">Batal</a>
                <button class="with-icon" type="submit"><x-ui-icon name="save" /> Simpan Data</button>
            </div>
        </form>
    </div>
@endsection
