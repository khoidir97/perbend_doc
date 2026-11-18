<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorisJiwa extends Model
{
    /**
     * Menggunakan koneksi database kedua (mysql2)
     */
    protected $connection = 'mysql2';

    /**
     * Nama tabel data keluarga di database kedua
     */
    protected $table = 'historis_jiwa';

    /**
     * Tabel keluarga menggunakan primary key majemuk (NIP + KDHUBKEL)
     */
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'TMTGAJI',
        'NIP',
        'JISTRI',
        'JANAK',
        'KETERANGAN',
        'TGLUPDATE',
        'KDDATI1',
        'KDDATI2',
        'kdstawin',
        'TMT'

    ];


}
