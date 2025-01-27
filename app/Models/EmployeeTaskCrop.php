<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTaskCrop extends Model
{
    protected $fillable =[
        'name',
        'code',
        'task_crop_weekly_plan_id',
        'employee_id',
        'lbs'
    ];
}
