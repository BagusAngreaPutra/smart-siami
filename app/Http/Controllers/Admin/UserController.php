<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use App\Support\ExcelXml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function create(): View
    {
        return view('admin.users.form', [
            'managedUser' => new User(['is_active' => true]),
            'roleOptions' => UserRole::cases(),
            'unitOptions' => Unit::query()->where('is_active', true)->orderBy('kode')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $validated = $this->validated($request);
        $validated['password'] = Hash::make($request->filled('password') ? $request->string('password')->toString() : 'password');

        User::query()->create($validated);

        return redirect()->route('admin.users', ['tab' => 'users'])->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $managedUser): View
    {
        return view('admin.users.form', [
            'managedUser' => $managedUser,
            'roleOptions' => UserRole::cases(),
            'unitOptions' => Unit::query()->where('is_active', true)->orWhere('id', $managedUser->unit_id)->orderBy('kode')->get(),
        ]);
    }

    public function update(Request $request, User $managedUser): RedirectResponse
    {
        $managedUser->update($this->validated($request, $managedUser));

        return redirect()->route('admin.users', ['tab' => 'users'])->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function editPassword(User $managedUser): View
    {
        return view('admin.users.reset-password', [
            'managedUser' => $managedUser,
        ]);
    }

    public function updatePassword(Request $request, User $managedUser): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $managedUser->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users', ['tab' => 'users'])->with('status', 'Password pengguna berhasil direset.');
    }

    public function toggleActive(Request $request, User $managedUser): RedirectResponse
    {
        if ($managedUser->is($request->user())) {
            return back()->with('warning', 'Admin tidak dapat menonaktifkan akun yang sedang digunakan.');
        }

        $managedUser->update(['is_active' => ! $managedUser->is_active]);

        return back()->with('status', $managedUser->is_active ? 'Pengguna berhasil diaktifkan.' : 'Pengguna berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, User $managedUser): RedirectResponse
    {
        if ($managedUser->is($request->user())) {
            return back()->with('warning', 'Admin tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($this->hasAuditTrail($managedUser)) {
            return back()->with('warning', 'Pengguna tidak dapat dihapus karena sudah memiliki jejak aktivitas audit. Gunakan Nonaktifkan agar riwayat tetap aman.');
        }

        $managedUser->delete();

        return back()->with('status', 'Pengguna berhasil dihapus.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['deactivate', 'delete'])],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = collect($validated['user_ids'])
            ->map(fn ($id): int => (int) $id)
            ->reject(fn (int $id): bool => $id === $request->user()->id)
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('warning', 'Akun yang sedang digunakan tidak dapat diproses.');
        }

        if ($validated['action'] === 'deactivate') {
            $updated = User::query()
                ->whereIn('id', $ids)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return back()->with('status', "{$updated} pengguna berhasil dinonaktifkan.");
        }

        $deleted = 0;
        $blocked = 0;

        User::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (User $user) use (&$deleted, &$blocked): void {
                if ($this->hasAuditTrail($user)) {
                    $blocked++;

                    return;
                }

                $user->delete();
                $deleted++;
            });

        return $blocked > 0
            ? back()->with('status', "{$deleted} pengguna berhasil dihapus.")->with('warning', "{$blocked} pengguna tidak dihapus karena sudah memiliki jejak aktivitas audit.")
            : back()->with('status', "{$deleted} pengguna berhasil dihapus.");
    }

    public function export(Request $request): StreamedResponse
    {
        $query = User::query()->with('unit')->orderBy('name');

        if ($request->filled('user_role')) {
            $query->where('role', $request->string('user_role')->toString());
        }

        if ($request->filled('user_status')) {
            $query->where('is_active', $request->string('user_status')->toString() === 'aktif');
        }

        if ($request->filled('user_unit_id')) {
            $query->where('unit_id', $request->integer('user_unit_id'));
        }

        $rows = $query->get()->map(fn (User $user): array => [
            $user->name,
            $user->nip_nidn,
            $user->email,
            $user->phone,
            $user->role->label(),
            $user->unit?->nama,
            $user->is_active ? 'aktif' : 'nonaktif',
        ])->all();

        return ExcelXml::download('data-pengguna-siami.xls', 'Data Pengguna', self::headers(), $rows);
    }

    public function template(): StreamedResponse
    {
        return ExcelXml::download('template-import-pengguna-siami.xls', 'Template Pengguna', self::importHeaders(), [
            ['Kaprodi Sistem Informasi', '0000000003', 'anggreaputrabagus@gmail.com', '080000000003', 'auditee', 'SI', 'aktif', 'password'],
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

            $role = strtolower($row['role'] ?? '');
            $unit = filled($row['unit_kode'] ?? null)
                ? Unit::query()->where('kode', strtoupper($row['unit_kode']))->first()
                : null;
            $existing = User::query()->where('email', $row['email'] ?? '')->first();

            $payload = [
                'name' => $row['nama'] ?? '',
                'nip_nidn' => filled($row['nip_nidn'] ?? null) ? $row['nip_nidn'] : null,
                'email' => $row['email'] ?? '',
                'phone' => filled($row['phone'] ?? null) ? $row['phone'] : null,
                'role' => $role,
                'unit_id' => $role === UserRole::Auditee->value ? $unit?->id : null,
                'is_active' => $this->toBoolean($row['is_active'] ?? 'aktif'),
            ];

            $validator = validator($payload, $this->rules($role, $existing));

            if ($role === UserRole::Auditee->value && ! $unit) {
                $validator->after(fn ($validator) => $validator->errors()->add('unit_kode', 'Unit auditee wajib diisi dan harus valid.'));
            }

            if ($validator->fails()) {
                $errors[] = 'Baris '.($index + 2).': '.$validator->errors()->first();

                continue;
            }

            if (filled($row['password'] ?? null)) {
                $payload['password'] = Hash::make($row['password']);
            } elseif (! $existing) {
                $payload['password'] = Hash::make('password');
            }

            User::query()->updateOrCreate(['email' => $payload['email']], $payload);
            $imported++;
        }

        return redirect()
            ->route('admin.users', ['tab' => 'users'])
            ->with('status', "{$imported} pengguna berhasil diimpor.")
            ->with('import_errors', $errors);
    }

    /**
     * @return array<int, string>
     */
    private static function headers(): array
    {
        return ['Nama', 'NIP/NIDN', 'Email', 'Phone', 'Peran', 'Unit', 'Status'];
    }

    /**
     * @return array<int, string>
     */
    private static function importHeaders(): array
    {
        return ['nama', 'nip_nidn', 'email', 'phone', 'role', 'unit_kode', 'is_active', 'password'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $managedUser = null): array
    {
        $role = $request->string('role')->toString();
        $validated = $request->validate($this->rules($role, $managedUser));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['unit_id'] = $role === UserRole::Auditee->value ? $validated['unit_id'] : null;

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(string $role, ?User $managedUser = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nip_nidn' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nip_nidn')->ignore($managedUser?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($managedUser?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in(array_map(fn (UserRole $role): string => $role->value, UserRole::cases()))],
            'unit_id' => [Rule::requiredIf($role === UserRole::Auditee->value), 'nullable', 'integer', 'exists:units,id'],
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

    private function hasAuditTrail(User $user): bool
    {
        $id = $user->id;

        return DB::table('audit_periods')->where('created_by', $id)->exists()
            || DB::table('audit_assignments')->where('lead_auditor_id', $id)->exists()
            || DB::table('assignment_auditors')->where('auditor_id', $id)->exists()
            || DB::table('evidences')->where('uploaded_by', $id)->exists()
            || DB::table('evaluations')->where('diperiksa_oleh', $id)->exists()
            || DB::table('clarifications')->where('dibuka_oleh', $id)->exists()
            || DB::table('clarification_messages')->where('pengirim_id', $id)->exists()
            || DB::table('clarification_evidences')->where('diunggah_oleh', $id)->exists()
            || DB::table('visit_attachments')->where('diunggah_oleh', $id)->exists()
            || DB::table('findings')->where('dibuat_oleh', $id)->orWhere('difinalisasi_oleh', $id)->exists()
            || DB::table('finding_status_histories')->where('changed_by', $id)->exists()
            || DB::table('follow_ups')->where('dibuat_oleh', $id)->exists()
            || DB::table('follow_up_verifications')->where('verifikator_id', $id)->exists();
    }
}
