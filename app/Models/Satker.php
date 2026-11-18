<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satker extends Model
{

    protected $connection = 'mysql';

    protected $table = 'satker';
    protected $primaryKey = 'stk_id';
    public $timestamps = false;

    protected $fillable = [
        'stk_kode_satker',
        'stk_kode_opd',
        'stk_nama'
    ];

    public function skpd()
    {
        return $this->belongsTo(Skpd::class, 'stk_kode_opd', 'opd_kode');
    }
}
