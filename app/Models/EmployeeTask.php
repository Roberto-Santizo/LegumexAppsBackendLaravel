<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTask extends Model
{
    protected $fillable = [
        'name',
        'code',
        'task_weekly_plan_id',
        'employee_id',
    ];
}
