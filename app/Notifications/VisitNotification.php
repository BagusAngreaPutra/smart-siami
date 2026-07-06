<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VisitNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Visit $visit,
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
        $this->visit->loadMissing(['assignment.unit', 'assignment.auditPeriod']);

        $message = match ($this->event) {
            'berita_acara' => 'Berita acara visitasi telah dikirim untuk dikonfirmasi.',
            default => 'Jadwal visitasi telah ditetapkan atau diperbarui.',
        };

        return [
            'title' => 'Visitasi audit',
            'message' => "{$message} Unit {$this->visit->assignment->unit->nama}, periode {$this->visit->assignment->auditPeriod->nama}.",
            'visit_id' => $this->visit->id,
            'assignment_id' => $this->visit->assignment_id,
            'url' => route('auditee.visit-schedules.show', $this->visit, absolute: false),
        ];
    }
}
