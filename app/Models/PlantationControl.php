<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantationControl extends Model
{
    protected $fillable = [
        'name',
        'crop_id',
        'recipe_id',
        'density',
        'size',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

}
