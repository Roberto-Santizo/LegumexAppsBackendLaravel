<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $fillable = [
        'name',
        'finca_id'
    ];

    public function finca()
    {
        return $this->belongsTo(Finca::class);
    }

    public function cdp()
    {
        return $this->hasOne(LotePlantationControl::class,'lote_id','id')->where('status',1);
    }

    public function lote_cdps()
    {
        return $this->hasMany(LotePlantationControl::class,'lote_id','id');
    }
}
