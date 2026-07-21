<?php

namespace App\Http\Middleware;

use App\Models\SystemLog;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackSystemActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $userBeforeRequest = $request->user();
        $response = $next($request);

        if (! $this->shouldRecord($request, $response)) {
            return $response;
        }

        $user = $userBeforeRequest ?? $request->user();

        if (! $user instanceof User) {
            return $response;
        }

        try {
            if (! Schema::hasTable('system_logs')) {
                return $response;
            }

            $routeName = $request->route()?->getName();
            [$subjectType, $subjectId] = $this->subjectFromRoute($request);
            $action = $this->actionFor((string) $routeName, $request->method());
            $role = $user->role;

            SystemLog::query()->create([
                'user_id' => $user->id,
                'actor_name' => $user->name,
                'actor_email' => $user->email,
                'actor_role' => is_object($role) && property_exists($role, 'value') ? $role->value : (string) $role,
                'event' => $this->eventFor((string) $routeName, $request->method()),
                'action' => $action,
                'description' => $action.'.',
                'route_name' => $routeName,
                'method' => strtoupper($request->method()),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'metadata' => [
                    'input' => $this->sanitizedInput($request->input()),
                    'response_status' => $response->getStatusCode(),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (in_array(strtoupper($request->method()), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return false;
        }

        if ($response->getStatusCode() >= 400 || ! $request->route()?->getName()) {
            return false;
        }

        return ! Str::startsWith((string) $request->route()?->getName(), 'admin.system-logs');
    }

    /** @return array{0: string|null, 1: int|null} */
    private function subjectFromRoute(Request $request): array
    {
        foreach (array_reverse($request->route()?->parameters() ?? []) as $parameter) {
            if ($parameter instanceof Model && is_numeric($parameter->getKey())) {
                return [$parameter::class, (int) $parameter->getKey()];
            }
        }

        return [null, null];
    }

    private function eventFor(string $routeName, string $method): string
    {
        if (in_array($routeName, ['login.store', 'logout'], true)) {
            return 'authentication';
        }

        if (str_ends_with($routeName, '.destroy') || strtoupper($method) === 'DELETE') {
            return 'deleted';
        }

        if (str_ends_with($routeName, '.store')) {
            return 'created';
        }

        if (in_array(strtoupper($method), ['PUT', 'PATCH'], true)) {
            return 'updated';
        }

        return 'action';
    }

    private function actionFor(string $routeName, string $method): string
    {
        if ($routeName === 'login.store') {
            return 'Masuk ke sistem';
        }

        if ($routeName === 'logout') {
            return 'Keluar dari sistem';
        }

        $module = $this->moduleFor($routeName);

        return match (true) {
            str_ends_with($routeName, '.store') => "Menambahkan {$module}",
            str_ends_with($routeName, '.update') => "Memperbarui {$module}",
            str_ends_with($routeName, '.destroy') || strtoupper($method) === 'DELETE' => "Menghapus {$module}",
            str_contains($routeName, 'bulk-action') => "Menjalankan aksi massal pada {$module}",
            str_ends_with($routeName, '.duplicate') => "Menyalin {$module}",
            str_ends_with($routeName, '.toggle-active') => "Mengubah status aktif {$module}",
            str_ends_with($routeName, '.activate') => "Mengaktifkan {$module}",
            str_ends_with($routeName, '.close') => "Menutup {$module}",
            str_ends_with($routeName, '.archive') => "Mengarsipkan {$module}",
            str_contains($routeName, 'notify'), str_contains($routeName, 'reminder') => "Mengirim notifikasi {$module}",
            str_contains($routeName, 'submit'), str_contains($routeName, 'finalize') => "Mengirim {$module}",
            default => "Menjalankan aksi pada {$module}",
        };
    }

    private function moduleFor(string $routeName): string
    {
        $modules = [
            'periods' => 'periode audit',
            'units' => 'unit',
            'managed-users' => 'pengguna',
            'quality-standards' => 'standar mutu',
            'instruments' => 'instrumen AMI',
            'assignments' => 'penugasan audit',
            'monitoring' => 'monitoring',
            'settings' => 'pengaturan',
            'profile' => 'profil akun',
            'notifications' => 'notifikasi',
            'self-assessments' => 'evaluasi diri',
            'evidences' => 'bukti dokumen',
            'clarifications' => 'klarifikasi',
            'visitations' => 'visitasi',
            'findings' => 'temuan audit',
            'follow-up' => 'tindak lanjut',
        ];

        foreach ($modules as $key => $label) {
            if (str_contains($routeName, $key)) {
                return $label;
            }
        }

        return 'sistem';
    }

    /** @param array<string, mixed> $input @return array<string, mixed> */
    private function sanitizedInput(array $input, int $depth = 0): array
    {
        if ($depth > 4) {
            return [];
        }

        $sanitized = [];

        foreach ($input as $key => $value) {
            if (preg_match('/password|token|secret|authorization|cookie/i', (string) $key)) {
                $sanitized[$key] = '[DISEMBUNYIKAN]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizedInput($value, $depth + 1);
            } elseif (is_scalar($value) || $value === null) {
                $sanitized[$key] = is_string($value) ? Str::limit($value, 500, '…') : $value;
            }
        }

        return $sanitized;
    }
}
