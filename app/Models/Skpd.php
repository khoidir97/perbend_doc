<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skpd extends Model
{
    use HasFactory;

    protected $table = 'skpd';
    protected $primaryKey = 'opd_id';
    public $timestamps = false;

    protected $fillable = [
        'opd_kode',
        'opd_nama',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'kd_skpd', 'opd_kode');
    }

    public function satkers()
    {
        return $this->hasMany(Satker::class, 'stk_kode_opd', 'opd_kode');
    }
}
