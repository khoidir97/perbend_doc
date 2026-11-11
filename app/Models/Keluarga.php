<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    /**
     * Menggunakan koneksi database kedua (mysql2)
     */
    protected $connection = 'mysql2';

    /**
     * Nama tabel data keluarga di database kedua
     */
    protected $table = 'keluarga';

    /**
     * Tabel keluarga menggunakan primary key majemuk (NIP + KDHUBKEL)
     */
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'NIP',
        'KDHUBKEL',
        'NMKEL',
        'GLRDEPAN',
        'GLRBELAKANG',
        'KDJENKEL',
        'TGLLHR',
        'kdstawin',
        'TGLNIKAH',
        'TGLCERAI',
        'TGLWAFAT',
        'TGLSKS',
        'TATSKS',
        'NOSKS',
        'KDTUNJANG',
        'NIPSUAMIISTRI',
        'UPDSTAMP',
        'INPUTER',
        'PEKERJAAN',
        'KDDATI1',
        'KDDATI2',
    ];

    /**
     * Relasi ke tabel referensi Hubkel
     */
    public function hubkel()
    {
        return $this->belongsTo(Hubkel::class, 'KDHUBKEL', 'KDHUBKEL');
    }
}

