<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Mail\SiamiNotificationMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class Notification extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'tipe',
        'judul',
        'isi',
        'url_tujuan',
        'objek_tipe',
        'objek_id',
        'is_read',
        'archived_at',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at')
            ->where('created_at', '>=', now()->subDays(30));
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => $this->read_at ?? now(),
        ]);
    }

    public static function archiveExpired(): int
    {
        return self::query()
            ->whereNull('archived_at')
            ->where('created_at', '<', now()->subDays(30))
            ->update(['archived_at' => now()]);
    }

    public static function sendNotification(
        int $userId,
        string $tipe,
        string $judul,
        string $isi,
        ?string $url = null,
        ?string $objekTipe = null,
        ?int $objekId = null,
    ): self {
        $notification = self::query()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $userId,
            'tipe' => $tipe,
            'judul' => $judul,
            'isi' => $isi,
            'url_tujuan' => $url,
            'objek_tipe' => $objekTipe,
            'objek_id' => $objekId,
            'is_read' => false,
            'type' => 'siami',
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => [
                'type' => $tipe,
                'title' => $judul,
                'message' => $isi,
                'url' => $url,
                'object_type' => $objekTipe,
                'object_id' => $objekId,
            ],
        ]);

        self::sendEmailNotification($notification);

        return $notification;
    }

    private static function sendEmailNotification(self $notification): void
    {
        if (Setting::getValue('email_notifications_enabled', '1') !== '1') {
            return;
        }

        $user = $notification->user;

        if (! $user || ! $user->is_active || ! $user->email) {
            return;
        }

        if (Str::endsWith(strtolower($user->email), '.test')) {
            return;
        }

        if (! in_array($user->role, [UserRole::Auditor, UserRole::Auditee], true)) {
            return;
        }

        try {
            $targetUrl = $notification->url_tujuan
                ? url($notification->url_tujuan)
                : route('notifications.index');

            Mail::to($user->email, $user->name)->send(new SiamiNotificationMail(
                title: $notification->judul,
                body: $notification->isi,
                targetUrl: $targetUrl,
                institution: Setting::getValue('nama_institusi', 'SMART SIAMI'),
                recipientName: $user->name,
                notificationType: Str::headline(str_replace('_', ' ', $notification->tipe)),
            ));
        } catch (Throwable $exception) {
            Log::warning('Gagal mengirim email notifikasi SMART SIAMI.', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
