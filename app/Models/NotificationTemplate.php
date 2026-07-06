<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    public const CREATED_AT = null;

    protected $fillable = [
        'tipe',
        'judul_template',
        'isi_template',
    ];
}
