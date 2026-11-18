<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pegawai extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'mysql';

    protected $table = 'pegawai';
    protected $primaryKey = 'pgw_id';
    public $timestamps = false;

    protected $fillable = [
        'pgw_nama',
        'pgw_nip',
        'pgw_kd_skpd',
        'pgw_kd_satker',
        'pgw_password',
        'pgw_created_at',
        'pgw_created_by',
        'pgw_updated_at',
        'pgw_updated_by',
        'pgw_deleted_at',
        'pgw_deleted_by',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi model
     */
    protected $hidden = [
        'pgw_password',
    ];

    /**
     * Mengambil password untuk autentikasi Laravel
     */
    public function getAuthPassword()
    {
        return $this->pgw_password;
    }

    public function getAuthPasswordName()
    {
        return 'pgw_password';
    }

    /**
     * Nonaktifkan remember_token jika kolom tidak ada di tabel pegawai
     */
    public function getRememberTokenName()
    {
        return '';
    }

    /**
     * Accessor aliases untuk kompatibilitas dengan pemanggilan Auth::user()
     */
    public function getNameAttribute()
    {
        return $this->pgw_nama;
    }

    public function getEmailAttribute()
    {
        return $this->pgw_nip;
    }

    public function getKdSkpdAttribute()
    {
        return $this->pgw_kd_skpd;
    }

    public function getKdSatkerAttribute()
    {
        return $this->pgw_kd_satker;
    }

    public function getIdAttribute()
    {
        return $this->pgw_id;
    }

    public function getRoleAttribute()
    {
        if (isset($this->attributes['role'])) {
            return (int) $this->attributes['role'];
        }
        if (isset($this->attributes['pgw_role'])) {
            return (int) $this->attributes['pgw_role'];
        }

        // BPKAD (036) sebagai administrator sistem (role 0)
        if (!empty($this->pgw_kd_skpd) && trim($this->pgw_kd_skpd) === '036') {
            return 0;
        }

        return 1;
    }

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
