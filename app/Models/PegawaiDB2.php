<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PegawaiDB2 extends Model
{
    /**
     * Menggunakan koneksi database kedua (mysql2)
     */
    protected $connection = 'mysql2';

    /**
     * Nama tabel data keluarga di database kedua
     */
    protected $table = 'mstpegawai';

    /**
     * Tabel keluarga menggunakan primary key majemuk (NIP + KDHUBKEL)
     */
    protected $primaryKey = 'NIP';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];
}
