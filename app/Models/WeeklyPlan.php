<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyPlan extends Model
{
    public function finca()
    {
        return $this->belongsTo(Finca::class);
    }
}
