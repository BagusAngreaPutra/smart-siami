<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'tipe',
        'lokasi_atau_tautan',
        'agenda',
        'catatan_wawancara',
        'catatan_observasi',
        'kesimpulan',
        'status',
        'konfirmasi_auditee',
        'waktu_konfirmasi_auditee',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'konfirmasi_auditee' => 'boolean',
            'waktu_konfirmasi_auditee' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(VisitParticipant::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(VisitAttachment::class);
    }

    public static function statusOptions(): array
    {
        return [
            'belum_dijadwalkan' => 'Belum Dijadwalkan',
            'terjadwal' => 'Terjadwal',
            'selesai' => 'Selesai',
            'berita_acara_disetujui' => 'Berita Acara Disetujui',
        ];
    }

    public static function tipeOptions(): array
    {
        return [
            'lapangan' => 'Lapangan',
            'daring' => 'Daring',
        ];
    }
}
