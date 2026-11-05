<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    use HasFactory;

    protected $table = 'dokumen';
    protected $primaryKey = 'dok_id';
    public $timestamps = false;

    protected $fillable = [
        'dok_skpd',
        'dok_kd_satker',
        'dok_pgw_id',
        'dok_file',
        'dok_status',
        'dok_tanggal_verif',
        'dok_verified_by',
        'dok_created_at',
        'dok_created_by',
        'dok_updated_at',
        'dok_updated_by',
        'dok_deleted_at',
        'dok_deleted_by',
    ];

    /**
     * Relasi ke model Skpd
     */
    public function skpd()
    {
        return $this->belongsTo(Skpd::class, 'dok_skpd', 'opd_kode');
    }

    /**
     * Relasi ke model Satker
     */
    public function satker()
    {
        return $this->belongsTo(Satker::class, 'dok_kd_satker', 'stk_kode_satker');
    }

    /**
     * Relasi ke model Pegawai
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'dok_pgw_id', 'pgw_id');
    }

    /**
     * Relasi ke User pembuat
     */
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'dok_created_by', 'id');
    }

    /**
     * Relasi ke User pengubah / verifikator
     */
    public function userUpdated()
    {
        return $this->belongsTo(User::class, 'dok_updated_by', 'id');
    }
}
