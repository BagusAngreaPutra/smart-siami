<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Standard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StandardController extends Controller
{
    public function create(): View
    {
        return view('admin.standards.form', [
            'standard' => new Standard([
                'is_active' => true,
                'urutan' => ((int) Standard::query()->max('urutan')) + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Standard::query()->create($this->validated($request));

        return redirect()->route('admin.standards', ['tab' => 'standards'])->with('status', 'Kriteria/standar berhasil ditambahkan.');
    }

    public function edit(Standard $standard): View
    {
        return view('admin.standards.form', [
            'standard' => $standard,
        ]);
    }

    public function update(Request $request, Standard $standard): RedirectResponse
    {
        $standard->update($this->validated($request, $standard));

        return redirect()->route('admin.standards', ['tab' => 'standards'])->with('status', 'Kriteria/standar berhasil diperbarui.');
    }

    public function toggleActive(Standard $standard): RedirectResponse
    {
        $standard->update(['is_active' => ! $standard->is_active]);

        return back()->with('status', $standard->is_active ? 'Standar berhasil diaktifkan.' : 'Standar berhasil dinonaktifkan.');
    }

    public function destroy(Standard $standard): RedirectResponse
    {
        if ($standard->instruments()->exists()) {
            return back()->with('warning', 'Kriteria/standar tidak dapat dihapus karena sudah memiliki instrumen. Nonaktifkan jika tidak digunakan.');
        }

        $standard->delete();

        return back()->with('status', 'Kriteria/standar berhasil dihapus.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['deactivate', 'delete'])],
            'standard_ids' => ['required', 'array', 'min:1'],
            'standard_ids.*' => ['integer', 'exists:standards,id'],
        ]);

        if ($validated['action'] === 'deactivate') {
            $updated = Standard::query()
                ->whereIn('id', $validated['standard_ids'])
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return back()->with('status', "{$updated} kriteria/standar berhasil dinonaktifkan.");
        }

        $deleted = 0;
        $blocked = 0;

        Standard::query()
            ->whereIn('id', $validated['standard_ids'])
            ->get()
            ->each(function (Standard $standard) use (&$deleted, &$blocked): void {
                if ($standard->instruments()->exists()) {
                    $blocked++;

                    return;
                }

                $standard->delete();
                $deleted++;
            });

        return $blocked > 0
            ? back()->with('status', "{$deleted} kriteria/standar berhasil dihapus.")->with('warning', "{$blocked} kriteria/standar tidak dihapus karena sudah memiliki instrumen.")
            : back()->with('status', "{$deleted} kriteria/standar berhasil dihapus.");
    }

    public function move(Standard $standard, string $direction): RedirectResponse
    {
        $operator = $direction === 'up' ? '<' : '>';
        $sort = $direction === 'up' ? 'desc' : 'asc';

        $swap = Standard::query()
            ->where('urutan', $operator, $standard->urutan)
            ->orderBy('urutan', $sort)
            ->first();

        if ($swap) {
            [$standardOrder, $swapOrder] = [$standard->urutan, $swap->urutan];
            $standard->update(['urutan' => $swapOrder]);
            $swap->update(['urutan' => $standardOrder]);
        }

        return back()->with('status', 'Urutan standar diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Standard $standard = null): array
    {
        if ($request->filled('kode')) {
            $request->merge(['kode' => strtoupper($request->string('kode')->toString())]);
        }

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('standards', 'kode')->ignore($standard?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'target' => ['nullable', 'string'],
            'urutan' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
