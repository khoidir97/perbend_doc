<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hubkel extends Model
{
    /**
     * Menggunakan koneksi database kedua (mysql2)
     */
    protected $connection = 'mysql2';

    /**
     * Nama tabel referensi hubungan keluarga di database kedua
     */
    protected $table = 'hubkel_tbl';

    /**
     * Primary Key tabel
     */
    protected $primaryKey = 'KDHUBKEL';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'KDHUBKEL',
        'NMHUBKEL',
    ];
}

