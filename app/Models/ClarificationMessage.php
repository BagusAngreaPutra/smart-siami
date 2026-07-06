<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClarificationMessage extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'clarification_id',
        'pengirim_id',
        'isi_pesan',
    ];

    public function clarification(): BelongsTo
    {
        return $this->belongsTo(Clarification::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}
