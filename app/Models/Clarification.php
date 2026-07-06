<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clarification extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'instrument_id',
        'dibuka_oleh',
        'status',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClarificationMessage::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ClarificationEvidence::class);
    }

    public static function statusOptions(): array
    {
        return [
            'terbuka' => 'Terbuka',
            'dijawab' => 'Dijawab',
            'selesai' => 'Selesai',
            'dibuka_kembali' => 'Dibuka Kembali',
        ];
    }
}
