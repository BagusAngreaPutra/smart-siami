<?php

namespace App\Notifications;

use App\Models\Finding;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FindingNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Finding $finding)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->finding->loadMissing(['assignment.unit', 'assignment.auditPeriod', 'standard']);

        return [
            'title' => 'Temuan audit',
            'message' => "Temuan {$this->finding->nomor_temuan} untuk unit {$this->finding->assignment->unit->nama} telah difinalisasi.",
            'finding_id' => $this->finding->id,
            'assignment_id' => $this->finding->assignment_id,
            'audit_period_id' => $this->finding->assignment->audit_period_id,
            'unit_id' => $this->finding->assignment->unit_id,
            'url' => route('auditee.findings-followups', absolute: false),
        ];
    }
}
