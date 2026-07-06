<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use App\Models\Standard;
use App\Support\ExcelXml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstrumentController extends Controller
{
    public function create(Request $request): View
    {
        return view('admin.instruments.form', [
            'instrument' => new Instrument([
                'standard_id' => $request->integer('standard_id') ?: null,
                'jenis_jawaban' => 'narasi',
                'is_active' => true,
                'urutan' => ((int) Instrument::query()->where('standard_id', $request->integer('standard_id'))->max('urutan')) + 1,
            ]),
            'standardOptions' => Standard::query()->where('is_active', true)->orderBy('urutan')->orderBy('kode')->get(),
            'jenisJawabanOptions' => Instrument::jenisJawabanOptions(),
            'kombinasiOptions' => Instrument::kombinasiOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Instrument::query()->create($this->validated($request));

        return redirect()->route('admin.standards', ['tab' => 'instruments'])->with('status', 'Instrumen berhasil ditambahkan.');
    }

    public function edit(Instrument $instrument): View
    {
        return view('admin.instruments.form', [
            'instrument' => $instrument,
            'standardOptions' => Standard::query()->where('is_active', true)->orWhere('id', $instrument->standard_id)->orderBy('urutan')->orderBy('kode')->get(),
            'jenisJawabanOptions' => Instrument::jenisJawabanOptions(),
            'kombinasiOptions' => Instrument::kombinasiOptions(),
        ]);
    }

    public function update(Request $request, Instrument $instrument): RedirectResponse
    {
        $instrument->update($this->validated($request, $instrument));

        return redirect()->route('admin.standards', ['tab' => 'instruments'])->with('status', 'Instrumen berhasil diperbarui.');
    }

    public function duplicate(Instrument $instrument): RedirectResponse
    {
        $copy = $instrument->replicate();
        $copy->kode = $this->copyCode($instrument);
        $copy->is_active = false;
        $copy->urutan = ((int) Instrument::query()->where('standard_id', $instrument->standard_id)->max('urutan')) + 1;
        $copy->save();

        return redirect()->route('admin.instruments.edit', $copy)->with('status', 'Instrumen berhasil disalin. Periksa kode dan aktifkan jika sudah siap.');
    }

    public function toggleActive(Instrument $instrument): RedirectResponse
    {
        if ($instrument->is_active && $instrument->hasBeenUsedInActiveAuditPeriod()) {
            return back()->with('warning', 'Instrumen sudah digunakan dalam periode audit aktif dan hanya dapat dinonaktifkan.');
        }

        $instrument->update(['is_active' => ! $instrument->is_active]);

        return back()->with('status', $instrument->is_active ? 'Instrumen berhasil diaktifkan.' : 'Instrumen berhasil dinonaktifkan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Instrument::query()->with('standard')->orderBy('standard_id')->orderBy('urutan')->orderBy('kode');

        if ($request->filled('instrument_standard_id')) {
            $query->where('standard_id', $request->integer('instrument_standard_id'));
        }

        if ($request->filled('instrument_status')) {
            $query->where('is_active', $request->string('instrument_status')->toString() === 'aktif');
        }

        $rows = $query->get()->map(fn (Instrument $instrument): array => [
            $instrument->standard?->kode,
            $instrument->kode,
            $instrument->nama_indikator,
            $instrument->pertanyaan,
            Instrument::jenisJawabanOptions()[$instrument->jenis_jawaban] ?? $instrument->jenis_jawaban,
            $instrument->target_kriteria,
            $instrument->bobot,
            $instrument->panduan_pengisian,
            $instrument->bukti_diperlukan,
            implode('|', $instrument->opsi_jawaban ?? []),
            $instrument->skor_min,
            $instrument->skor_max,
            implode('|', $instrument->kombinasi_jawaban ?? []),
            $instrument->is_active ? 'aktif' : 'nonaktif',
            $instrument->urutan,
        ])->all();

        return ExcelXml::download('data-instrumen-siami.xls', 'Data Instrumen', self::headers(), $rows);
    }

    public function template(): StreamedResponse
    {
        return ExcelXml::download('template-import-instrumen-siami.xls', 'Template Instrumen', self::importHeaders(), [
            ['S1', 'S1-01', 'Kejelasan visi misi', 'Apakah visi, misi, tujuan, dan strategi unit telah ditetapkan?', 'narasi', 'Dokumen VMTS tersedia dan disosialisasikan.', '10', 'Isi ringkasan kondisi dan tautan bukti.', 'Dokumen VMTS, notulen sosialisasi', '', '', '', '', 'aktif', '1'],
            ['S1', 'S1-02', 'Tingkat pemahaman VMTS', 'Berapa persentase sivitas akademika yang memahami VMTS?', 'skor', 'Nilai semakin baik jika persentase semakin tinggi.', '10', 'Masukkan skor sesuai panduan.', 'Rekap survei pemahaman VMTS', '', '1', '4', '', 'aktif', '2'],
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048'],
        ]);

        $errors = [];
        $imported = 0;

        foreach (ExcelXml::read($request->file('file')) as $index => $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $standard = Standard::query()->where('kode', strtoupper($row['standard_kode'] ?? ''))->first();
            $jenisJawaban = strtolower($row['jenis_jawaban'] ?? '');
            $existing = $standard
                ? Instrument::query()->where('standard_id', $standard->id)->where('kode', strtoupper($row['kode'] ?? ''))->first()
                : null;

            $payload = [
                'standard_id' => $standard?->id,
                'kode' => strtoupper($row['kode'] ?? ''),
                'nama_indikator' => ($row['nama_indikator'] ?? '') ?: null,
                'pertanyaan' => $row['pertanyaan'] ?? '',
                'jenis_jawaban' => $jenisJawaban,
                'target_kriteria' => $row['target_kriteria'] ?? '',
                'bobot' => ($row['bobot'] ?? '') !== '' ? $row['bobot'] : null,
                'panduan_pengisian' => ($row['panduan_pengisian'] ?? '') ?: null,
                'bukti_diperlukan' => $row['bukti_diperlukan'] ?? '',
                'opsi_jawaban' => $this->splitList($row['opsi_jawaban'] ?? ''),
                'skor_min' => ($row['skor_min'] ?? '') !== '' ? (int) $row['skor_min'] : null,
                'skor_max' => ($row['skor_max'] ?? '') !== '' ? (int) $row['skor_max'] : null,
                'kombinasi_jawaban' => $this->splitList($row['kombinasi_jawaban'] ?? ''),
                'is_active' => $this->toBoolean($row['is_active'] ?? 'aktif'),
                'urutan' => ($row['urutan'] ?? '') !== '' ? (int) $row['urutan'] : 0,
            ];

            $validator = validator($payload, $this->rules($jenisJawaban, $existing, $payload['standard_id']));

            if (! $standard) {
                $validator->after(fn ($validator) => $validator->errors()->add('standard_kode', 'Kode standar tidak ditemukan.'));
            }

            $this->addJenisJawabanValidation($validator, $payload);

            if ($validator->fails()) {
                $errors[] = 'Baris '.($index + 2).': '.$validator->errors()->first();

                continue;
            }

            Instrument::query()->updateOrCreate(
                ['standard_id' => $payload['standard_id'], 'kode' => $payload['kode']],
                $this->normalizePayload($payload),
            );
            $imported++;
        }

        return redirect()
            ->route('admin.standards', ['tab' => 'instruments'])
            ->with('status', "{$imported} instrumen berhasil diimpor.")
            ->with('import_errors', $errors);
    }

    /**
     * @return array<int, string>
     */
    private static function headers(): array
    {
        return ['Standar', 'Kode', 'Nama Indikator', 'Pertanyaan', 'Jenis Jawaban', 'Target Kriteria', 'Bobot', 'Panduan Pengisian', 'Bukti Diperlukan', 'Opsi Jawaban', 'Skor Min', 'Skor Max', 'Kombinasi Jawaban', 'Status', 'Urutan'];
    }

    /**
     * @return array<int, string>
     */
    private static function importHeaders(): array
    {
        return ['standard_kode', 'kode', 'nama_indikator', 'pertanyaan', 'jenis_jawaban', 'target_kriteria', 'bobot', 'panduan_pengisian', 'bukti_diperlukan', 'opsi_jawaban', 'skor_min', 'skor_max', 'kombinasi_jawaban', 'is_active', 'urutan'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Instrument $instrument = null): array
    {
        if ($request->filled('kode')) {
            $request->merge(['kode' => strtoupper($request->string('kode')->toString())]);
        }

        $jenisJawaban = $request->string('jenis_jawaban')->toString();
        $validated = $request->validate($this->rules($jenisJawaban, $instrument, $request->integer('standard_id')));
        $payload = [
            ...$validated,
            'is_active' => $request->boolean('is_active'),
            'opsi_jawaban' => $this->splitLines($request->string('opsi_jawaban')->toString()),
            'kombinasi_jawaban' => $request->array('kombinasi_jawaban'),
        ];

        $validator = validator($payload, []);
        $this->addJenisJawabanValidation($validator, $payload);
        $validator->validate();

        return $this->normalizePayload($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(string $jenisJawaban, ?Instrument $instrument = null, ?int $standardId = null): array
    {
        $standardId ??= request('standard_id');

        return [
            'standard_id' => ['required', 'integer', 'exists:standards,id'],
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('instruments', 'kode')
                    ->where(fn ($query) => $query->where('standard_id', $standardId))
                    ->ignore($instrument?->id),
            ],
            'nama_indikator' => ['nullable', 'string', 'max:255'],
            'pertanyaan' => ['required', 'string'],
            'jenis_jawaban' => ['required', Rule::in(array_keys(Instrument::jenisJawabanOptions()))],
            'target_kriteria' => ['required', 'string'],
            'bobot' => ['nullable', 'numeric', 'min:0'],
            'panduan_pengisian' => ['nullable', 'string'],
            'bukti_diperlukan' => ['required', 'string'],
            'opsi_jawaban' => [Rule::requiredIf($jenisJawaban === 'pilihan'), 'nullable'],
            'skor_min' => [Rule::requiredIf($jenisJawaban === 'skor'), 'nullable', 'integer'],
            'skor_max' => [Rule::requiredIf($jenisJawaban === 'skor'), 'nullable', 'integer', 'gte:skor_min'],
            'kombinasi_jawaban' => [Rule::requiredIf($jenisJawaban === 'kombinasi'), 'nullable', 'array'],
            'kombinasi_jawaban.*' => [Rule::in(array_keys(Instrument::kombinasiOptions()))],
            'is_active' => ['nullable', 'boolean'],
            'urutan' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function addJenisJawabanValidation($validator, array $payload): void
    {
        $validator->after(function ($validator) use ($payload): void {
            if (($payload['jenis_jawaban'] ?? null) === 'pilihan' && count($payload['opsi_jawaban'] ?? []) < 2) {
                $validator->errors()->add('opsi_jawaban', 'Jenis pilihan membutuhkan minimal dua opsi jawaban.');
            }

            if (($payload['jenis_jawaban'] ?? null) === 'kombinasi' && count($payload['kombinasi_jawaban'] ?? []) < 2) {
                $validator->errors()->add('kombinasi_jawaban', 'Jenis kombinasi membutuhkan minimal dua jenis jawaban.');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        if (($payload['jenis_jawaban'] ?? null) !== 'pilihan') {
            $payload['opsi_jawaban'] = null;
        }

        if (($payload['jenis_jawaban'] ?? null) !== 'skor') {
            $payload['skor_min'] = null;
            $payload['skor_max'] = null;
        }

        if (($payload['jenis_jawaban'] ?? null) !== 'kombinasi') {
            $payload['kombinasi_jawaban'] = null;
        }

        return Arr::only($payload, [
            'standard_id',
            'kode',
            'nama_indikator',
            'pertanyaan',
            'jenis_jawaban',
            'target_kriteria',
            'bobot',
            'panduan_pengisian',
            'bukti_diperlukan',
            'opsi_jawaban',
            'skor_min',
            'skor_max',
            'kombinasi_jawaban',
            'is_active',
            'urutan',
        ]);
    }

    private function copyCode(Instrument $instrument): string
    {
        $base = $instrument->kode.'-COPY';
        $candidate = $base;
        $counter = 2;

        while (Instrument::query()->where('standard_id', $instrument->standard_id)->where('kode', $candidate)->exists()) {
            $candidate = $base.$counter;
            $counter++;
        }

        return $candidate;
    }

    /**
     * @return array<int, string>
     */
    private function splitLines(string $value): array
    {
        return collect(preg_split('/\R/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function splitList(string $value): array
    {
        return collect(explode('|', $value))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        return collect($row)->filter(fn (string $value): bool => trim($value) !== '')->isEmpty();
    }

    private function toBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'aktif', 'active', 'ya', 'yes'], true);
    }
}
