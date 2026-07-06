<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Support\ExcelXml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnitController extends Controller
{
    public function create(): View
    {
        return view('admin.units.form', [
            'unit' => new Unit(['is_active' => true]),
            'jenisUnitOptions' => Unit::jenisUnitOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Unit::query()->create($this->validated($request));

        return redirect()->route('admin.users', ['tab' => 'units'])->with('status', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return view('admin.units.form', [
            'unit' => $unit,
            'jenisUnitOptions' => Unit::jenisUnitOptions(),
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $unit->update($this->validated($request, $unit));

        return redirect()->route('admin.users', ['tab' => 'units'])->with('status', 'Unit berhasil diperbarui.');
    }

    public function toggleActive(Request $request, Unit $unit): RedirectResponse
    {
        if ($unit->is_active && $unit->hasActiveAssignments() && ! $request->boolean('confirm_active_assignments')) {
            return back()->with('warning', 'Unit masih memiliki penugasan aktif. Konfirmasi diperlukan sebelum dinonaktifkan.');
        }

        $unit->update(['is_active' => ! $unit->is_active]);

        return back()->with('status', $unit->is_active ? 'Unit berhasil diaktifkan.' : 'Unit berhasil dinonaktifkan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Unit::query()->orderBy('kode');

        if ($request->filled('unit_jenis_unit')) {
            $query->where('jenis_unit', $request->string('unit_jenis_unit')->toString());
        }

        if ($request->filled('unit_status')) {
            $query->where('is_active', $request->string('unit_status')->toString() === 'aktif');
        }

        $rows = $query->get()->map(fn (Unit $unit): array => [
            $unit->kode,
            $unit->nama,
            Unit::jenisUnitOptions()[$unit->jenis_unit] ?? $unit->jenis_unit,
            $unit->fakultas_induk,
            $unit->nama_pimpinan,
            $unit->email,
            $unit->phone,
            $unit->is_active ? 'aktif' : 'nonaktif',
        ])->all();

        return ExcelXml::download('data-unit-siami.xls', 'Data Unit', self::headers(), $rows);
    }

    public function template(): StreamedResponse
    {
        return ExcelXml::download('template-import-unit-siami.xls', 'Template Unit', self::importHeaders(), [
            ['SI', 'Program Studi Sistem Informasi', 'prodi', 'Fakultas Teknologi Informasi', 'Nama Pimpinan', 'si@siami.test', '0800000000', 'aktif'],
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

            $payload = [
                'kode' => strtoupper($row['kode'] ?? ''),
                'nama' => $row['nama'] ?? '',
                'jenis_unit' => strtolower($row['jenis_unit'] ?? ''),
                'fakultas_induk' => ($row['fakultas_induk'] ?? '') ?: null,
                'nama_pimpinan' => ($row['nama_pimpinan'] ?? '') ?: null,
                'email' => ($row['email'] ?? '') ?: null,
                'phone' => ($row['phone'] ?? '') ?: null,
                'is_active' => $this->toBoolean($row['is_active'] ?? 'aktif'),
            ];
            $existing = Unit::query()->where('kode', $payload['kode'])->first();

            $validator = validator($payload, $this->rules($existing));

            if ($validator->fails()) {
                $errors[] = 'Baris '.($index + 2).': '.$validator->errors()->first();

                continue;
            }

            Unit::query()->updateOrCreate(['kode' => $payload['kode']], $payload);
            $imported++;
        }

        return redirect()
            ->route('admin.users', ['tab' => 'units'])
            ->with('status', "{$imported} unit berhasil diimpor.")
            ->with('import_errors', $errors);
    }

    /**
     * @return array<int, string>
     */
    private static function headers(): array
    {
        return ['Kode', 'Nama', 'Jenis Unit', 'Fakultas Induk', 'Nama Pimpinan', 'Email', 'Phone', 'Status'];
    }

    /**
     * @return array<int, string>
     */
    private static function importHeaders(): array
    {
        return ['kode', 'nama', 'jenis_unit', 'fakultas_induk', 'nama_pimpinan', 'email', 'phone', 'is_active'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Unit $unit = null): array
    {
        if ($request->filled('kode')) {
            $request->merge(['kode' => strtoupper($request->string('kode')->toString())]);
        }

        $validated = $request->validate($this->rules($unit));
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Unit $unit = null): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', Rule::unique('units', 'kode')->ignore($unit?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_unit' => ['required', Rule::in(array_keys(Unit::jenisUnitOptions()))],
            'fakultas_induk' => ['nullable', 'string', 'max:255'],
            'nama_pimpinan' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
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
