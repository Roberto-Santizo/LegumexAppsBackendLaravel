<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskProductionPlan extends Model
{
    protected $casts = [
        'operation_date' => 'datetime',
        'start_date'  => 'datetime',
        'end_date'  => 'datetime',
    ];
    protected $fillable = [
        'line_id',
        'weekly_production_plan_id',
        'operation_date',
        'total_hours',
        'start_date',
        'end_date',
        'tarimas',
        'sku_id',
        'status'
    ];

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function weeklyPlan()
    {
        return $this->belongsTo(WeeklyProductionPlan::class,'weekly_production_plan_id','id');
    }

    public function employees()
    {
        return $this->hasMany(TaskProductionEmployee::class,'task_p_id','id');
    }

    public function sku()
    {
        return $this->belongsTo(StockKeepingUnit::class);
    }

    public function performances()
    {
        return $this->hasMany(TaskProductionPerformance::class);
    }

}
