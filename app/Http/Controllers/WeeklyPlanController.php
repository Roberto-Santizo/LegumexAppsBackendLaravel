<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\WeeklyPlan;
use Illuminate\Http\Request;
use App\Imports\WeeklyPlanImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\WeeklyPlanCollection;
use App\Models\LotePlantationControl;
use Carbon\Carbon;

class WeeklyPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $week = $request->input('week') ?? Carbon::now()->weekOfYear;
        $year = $request->input('year') ?? Carbon::now()->year;

        $role = $request->user()->getRoleNames();

        if ($role[0] != 'admin' && $role[0] != 'adminagricola') {
            $permission = $request->user()->permissions()->first();

            $weekly_plans = WeeklyPlan::whereHas('finca', function ($query) use ($permission) {
                $query->where('name', 'LIKE', '%' . $permission->name . '%');
            })->where(function ($query) use ($week) {
                $query->where('week', $week)->orWhere('week', $week - 1);
            })->where('year', $year)->orderBy('created_at', 'DESC')->paginate(10);
        } else {
            $weekly_plans = WeeklyPlan::orderBy('created_at', 'DESC')->paginate(10);
        }

        return new WeeklyPlanCollection($weekly_plans);
    }



    public function GetAllPlans()
    {
        return new WeeklyPlanCollection(WeeklyPlan::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        try {
            Excel::import(new WeeklyPlanImport, $request->file('file'));

            return response()->json([
                'message' => 'Plan Creado Correctamente'
            ]);
        } catch (\Throwable  $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $plan = WeeklyPlan::find($id);
        if (!$plan) {
            return response()->json([
                'errors' => ['El plan no existe']
            ], 404);
        }
        $tasks_by_lote = $plan->tasks->groupBy('lote_plantation_control_id');
        $tasks_crop_by_lote = $plan->tasks_crops->groupBy('lote_plantation_control_id');

        $summary_tasks = $tasks_by_lote->map(function ($group, $key) {
            return [
                'lote' => LotePlantationControl::find($key)->lote->name,
                'lote_plantation_control_id' => strval($key),
                'total_budget' => round($group->sum('budget'), 2),
                'total_workers' => $group->sum('workers_quantity'),
                'total_hours' => round($group->sum('hours'), 2),
                'total_tasks' => $group->count(),
                'finished_tasks' => $group->filter(function ($task) {
                    return !is_null($task->end_date);
                })->count(),
            ];
        })->values();

        $summary_crops = $tasks_crop_by_lote->map(function ($group, $key) {
            $lote_plantation_control = LotePlantationControl::find($key);
            return [
                'id' => strval($key),
                'lote_plantation_control_id' => strval($lote_plantation_control->id),
                'lote' => $lote_plantation_control->lote->name,
            ];
        })->values();

        return response()->json([
            'data' => [
                'id' => strval($plan->id),
                'finca' => $plan->finca->name,
                'week' => $plan->week,
                'year' => $plan->year,
                'summary_tasks' => $summary_tasks,
                'summary_crops' => $summary_crops
            ]
        ]);
    }
}
