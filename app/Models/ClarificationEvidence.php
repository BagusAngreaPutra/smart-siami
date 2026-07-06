<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClarificationEvidence extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'clarification_evidences';

    protected $fillable = [
        'clarification_id',
        'nama_dokumen',
        'tipe_sumber',
        'path_file',
        'url_tautan',
        'diunggah_oleh',
    ];

    public function clarification(): BelongsTo
    {
        return $this->belongsTo(Clarification::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }
}
