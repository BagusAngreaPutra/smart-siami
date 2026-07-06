<?php

use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\FollowUp;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$sendDeadlineReminderOnce = function (int $userId, string $title, string $message, string $url, string $objectType, int $objectId): int {
    $exists = Notification::query()
        ->where('user_id', $userId)
        ->where('tipe', 'pengingat_batas_waktu')
        ->where('objek_tipe', $objectType)
        ->where('objek_id', $objectId)
        ->whereDate('created_at', now()->toDateString())
        ->exists();

    if ($exists) {
        return 0;
    }

    Notification::sendNotification(
        $userId,
        'pengingat_batas_waktu',
        $title,
        $message,
        $url,
        $objectType,
        $objectId,
    );

    return 1;
};

Artisan::command('siami:deadline-reminders', function () use ($sendDeadlineReminderOnce): int {
    $sent = 0;

    AuditAssignment::query()
        ->with(['auditPeriod', 'unit'])
        ->where('status', 'aktif')
        ->whereHas('auditPeriod', function ($query): void {
            $query->where('status', 'aktif')
                ->whereDate('batas_evaluasi_diri', '<=', now()->addDays(3)->toDateString());
        })
        ->where(function ($query): void {
            $query->whereDoesntHave('selfAssessments')
                ->orWhereHas('selfAssessments', fn ($query) => $query->where('status', '!=', 'final'));
        })
        ->get()
        ->each(function (AuditAssignment $assignment) use (&$sent): void {
            User::query()
                ->where('role', UserRole::Auditee->value)
                ->where('unit_id', $assignment->unit_id)
                ->where('is_active', true)
                ->get()
                ->each(function (User $user) use ($assignment, &$sent): void {
                    $sent += $sendDeadlineReminderOnce(
                        $user->id,
                        'Batas Evaluasi Diri',
                        "Batas evaluasi diri unit {$assignment->unit->kode} adalah {$assignment->auditPeriod->batas_evaluasi_diri->format('d/m/Y')}.",
                        route('auditee.self-evaluations', absolute: false),
                        'audit_assignment',
                        $assignment->id,
                    );
                });
        });

    FollowUp::query()
        ->with(['finding', 'assignment.unit'])
        ->whereIn('status', ['draft', 'diajukan', 'perlu_perbaikan'])
        ->whereDate('target_penyelesaian', '<=', now()->addDays(3)->toDateString())
        ->get()
        ->each(function (FollowUp $followUp) use (&$sent): void {
            $sent += $sendDeadlineReminderOnce(
                $followUp->dibuat_oleh,
                'Batas Tindak Lanjut',
                "Target tindak lanjut temuan {$followUp->finding->nomor_temuan} adalah {$followUp->target_penyelesaian->format('d/m/Y')}.",
                route('auditee.findings-followups.show', $followUp->finding, absolute: false),
                'follow_up',
                $followUp->id,
            );

            if ($followUp->target_penyelesaian->toDateString() < now()->toDateString()) {
                User::query()
                    ->where('role', UserRole::Admin->value)
                    ->where('is_active', true)
                    ->get()
                    ->each(function (User $admin) use ($followUp, &$sent): void {
                        $sent += $sendDeadlineReminderOnce(
                            $admin->id,
                            'Tindak Lanjut Terlambat',
                            "Tindak lanjut temuan {$followUp->finding->nomor_temuan} dari {$followUp->assignment->unit->kode} melewati target.",
                            route('admin.monitoring', ['tab' => 'late-findings'], false),
                            'follow_up',
                            $followUp->id,
                        );
                    });
            }
        });

    $this->info("{$sent} pengingat batas waktu dikirim.");

    return 0;
})->purpose('Send SIAMI automatic deadline reminders');

Schedule::command('siami:deadline-reminders')->dailyAt('07:00');
