<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_name',
        'actor_email',
        'actor_role',
        'event',
        'action',
        'description',
        'route_name',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'subject_type',
        'subject_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    public static function eventOptions(): array
    {
        return [
            'created' => 'Penambahan',
            'updated' => 'Perubahan',
            'deleted' => 'Penghapusan',
            'authentication' => 'Autentikasi',
            'action' => 'Aksi Sistem',
        ];
    }

    public function eventLabel(): string
    {
        return self::eventOptions()[$this->event] ?? ucfirst($this->event);
    }

    public function eventTone(): string
    {
        return match ($this->event) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'authentication' => 'info',
            default => 'neutral',
        };
    }

    public function actorRoleLabel(): string
    {
        return match ($this->actor_role) {
            'admin' => 'Admin',
            'auditor' => 'Auditor',
            'auditee' => 'Auditee',
            default => $this->actor_role ? ucfirst($this->actor_role) : 'Sistem',
        };
    }
}
