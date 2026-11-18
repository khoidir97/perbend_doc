<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wafat extends Model
{
    /**
     * Menggunakan koneksi database kedua (mysql2)
     */
    protected $connection = 'mysql2';

    /**
     * Nama tabel data keluarga di database kedua
     */
    protected $table = 'twafat';

    /**
     * Tabel keluarga menggunakan primary key majemuk (NIP + KDHUBKEL)
     */
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'NIP',
        'KDHUBKEL',
        'TGLWAFAT',
        'NOSURAT',
        'TGLSURAT',
        'PENERBIT',
        'JMLTERUSAN',
        'TGLSTOP',
        'KDHUBKELPENERIMA',
        'COVID',
        'KDDATI1',
        'KDDATI2'

    ];

    /**
     * Relasi ke tabel referensi Hubkel
     */
    public function hubkel()
    {
        return $this->belongsTo(Hubkel::class, 'KDHUBKEL', 'KDHUBKEL');
    }
}
