<?php

namespace App\Notifications;

use App\Enums\UserRole;
use App\Models\Clarification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClarificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Clarification $clarification,
        private readonly string $event
    ) {
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
        $this->clarification->loadMissing(['assignment.unit', 'assignment.auditPeriod', 'instrument']);

        $message = match ($this->event) {
            'dijawab' => 'Auditee telah menjawab klarifikasi.',
            'dibuka_kembali' => 'Klarifikasi dibuka kembali oleh auditor.',
            default => 'Auditor membuka klarifikasi baru.',
        };

        $route = $notifiable->role === UserRole::Auditor
            ? 'auditor.clarifications.show'
            : 'auditee.clarifications.show';

        return [
            'title' => 'Klarifikasi audit',
            'message' => "{$message} Unit {$this->clarification->assignment->unit->nama}, instrumen {$this->clarification->instrument->kode}.",
            'clarification_id' => $this->clarification->id,
            'assignment_id' => $this->clarification->assignment_id,
            'instrument_id' => $this->clarification->instrument_id,
            'url' => route($route, $this->clarification, absolute: false),
        ];
    }
}
