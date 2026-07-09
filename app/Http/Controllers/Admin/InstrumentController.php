<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clarification;
use App\Models\Evaluation;
use App\Models\Finding;
use App\Models\Instrument;
use App\Models\SelfAssessment;
use App\Models\Standard;
use App\Support\ExcelXml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

    public function destroy(Instrument $instrument): RedirectResponse
    {
        if ($this->hasAuditData($instrument)) {
            return back()->with('warning', 'Instrumen sudah memiliki data audit. Untuk menjaga audit trail, gunakan Nonaktifkan, bukan Hapus.');
        }

        $instrument->delete();

        return back()->with('status', 'Instrumen berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'instrument_ids' => ['required', 'array', 'min:1'],
            'instrument_ids.*' => ['integer', 'exists:instruments,id'],
        ]);

        $deleted = 0;
        $blocked = 0;

        Instrument::query()
            ->whereIn('id', $validated['instrument_ids'])
            ->get()
            ->each(function (Instrument $instrument) use (&$deleted, &$blocked): void {
                if ($this->hasAuditData($instrument)) {
                    $blocked++;

                    return;
                }

                $instrument->delete();
                $deleted++;
            });

        if ($blocked > 0) {
            return back()
                ->with('status', "{$deleted} instrumen berhasil dihapus.")
                ->with('warning', "{$blocked} instrumen tidak dihapus karena sudah memiliki data audit. Gunakan Nonaktifkan untuk menjaga audit trail.");
        }

        return back()->with('status', "{$deleted} instrumen berhasil dihapus.");
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

        if ($request->filled('accreditation_body')) {
            $query->where('accreditation_body', $request->string('accreditation_body')->toString());
        }

        $rows = $query->get()->map(fn (Instrument $instrument): array => [
            $instrument->urutan,
            $instrument->kode,
            $instrument->accreditation_body,
            $instrument->sasaran_strategi_kode,
            $instrument->ikss_kode,
            $instrument->indikator_kegiatan_kode,
            $instrument->standard?->nama,
            $instrument->kode_indikator_akreditasi,
            $instrument->standar_universitas,
            $instrument->pertanyaan,
            $instrument->matriks_skor['4'] ?? '',
            $instrument->matriks_skor['3'] ?? '',
            $instrument->matriks_skor['2'] ?? '',
            $instrument->matriks_skor['1'] ?? '',
        ])->all();

        return ExcelXml::download('data-instrumen-ami-siami.xls', 'Intrumen', self::headers(), $rows);
    }

    public function template(): StreamedResponse
    {
        $rows = [
            [1, 'BAN-PT S1', 'BAN-PT S1', '1.1', '1.1.1', '1.1.1.1', 'Visi, Misi, Tujuan dan Strategi', '1.1', 'visi misi tujuan strategi', 'Program Studi memiliki Visi Keilmuan yang memuat keunikan program studi sesuai perkembangan IPTEKS dan kebutuhan pengguna.', '', '', '', ''],
            [2, 'LAMPTKES-BID D3', 'LAMPTKES D3 Kebidanan', '1.1', '1.1.1', '1.1.1.1', 'Visi, Misi, Tujuan dan Strategi', '1.1', 'visi misi tujuan strategi', 'Program Studi memiliki Visi Keilmuan yang relevan dan mendukung pengembangan program studi.', '', '', '', ''],
        ];

        return ExcelXml::download('Instrumen AMI Prodi - Akreditasi rev01.xls', 'Intrumen', self::headers(), $rows);
    }

    public function templateForStandard(Standard $standard): StreamedResponse
    {
        $rows = collect(range(1, 20))
            ->map(fn (int $number): array => [
                $number,
                '',
                '',
                '',
                '',
                '',
                $standard->nama,
                '',
                $standard->target ?: '',
                '',
                '',
                '',
                '',
                '',
            ])
            ->all();

        $filename = 'template-instrumen-'.$this->filenameSlug($standard->nama).'.xls';

        return ExcelXml::download($filename, 'Intrumen', self::headers(), $rows);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,xml,csv,txt', 'max:4096'],
        ]);

        $errors = [];
        $imported = 0;
        $sourceName = $request->file('file')?->getClientOriginalName();

        foreach ($this->instrumentRows($request) as $index => $row) {
            if ($this->isEmptyRow($row) || $this->isBlankTemplateRow($row)) {
                continue;
            }

            $jenisJawaban = 'narasi';
            $instrumentCode = strtoupper($row['kode'] ?? '');
            $standard = $this->resolveTemplateStandard($row, $index);
            $existing = $standard
                ? Instrument::query()->where('standard_id', $standard->id)->where('kode', $instrumentCode)->first()
                : null;

            $payload = [
                'standard_id' => $standard?->id,
                'kode' => $instrumentCode,
                'accreditation_body' => ($row['lembaga_akreditasi'] ?? '') ?: null,
                'sasaran_strategi_kode' => ($row['sasaran_strategi_kode'] ?? '') ?: null,
                'ikss_kode' => ($row['ikss_kode'] ?? '') ?: null,
                'indikator_kegiatan_kode' => ($row['indikator_kegiatan_kode'] ?? '') ?: null,
                'kode_indikator_akreditasi' => ($row['kode_indikator_akreditasi'] ?? '') ?: null,
                'standar_universitas' => ($row['standar_universitas'] ?? '') ?: null,
                'aspek_indikator' => ($row['aspek_indikator'] ?? '') ?: null,
                'nama_indikator' => ($row['standar_universitas'] ?? '') ?: null,
                'pertanyaan' => $row['indikator_akreditasi'] ?? '',
                'jenis_jawaban' => $jenisJawaban,
                'target_kriteria' => $this->targetFromTemplateRow($row),
                'matriks_skor' => $this->matrixPayload($row),
                'bobot' => ($row['bobot'] ?? '') !== '' ? $row['bobot'] : null,
                'panduan_pengisian' => ($row['panduan_pengisian'] ?? '') ?: null,
                'bukti_diperlukan' => 'Bukti dokumen pendukung sesuai indikator akreditasi.',
                'opsi_jawaban' => $this->splitList($row['opsi_jawaban'] ?? ''),
                'skor_min' => ($row['skor_min'] ?? '') !== '' ? (int) $row['skor_min'] : null,
                'skor_max' => ($row['skor_max'] ?? '') !== '' ? (int) $row['skor_max'] : null,
                'kombinasi_jawaban' => $this->splitList($row['kombinasi_jawaban'] ?? ''),
                'is_active' => true,
                'urutan' => ($row['no'] ?? '') !== '' ? (int) $row['no'] : $index + 1,
                'sumber_template' => $sourceName,
                'imported_at' => now(),
            ];

            $validator = validator($payload, $this->rules($jenisJawaban, $existing, $payload['standard_id']));

            if (! $standard) {
                $validator->after(fn ($validator) => $validator->errors()->add('kriteria_standar_akreditasi', 'Kriteria/Standar Akreditasi wajib diisi.'));
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
        return ['No', 'Kode', 'Lembaga Akreditas', 'Sasaran Strategi (SS) - Kode', 'Indikator Kinerja Sasaran Strategi (IKSS) - Kode', 'Indikator Kegiatan (IK) - Kode', 'Kriteria/Standar Akreditasi', 'Kode Indikator Akreditasi', 'Standar Universitas', 'Indikator Akreditasi', 'Matriks Penilaian 4', 'Matriks Penilaian 3', 'Matriks Penilaian 2', 'Matriks Penilaian 1'];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function instrumentRows(Request $request): array
    {
        $file = $request->file('file');

        if (! $file) {
            return [];
        }

        $rawRows = ExcelXml::readRaw($file);
        $headerIndex = $this->detectInstrumentHeaderIndex($rawRows);

        if ($headerIndex === null) {
            return [];
        }

        $headers = $this->normalizeImportHeaders($rawRows[$headerIndex], $rawRows[$headerIndex + 1] ?? []);
        $rows = [];

        foreach (array_slice($rawRows, $headerIndex + 1) as $offset => $rawRow) {
            if ($offset === 0 && $this->looksLikeScoreSubheader($rawRow)) {
                continue;
            }

            $record = [];

            foreach ($headers as $column => $header) {
                if ($header === '') {
                    continue;
                }

                $record[$header] = trim((string) ($rawRow[$column] ?? ''));
            }

            $rows[] = $this->normalizeInstrumentRow($record);
        }

        return $rows;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function detectInstrumentHeaderIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $headers = array_map(fn (string $value): string => $this->normalizeImportHeader($value), $row);

            if (
                in_array('no', $headers, true)
                && in_array('kode', $headers, true)
                && in_array('lembaga_akreditasi', $headers, true)
                && in_array('kriteria_standar_akreditasi', $headers, true)
                && in_array('indikator_akreditasi', $headers, true)
            ) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $headerRow
     * @param  array<int, string>  $subHeaderRow
     * @return array<int, string>
     */
    private function normalizeImportHeaders(array $headerRow, array $subHeaderRow = []): array
    {
        $headers = [];

        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeImportHeader($header);
            $subHeader = $this->normalizeImportHeader($subHeaderRow[$index] ?? '');

            if (in_array($subHeader, ['1', '2', '3', '4'], true)) {
                $headers[$index] = 'matriks_skor_'.$subHeader;

                continue;
            }

            $headers[$index] = $normalized;
        }

        return $headers;
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, string>
     */
    private function normalizeInstrumentRow(array $row): array
    {
        return [
            'no' => $row['no'] ?? '',
            'kode' => $row['kode'] ?? '',
            'lembaga_akreditasi' => $row['lembaga_akreditasi'] ?? '',
            'sasaran_strategi_kode' => $row['sasaran_strategi_kode'] ?? '',
            'ikss_kode' => $row['ikss_kode'] ?? '',
            'indikator_kegiatan_kode' => $row['indikator_kegiatan_kode'] ?? '',
            'kriteria_standar_akreditasi' => $row['kriteria_standar_akreditasi'] ?? '',
            'kode_indikator_akreditasi' => $row['kode_indikator_akreditasi'] ?? '',
            'standar_universitas' => $row['standar_universitas'] ?? '',
            'indikator_akreditasi' => $row['indikator_akreditasi'] ?? '',
            'matriks_skor_4' => $row['matriks_skor_4'] ?? '',
            'matriks_skor_3' => $row['matriks_skor_3'] ?? '',
            'matriks_skor_2' => $row['matriks_skor_2'] ?? '',
            'matriks_skor_1' => $row['matriks_skor_1'] ?? '',
        ];
    }

    private function normalizeImportHeader(string $header): string
    {
        $header = Str::of($header)
            ->lower()
            ->replace(['/', '-'], ' ')
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return match ($header) {
            'lembaga_akreditas', 'lembaga_akreditasi' => 'lembaga_akreditasi',
            'sasaran_strategi_ss_kode' => 'sasaran_strategi_kode',
            'indikator_kinerja_sasaran_strategi_ikss_kode' => 'ikss_kode',
            'indikator_kegiatan_ik_kode' => 'indikator_kegiatan_kode',
            'kriteria_standar_akreditasi' => 'kriteria_standar_akreditasi',
            'standar_universitas' => 'standar_universitas',
            'indikator_akreditasi' => 'indikator_akreditasi',
            'matriks_penilaian' => 'matriks_penilaian',
            'matriks_penilaian_4' => 'matriks_skor_4',
            'matriks_penilaian_3' => 'matriks_skor_3',
            'matriks_penilaian_2' => 'matriks_skor_2',
            'matriks_penilaian_1' => 'matriks_skor_1',
            default => $header,
        };
    }

    /**
     * @param  array<int, string>  $row
     */
    private function looksLikeScoreSubheader(array $row): bool
    {
        $values = collect($row)
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->values()
            ->all();

        return count(array_intersect($values, ['1', '2', '3', '4'])) >= 3;
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, string>
     */
    private function matrixPayload(array $row): array
    {
        return collect([
            '4' => $row['matriks_skor_4'] ?? '',
            '3' => $row['matriks_skor_3'] ?? '',
            '2' => $row['matriks_skor_2'] ?? '',
            '1' => $row['matriks_skor_1'] ?? '',
        ])
            ->map(fn (string $value): string => trim($value))
            ->filter(fn (string $value): bool => $value !== '')
            ->all();
    }

    /**
     * @param  array<string, string>  $row
     */
    private function resolveTemplateStandard(array $row, int $index): ?Standard
    {
        $name = trim($row['kriteria_standar_akreditasi'] ?? '');

        if ($name === '') {
            return null;
        }

        $existing = Standard::query()->where('nama', $name)->first();

        if ($existing) {
            return $existing;
        }

        return Standard::query()->create([
            'kode' => $this->standardCodeFromName($name),
            'nama' => $name,
            'deskripsi' => 'Diimpor dari template Instrumen AMI Prodi - Akreditasi rev01.',
            'target' => $row['standar_universitas'] ?? null,
            'is_active' => true,
            'urutan' => ((int) Standard::query()->max('urutan')) + 1,
        ]);
    }

    private function standardCodeFromName(string $name): string
    {
        $base = Str::upper(Str::limit(Str::slug($name, '-'), 42, ''));
        $base = $base !== '' ? $base : 'STANDAR';
        $candidate = $base;
        $counter = 2;

        while (Standard::query()->where('kode', $candidate)->exists()) {
            $candidate = Str::limit($base, 38, '').'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function targetFromTemplateRow(array $row): string
    {
        foreach (['matriks_skor_4', 'indikator_akreditasi', 'standar_universitas'] as $key) {
            if (($row[$key] ?? '') !== '') {
                return $row[$key];
            }
        }

        return 'Target mengikuti indikator akreditasi.';
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Instrument $instrument = null): array
    {
        if ($request->filled('kode')) {
            $request->merge(['kode' => strtoupper($request->string('kode')->toString())]);
        }

        $matrix = $request->input('matriks_skor', []);
        $request->merge([
            'jenis_jawaban' => $request->input('jenis_jawaban', 'narasi') ?: 'narasi',
            'target_kriteria' => $request->input('target_kriteria') ?: ($matrix[4] ?? $matrix['4'] ?? $request->input('pertanyaan') ?: 'Target mengikuti indikator akreditasi.'),
            'bukti_diperlukan' => $request->input('bukti_diperlukan') ?: 'Bukti dokumen pendukung sesuai indikator akreditasi.',
            'nama_indikator' => $request->input('nama_indikator') ?: $request->input('standar_universitas'),
        ]);

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
            'accreditation_body' => ['nullable', 'string', 'max:255'],
            'sasaran_strategi_kode' => ['nullable', 'string', 'max:100'],
            'ikss_kode' => ['nullable', 'string', 'max:100'],
            'indikator_kegiatan_kode' => ['nullable', 'string', 'max:100'],
            'kode_indikator_akreditasi' => ['nullable', 'string', 'max:100'],
            'standar_universitas' => ['nullable', 'string', 'max:255'],
            'aspek_indikator' => ['nullable', 'string', 'max:255'],
            'nama_indikator' => ['nullable', 'string', 'max:255'],
            'pertanyaan' => ['required', 'string'],
            'jenis_jawaban' => ['required', Rule::in(array_keys(Instrument::jenisJawabanOptions()))],
            'target_kriteria' => ['required', 'string'],
            'matriks_skor' => ['nullable', 'array'],
            'matriks_skor.*' => ['nullable', 'string'],
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
            'accreditation_body',
            'sasaran_strategi_kode',
            'ikss_kode',
            'indikator_kegiatan_kode',
            'kode_indikator_akreditasi',
            'standar_universitas',
            'aspek_indikator',
            'nama_indikator',
            'pertanyaan',
            'jenis_jawaban',
            'target_kriteria',
            'matriks_skor',
            'bobot',
            'panduan_pengisian',
            'bukti_diperlukan',
            'opsi_jawaban',
            'skor_min',
            'skor_max',
            'kombinasi_jawaban',
            'sumber_template',
            'imported_at',
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

    private function hasAuditData(Instrument $instrument): bool
    {
        return SelfAssessment::query()->where('instrument_id', $instrument->id)->exists()
            || Evaluation::query()->where('instrument_id', $instrument->id)->exists()
            || Clarification::query()->where('instrument_id', $instrument->id)->exists()
            || Finding::query()->where('instrument_id', $instrument->id)->exists();
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

    /**
     * @param  array<string, string>  $row
     */
    private function isBlankTemplateRow(array $row): bool
    {
        return trim($row['kode'] ?? '') === ''
            && trim($row['indikator_akreditasi'] ?? '') === '';
    }

    private function filenameSlug(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->limit(60, '')
            ->toString() ?: 'standar';
    }

    private function toBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'aktif', 'active', 'ya', 'yes'], true);
    }
}
