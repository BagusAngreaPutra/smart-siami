@extends('layouts.app')

@section('title', 'Instrumen AMI - SMART SIAMI')
@section('page_title', 'Instrumen AMI')

@php
    $activeInstrumentCount = $instruments->getCollection()->where('is_active', true)->count();
    $inactiveInstrumentCount = $instruments->getCollection()->where('is_active', false)->count();
    $selectedStandardId = request('instrument_standard_id');
@endphp

@section('content')
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="warning">{{ session('warning') }}</div>
    @endif

    @if (session('import_errors'))
        <div class="warning">
            <strong>Beberapa baris gagal diimpor.</strong>
            <ul>
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="instrument-hero">
        <div>
            <span class="instrument-eyebrow">Master audit</span>
            <h2>Bank Instrumen AMI</h2>
            <p>Kelola kriteria, indikator, matriks penilaian, dan template Excel dalam satu ruang kerja yang lebih ringkas.</p>
        </div>
        <div class="instrument-stats" aria-label="Ringkasan instrumen">
            <div class="instrument-stat">
                <span>Kriteria</span>
                <strong>{{ $standardOptions->count() }}</strong>
            </div>
            <div class="instrument-stat">
                <span>Instrumen</span>
                <strong>{{ $instruments->total() }}</strong>
            </div>
            <div class="instrument-stat">
                <span>Lembaga</span>
                <strong>{{ $accreditationBodyOptions->count() }}</strong>
            </div>
        </div>
    </section>

    <section class="instrument-workflow" aria-label="Alur kerja instrumen">
        <div class="workflow-step">
            <span>A</span>
            <strong>Kriteria</strong>
            <p>Siapkan folder standar.</p>
        </div>
        <div class="workflow-step">
            <span>B</span>
            <strong>Template</strong>
            <p>Unduh format Excel.</p>
        </div>
        <div class="workflow-step">
            <span>C</span>
            <strong>Import</strong>
            <p>Masukkan instrumen massal.</p>
        </div>
        <div class="workflow-step">
            <span>D</span>
            <strong>Kelola</strong>
            <p>Edit, salin, nonaktifkan.</p>
        </div>
    </section>

    <section class="instrument-section">
        <div class="instrument-section-header">
            <div>
                <h3>Filter dan Aksi</h3>
                <p class="muted">Pilih data yang ingin dilihat, lalu unduh template atau tambah instrumen baru.</p>
            </div>
            <div class="actions">
                <button class="button secondary" type="button" data-template-modal-open>Unduh Template</button>
                <div class="excel-action-group" aria-label="Import dan export instrumen">
                    <x-excel-action mode="import" label="Import Excel" data-import-modal-open="instruments-import" />
                    <x-excel-action :href="route('admin.instruments.export', request()->query())" mode="export" label="Ekspor Excel" />
                </div>
                <a class="button" href="{{ route('admin.instruments.create', ['standard_id' => $selectedStandardId]) }}">Tambah Instrumen</a>
            </div>
        </div>

        <form class="instrument-filter-grid" method="get" action="{{ route('admin.standards') }}">
            <div class="form-field">
                <label for="instrument_standard_id">Kriteria/Standar Akreditasi</label>
                <select id="instrument_standard_id" name="instrument_standard_id">
                    <option value="">Semua</option>
                    @foreach ($standardOptions as $standard)
                        <option value="{{ $standard->id }}" @selected((string) $selectedStandardId === (string) $standard->id)>{{ $standard->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label for="accreditation_body">Lembaga Akreditasi</label>
                <select id="accreditation_body" name="accreditation_body">
                    <option value="">Semua</option>
                    @foreach ($accreditationBodyOptions as $body)
                        <option value="{{ $body }}" @selected(request('accreditation_body') === $body)>{{ $body }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label for="instrument_status">Status</label>
                <select id="instrument_status" name="instrument_status">
                    <option value="">Semua</option>
                    <option value="aktif" @selected(request('instrument_status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('instrument_status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>

            <div class="instrument-filter-actions">
                <button type="submit">Terapkan</button>
                <a class="button secondary" href="{{ route('admin.standards') }}">Reset</a>
            </div>
        </form>
    </section>

    <details class="instrument-section standard-manager" {{ $standardOptions->isEmpty() ? 'open' : '' }}>
        <summary>
            <span>
                <strong>Kelola Kriteria/Standar</strong>
                <small>{{ $standardOptions->count() }} kriteria tersedia</small>
            </span>
            <span class="standard-manager-caret">Buka</span>
        </summary>

        <div class="standard-manager-actions">
            <a class="button" href="{{ route('admin.quality-standards.create') }}">Tambah Kriteria</a>
        </div>

        @if ($standardOptions->isEmpty())
            <div class="warning">
                Belum ada kriteria/standar. Tambahkan manual atau import file utama yang berisi kolom Kriteria/Standar Akreditasi.
            </div>
        @else
            <form id="bulk-action-standards" class="bulk-action-bar standard-bulk-action-bar" method="post" action="{{ route('admin.quality-standards.bulk-action') }}" hidden data-bulk-action-bar>
                @csrf
                <span class="bulk-action-count"><span data-bulk-selected-count>0</span> dipilih</span>
                <button class="button secondary bulk-deactivate-button" type="submit" name="action" value="deactivate" data-bulk-action-button>Nonaktifkan</button>
                <button
                    class="button secondary bulk-delete-button"
                    type="submit"
                    name="action"
                    value="delete"
                    data-bulk-action-button
                    data-danger-confirm
                    data-danger-title="Hapus kriteria/standar terpilih?"
                    data-danger-message="Kriteria/standar yang sudah memiliki instrumen tidak akan dihapus."
                    data-danger-message-template="Hapus {count} kriteria/standar yang dicentang? Kriteria/standar yang sudah memiliki instrumen tidak akan dihapus."
                    data-danger-confirm-label="Ya, Hapus"
                >Hapus</button>
            </form>

            <div class="table-wrap compact-table" data-bulk-container>
                <table>
                    <thead>
                        <tr>
                            <th class="instrument-select-cell">
                                <input type="checkbox" aria-label="Pilih semua kriteria/standar di halaman ini" data-bulk-select-all>
                            </th>
                            <th>Kode</th>
                            <th>Nama Kriteria/Standar</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($standardOptions as $standard)
                            <tr>
                                <td class="instrument-select-cell">
                                    <input type="checkbox" name="standard_ids[]" value="{{ $standard->id }}" form="bulk-action-standards" aria-label="Pilih kriteria {{ $standard->kode }}" data-bulk-select>
                                </td>
                                <td>{{ $standard->kode }}</td>
                                <td>{{ $standard->nama }}</td>
                                <td>{{ $standard->urutan }}</td>
                                <td><span class="badge @if (! $standard->is_active) off @endif">{{ $standard->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td>
                                    <div class="table-actions">
                                        <x-action-icon :href="route('admin.quality-standards.edit', $standard)" icon="edit" label="Edit kriteria" tone="edit" />
                                        <x-action-icon
                                            :action="route('admin.quality-standards.toggle-active', $standard)"
                                            method="patch"
                                            icon="power"
                                            :label="$standard->is_active ? 'Nonaktifkan kriteria' : 'Aktifkan kriteria'"
                                            :tone="$standard->is_active ? 'warning' : 'success'"
                                        />
                                        <x-action-icon
                                            :action="route('admin.quality-standards.destroy', $standard)"
                                            method="delete"
                                            icon="trash"
                                            label="Hapus kriteria"
                                            tone="danger"
                                            :confirm="true"
                                            confirm-title="Hapus kriteria/standar?"
                                            confirm-message="Kriteria/standar hanya akan terhapus jika belum memiliki instrumen."
                                            confirm-label="Ya, Hapus"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </details>

    <section class="instrument-section">
        <div class="instrument-section-header">
            <div>
                <h3>Daftar Instrumen</h3>
                <p class="muted">{{ $activeInstrumentCount }} aktif, {{ $inactiveInstrumentCount }} nonaktif pada halaman ini.</p>
            </div>
            <form id="bulk-action-instruments" class="bulk-action-bar" method="post" action="{{ route('admin.instruments.bulk-action') }}" hidden data-bulk-action-bar>
                @csrf
                <span class="bulk-action-count"><span data-bulk-selected-count>0</span> dipilih</span>
                <button class="button secondary bulk-deactivate-button" type="submit" name="action" value="deactivate" data-bulk-action-button>
                    Nonaktifkan
                </button>
                <button
                    class="button secondary bulk-delete-button"
                    type="submit"
                    name="action"
                    value="delete"
                    data-bulk-action-button
                    data-danger-confirm
                    data-danger-title="Hapus instrumen terpilih?"
                    data-danger-message="Instrumen yang sudah dipakai audit tidak akan dihapus. Instrumen lain yang masih bersih akan dihapus permanen."
                    data-danger-message-template="Hapus {count} instrumen yang dicentang? Instrumen yang sudah dipakai audit tidak akan dihapus."
                    data-danger-confirm-label="Ya, Hapus"
                >
                    Hapus
                </button>
            </form>
        </div>

        <div class="table-wrap instrument-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="instrument-select-cell">
                            <input type="checkbox" aria-label="Pilih semua instrumen di halaman ini" data-bulk-select-all>
                        </th>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Lembaga Akreditasi</th>
                        <th>Kriteria/Standar</th>
                        <th>Kode Indikator</th>
                        <th>Standar Universitas</th>
                        <th>Indikator Akreditasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($instruments as $instrument)
                        <tr>
                            <td class="instrument-select-cell">
                                <input type="checkbox" name="instrument_ids[]" value="{{ $instrument->id }}" form="bulk-action-instruments" aria-label="Pilih instrumen {{ $instrument->kode }}" data-bulk-select>
                            </td>
                            <td>{{ $instrument->urutan }}</td>
                            <td><strong>{{ $instrument->kode }}</strong></td>
                            <td>{{ $instrument->accreditation_body ?: '-' }}</td>
                            <td>{{ $instrument->standard?->nama }}</td>
                            <td>{{ $instrument->kode_indikator_akreditasi ?: '-' }}</td>
                            <td>{{ $instrument->standar_universitas ?: '-' }}</td>
                            <td>{{ $truncate($instrument->pertanyaan) }}</td>
                            <td><span class="badge @if (! $instrument->is_active) off @endif">{{ $instrument->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <x-action-icon :href="route('admin.instruments.edit', $instrument)" icon="edit" label="Edit instrumen" tone="edit" />
                                    <x-action-icon :action="route('admin.instruments.duplicate', $instrument)" icon="copy" label="Salin instrumen" tone="neutral" />
                                    <x-action-icon
                                        :action="route('admin.instruments.toggle-active', $instrument)"
                                        method="patch"
                                        icon="power"
                                        :label="$instrument->is_active ? 'Nonaktifkan instrumen' : 'Aktifkan instrumen'"
                                        :tone="$instrument->is_active ? 'warning' : 'success'"
                                    />
                                    <x-action-icon
                                        :action="route('admin.instruments.destroy', $instrument)"
                                        method="delete"
                                        icon="trash"
                                        label="Hapus instrumen"
                                        tone="danger"
                                        :confirm="true"
                                        confirm-title="Hapus instrumen?"
                                        confirm-message="Instrumen hanya akan terhapus jika belum memiliki data audit. Jika sudah dipakai, sistem akan menolak dan menyarankan nonaktifkan."
                                        confirm-label="Ya, Hapus"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="instrument-empty">
                                    <strong>Belum ada instrumen.</strong>
                                    <span>Unduh template, isi data, lalu import Excel untuk mulai mengisi bank instrumen.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $instruments->links() }}</div>
    </section>

    <x-import-modal
        id="instruments-import"
        title="Import Instrumen AMI"
        description="Upload template instrumen yang sudah diisi. Sistem akan membaca kriteria, indikator, target, dan bobot dari file."
        :action="route('admin.instruments.import')"
        input-id="instrument_file"
        accept=".xlsx,.xls,.xml,.csv,.txt"
    />

    <div class="template-modal" data-template-modal hidden>
        <div class="template-modal-backdrop" data-template-modal-close></div>
        <section class="template-modal-card" role="dialog" aria-modal="true" aria-labelledby="templateModalTitle">
            <div class="template-modal-header">
                <div>
                    <span class="instrument-eyebrow">Template Excel</span>
                    <h3 id="templateModalTitle">Pilih Template</h3>
                    <p class="muted">Unduh template utama atau template yang sudah terisi nama kriteria/standar.</p>
                </div>
                <button class="template-modal-close" type="button" aria-label="Tutup modal template" data-template-modal-close>&times;</button>
            </div>

            <a class="template-option template-option-main" href="{{ route('admin.instruments.template') }}">
                <span>AMI</span>
                <strong>Template utama lengkap</strong>
                <small>Semua kolom tersedia, cocok untuk import awal.</small>
            </a>

            @if ($standardOptions->isEmpty())
                <div class="warning">
                    Belum ada kriteria/standar internal. Tambahkan kriteria terlebih dahulu untuk membuka template per standar.
                </div>
            @else
                <div class="template-option-grid">
                    @foreach ($standardOptions as $standard)
                        <a class="template-option" href="{{ route('admin.instruments.template.standard', $standard) }}">
                            <span>{{ $standard->kode }}</span>
                            <strong>{{ $standard->nama }}</strong>
                            <small>Template khusus kriteria ini.</small>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const modal = document.querySelector('[data-template-modal]');
            const openButton = document.querySelector('[data-template-modal-open]');
            const closeButtons = document.querySelectorAll('[data-template-modal-close]');

            if (! modal || ! openButton) {
                return;
            }

            const openModal = () => {
                modal.hidden = false;
                document.body.classList.add('modal-open');
                const firstLink = modal.querySelector('a, button');
                firstLink?.focus();
            };

            const closeModal = () => {
                modal.hidden = true;
                document.body.classList.remove('modal-open');
                openButton.focus();
            };

            openButton.addEventListener('click', openModal);
            closeButtons.forEach((button) => button.addEventListener('click', closeModal));
            modal.querySelectorAll('.template-option').forEach((link) => {
                link.addEventListener('click', () => window.setTimeout(closeModal, 150));
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && ! modal.hidden) {
                    closeModal();
                }
            });
        })();

    </script>
@endpush
