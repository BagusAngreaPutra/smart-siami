<?php

namespace App\Notifications;

use App\Models\FollowUpVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowUpVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly FollowUpVerification $verification)
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
        $this->verification->loadMissing('followUp.finding');
        $finding = $this->verification->followUp->finding;
        $message = $this->verification->keputusan === 'disetujui'
            ? "Tindak lanjut Anda untuk temuan {$finding->nomor_temuan} telah disetujui."
            : "Tindak lanjut untuk temuan {$finding->nomor_temuan} perlu perbaikan. Catatan: {$this->verification->catatan_verifikasi}";

        return [
            'title' => 'Hasil verifikasi tindak lanjut',
            'message' => $message,
            'follow_up_id' => $this->verification->follow_up_id,
            'finding_id' => $finding->id,
            'url' => route('auditee.findings-followups.show', $finding, absolute: false),
        ];
    }
}
