<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';
    protected $primaryKey = 'pgw_id';
    public $timestamps = false;

    protected $fillable = [
        'pgw_nama',
        'pgw_nip',
        'pgw_kd_skpd',
        'pgw_kd_satker',
        'pgw_created_at',
        'pgw_created_by',
        'pgw_updated_at',
        'pgw_updated_by',
        'pgw_deleted_at',
        'pgw_deleted_by',
    ];

    /**
     * Relasi ke model Skpd
     */
    public function skpd()
    {
        return $this->belongsTo(Skpd::class, 'pgw_kd_skpd', 'opd_kode');
    }

    /**
     * Relasi ke model Satker
     */
    public function satker()
    {
        return $this->belongsTo(Satker::class, 'pgw_kd_satker', 'stk_kode_satker');
    }

    /**
     * Relasi ke User pembuat
     */
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'pgw_created_by', 'id');
    }

    /**
     * Relasi ke User pengubah / verifikator
     */
    public function userUpdated()
    {
        return $this->belongsTo(User::class, 'pgw_updated_by', 'id');
    }
}
