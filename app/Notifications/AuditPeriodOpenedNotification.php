<?php

namespace App\Notifications;

use App\Models\AuditPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuditPeriodOpenedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AuditPeriod $auditPeriod)
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
            'title' => 'Periode audit dibuka',
            'message' => "Periode {$this->auditPeriod->nama} telah dibuka.",
            'audit_period_id' => $this->auditPeriod->id,
            'audit_period_name' => $this->auditPeriod->nama,
            'url' => route('dashboard', absolute: false),
        ];
    }
}
