<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    use HasFactory;

    protected $table = 'evidences';

    protected $fillable = [
        'self_assessment_id',
        'follow_up_id',
        'nama_dokumen',
        'jenis_dokumen',
        'tipe_sumber',
        'path_file',
        'url_tautan',
        'ukuran_file',
        'tahun_dokumen',
        'deskripsi',
        'uploaded_by',
        'instrumen_terkait',
        'instrument_ids',
        'status_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'self_assessment_id' => 'integer',
            'follow_up_id' => 'integer',
            'instrument_ids' => 'array',
            'ukuran_file' => 'integer',
            'tahun_dokumen' => 'integer',
            'uploaded_by' => 'integer',
        ];
    }

    public function selfAssessment(): BelongsTo
    {
        return $this->belongsTo(SelfAssessment::class);
    }

    public function followUp(): BelongsTo
    {
        return $this->belongsTo(FollowUp::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function statusVerifikasiOptions(): array
    {
        return [
            'belum_diperiksa' => 'Belum Diperiksa',
            'valid' => 'Valid',
            'perlu_klarifikasi' => 'Perlu Klarifikasi',
        ];
    }
}
