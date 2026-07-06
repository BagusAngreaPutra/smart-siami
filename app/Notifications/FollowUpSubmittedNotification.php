<?php

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowUpSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly FollowUp $followUp)
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
        $this->followUp->loadMissing(['finding', 'assignment.unit']);

        return [
            'title' => 'Tindak lanjut diajukan',
            'message' => "Tindak lanjut untuk temuan {$this->followUp->finding->nomor_temuan} dari {$this->followUp->assignment->unit->nama} menunggu verifikasi.",
            'follow_up_id' => $this->followUp->id,
            'finding_id' => $this->followUp->finding_id,
            'assignment_id' => $this->followUp->assignment_id,
            'url' => route('auditor.follow-up-verifications.show', $this->followUp, absolute: false),
        ];
    }
}
