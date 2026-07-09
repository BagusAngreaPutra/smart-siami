<?php

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Str;

if (! function_exists('getSetting')) {
    function getSetting(string $key, ?string $default = null): ?string
    {
        return Setting::getValue($key, $default);
    }
}

if (! function_exists('allowedUploadExtensions')) {
    /**
     * @return array<int, string>
     */
    function allowedUploadExtensions(): array
    {
        return collect(explode(',', getSetting('allowed_file_types', 'pdf,docx,xlsx,jpg,png')))
            ->map(fn (string $extension): string => strtolower(trim($extension)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

if (! function_exists('maxUploadKilobytes')) {
    function maxUploadKilobytes(): int
    {
        return max(1, (int) getSetting('max_file_size_mb', '10')) * 1024;
    }
}

if (! function_exists('humanStatus')) {
    function humanStatus(?string $status): string
    {
        if ($status === null || trim($status) === '') {
            return '-';
        }

        return Str::of($status)
            ->replace('_', ' ')
            ->headline()
            ->toString();
    }
}

if (! function_exists('reportPrintSettings')) {
    /**
     * @return array<string, string|int|bool>
     */
    function reportPrintSettings(): array
    {
        $legacyMargin = fn (string $key, string $default): string => getSetting($key, getSetting(str_replace('_cm', '_mm', $key), $default));
        $normalizeMargin = function (string $value): float {
            $number = (float) $value;

            return $number > 5 ? round($number / 10, 1) : $number;
        };

        return [
            'paper_size' => getSetting('report_paper_size', 'A4'),
            'orientation' => getSetting('report_orientation', 'portrait'),
            'margin_top_cm' => $normalizeMargin($legacyMargin('report_margin_top_cm', '1.8')),
            'margin_right_cm' => $normalizeMargin($legacyMargin('report_margin_right_cm', '1.6')),
            'margin_bottom_cm' => $normalizeMargin($legacyMargin('report_margin_bottom_cm', '1.8')),
            'margin_left_cm' => $normalizeMargin($legacyMargin('report_margin_left_cm', '1.6')),
            'font_family' => getSetting('report_font_family', 'Arial'),
            'font_size' => (int) getSetting('report_font_size', '12'),
            'line_height' => getSetting('report_line_height', '1.45'),
            'table_density' => getSetting('report_table_density', 'normal'),
            'show_visual_summary' => getSetting('report_show_visual_summary', '1') === '1',
        ];
    }
}

if (! function_exists('reportLetterheadSettings')) {
    /**
     * @return array<string, string|null>
     */
    function reportLetterheadSettings(): array
    {
        return [
            'mode' => getSetting('report_letterhead_mode', 'default'),
            'institution' => getSetting('report_letterhead_institution', 'Universitas JDS'),
            'unit' => getSetting('report_letterhead_unit', 'Lembaga Penjaminan Mutu'),
            'address' => getSetting('report_letterhead_address', 'Jl. Contoh Kampus JDS No. 10, Kota Pendidikan 12345'),
            'contact' => getSetting('report_letterhead_contact', 'Telp. (021) 555-0199 | Email: lpm@universitasjds.test | www.universitasjds.test'),
            'file_path' => getSetting('report_letterhead_file_path'),
            'file_name' => getSetting('report_letterhead_file_name'),
            'file_type' => getSetting('report_letterhead_file_type'),
            'institution_font_size' => getSetting('report_letterhead_institution_font_size', '16'),
            'unit_font_size' => getSetting('report_letterhead_unit_font_size', '14'),
            'address_font_size' => getSetting('report_letterhead_address_font_size', '11'),
            'institution_bold' => getSetting('report_letterhead_institution_bold', '1'),
            'unit_bold' => getSetting('report_letterhead_unit_bold', '1'),
            'address_bold' => getSetting('report_letterhead_address_bold', '0'),
            'logo_width' => getSetting('report_letterhead_logo_width', '88'),
        ];
    }
}

if (! function_exists('reportLetterheadLines')) {
    /**
     * @return array<int, string>
     */
    function reportLetterheadLines(): array
    {
        $letterhead = reportLetterheadSettings();

        return array_values(array_filter([
            $letterhead['institution'] ?: getSetting('nama_institusi', 'SIAMI'),
            $letterhead['unit'] ?: getSetting('nama_lpm', 'Lembaga Penjaminan Mutu'),
            $letterhead['address'] ?: null,
            $letterhead['contact'] ?: null,
        ]));
    }
}

if (! function_exists('notificationUrlPath')) {
    function notificationUrlPath(?string $url): string
    {
        if (! $url) {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = '/'.ltrim($path, '/');

        return rtrim($path, '/') ?: '/';
    }
}

if (! function_exists('currentNotificationPath')) {
    function currentNotificationPath(): string
    {
        return rtrim(request()->getPathInfo(), '/') ?: '/';
    }
}

if (! function_exists('notificationPathMatchesCurrentPage')) {
    function notificationPathMatchesCurrentPage(string $notificationPath): bool
    {
        return $notificationPath === currentNotificationPath();
    }
}

if (! function_exists('markViewedNotificationsForCurrentPage')) {
    function markViewedNotificationsForCurrentPage(?User $user = null): void
    {
        $user ??= auth()->user();

        if (! $user || ! request()->isMethod('get')) {
            return;
        }

        Notification::query()
            ->where('user_id', $user->id)
            ->active()
            ->unread()
            ->get()
            ->filter(fn (Notification $notification): bool => notificationPathMatchesCurrentPage(notificationUrlPath($notification->url_tujuan))
                || notificationObjectMatchesCurrentRoute($notification))
            ->each(fn (Notification $notification): null => $notification->markAsRead());
    }
}

if (! function_exists('notificationObjectMatchesCurrentRoute')) {
    function notificationObjectMatchesCurrentRoute(Notification $notification): bool
    {
        if (! $notification->objek_tipe || ! $notification->objek_id) {
            return false;
        }

        $objectId = (int) $notification->objek_id;
        $route = request()->route();

        if (! $route) {
            return false;
        }

        $routeModelId = function (string $key) use ($route): ?int {
            $value = $route->parameter($key);

            if (is_object($value) && isset($value->id)) {
                return (int) $value->id;
            }

            if (is_numeric($value)) {
                return (int) $value;
            }

            return null;
        };

        return match ($notification->objek_tipe) {
            'audit_assignment' => $routeModelId('assignment') === $objectId,
            'clarification' => $routeModelId('clarification') === $objectId,
            'finding' => $routeModelId('finding') === $objectId,
            'follow_up' => $routeModelId('followUp') === $objectId,
            'visit' => $routeModelId('visit') === $objectId,
            default => false,
        };
    }
}

if (! function_exists('unreadNotificationCountForMenu')) {
    function unreadNotificationCountForMenu(string $routeName, ?User $user = null): int
    {
        $user ??= auth()->user();

        if (! $user) {
            return 0;
        }

        try {
            $menuPath = notificationUrlPath(route($routeName, absolute: false));
        } catch (Throwable) {
            return 0;
        }

        return Notification::query()
            ->where('user_id', $user->id)
            ->active()
            ->unread()
            ->get()
            ->filter(function (Notification $notification) use ($menuPath, $routeName): bool {
                $notificationPath = notificationUrlPath($notification->url_tujuan);

                if ($notificationPath === '') {
                    return false;
                }

                if (Str::startsWith($notificationPath.'/', $menuPath.'/')) {
                    return true;
                }

                if (Str::endsWith($routeName, '.dashboard') && $notificationPath === '/dashboard') {
                    return true;
                }

                return notificationBelongsToMenuRoute($notification, $routeName);
            })
            ->count();
    }
}

if (! function_exists('notificationBelongsToMenuRoute')) {
    function notificationBelongsToMenuRoute(Notification $notification, string $routeName): bool
    {
        $targets = match ($notification->objek_tipe) {
            'audit_assignment' => [
                'auditor.tasks',
                'auditor.desk-evaluation',
            ],
            'clarification' => [
                'auditor.clarifications',
                'auditee.clarifications',
            ],
            'visit' => [
                'auditor.visitations',
                'auditee.visit-schedules',
            ],
            'finding',
            'follow_up_verification' => [
                'auditee.findings-followups',
            ],
            'follow_up' => [
                'auditor.follow-up-verifications',
            ],
            default => [],
        };

        return in_array($routeName, $targets, true);
    }
}

if (! function_exists('unreadNotificationObjectIds')) {
    /**
     * @return array<int, int>
     */
    function unreadNotificationObjectIds(string $objectType, ?User $user = null): array
    {
        $user ??= auth()->user();

        if (! $user) {
            return [];
        }

        return Notification::query()
            ->where('user_id', $user->id)
            ->active()
            ->unread()
            ->where('objek_tipe', $objectType)
            ->whereNotNull('objek_id')
            ->pluck('objek_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
