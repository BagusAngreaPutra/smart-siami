<?php

namespace App\Notifications;

use App\Models\AuditAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuditAssignmentNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AuditAssignment $assignment)
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
        return [
            'title' => 'Penugasan audit',
            'message' => "Anda ditugaskan pada audit {$this->assignment->unit->nama} untuk periode {$this->assignment->auditPeriod->nama}.",
            'assignment_id' => $this->assignment->id,
            'audit_period_id' => $this->assignment->audit_period_id,
            'unit_id' => $this->assignment->unit_id,
            'url' => route('dashboard', absolute: false),
        ];
    }
}
