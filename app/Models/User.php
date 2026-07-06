<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nip_nidn',
        'email',
        'phone',
        'profile_photo_path',
        'profile_photo_focus_x',
        'profile_photo_focus_y',
        'password',
        'role',
        'unit_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'profile_photo_focus_x' => 'integer',
            'profile_photo_focus_y' => 'integer',
            'password' => 'hashed',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function leadAuditAssignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class, 'lead_auditor_id');
    }

    public function auditAssignments(): BelongsToMany
    {
        return $this->belongsToMany(AuditAssignment::class, 'assignment_auditors', 'auditor_id', 'assignment_id')
            ->withPivot('peran_dalam_tim')
            ->withTimestamps();
    }
}
