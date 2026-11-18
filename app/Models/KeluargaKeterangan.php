<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeluargaKeterangan extends Model
{
    /**
     * Menggunakan koneksi database kedua (mysql2)
     */
    protected $connection = 'mysql2';

    /**
     * Nama tabel data keluarga di database kedua
     */
    protected $table = 'keluarga_keterangan';

    /**
     * Tabel keluarga menggunakan primary key majemuk (NIP + KDHUBKEL)
     */
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'nip',
        'kdhubkel',
        'kddati1',
        'kddati2',
        'nosuratnikah',
        'noaktalahir',
        'nosuratkematian',
        'updater',
        'updstamp'

    ];
}
