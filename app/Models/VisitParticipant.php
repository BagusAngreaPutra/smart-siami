<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitParticipant extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'visit_id',
        'nama_peserta',
        'jabatan',
        'tipe',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
