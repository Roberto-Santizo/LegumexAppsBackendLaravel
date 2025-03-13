<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskProductionPlanResource;
use App\Http\Resources\WeeklyPlanProductionResource;
use App\Imports\CreateAssignmentsProductionImport;
use App\Imports\WeeklyProductionPlanImport;
use App\Models\Line;
use App\Models\TaskProductionPlan;
use App\Models\WeeklyProductionPlan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WeeklyProductionPlanController extends Controller
{
    public function index()
    {
        $plans_production = WeeklyProductionPlan::paginate(10);
        return WeeklyPlanProductionResource::collection($plans_production);
    }

    public function show(string $id)
    {
        $weekly_plan = WeeklyProductionPlan::find($id);

        $groupedTasks = $weekly_plan->tasks->groupBy(function ($task) {
            return $task->line->code;
        });

        $lineas = $groupedTasks->keys()->map(function ($linea) {
            $line = Line::where('code', $linea)->first();
            $tasks = TaskProductionPlan::where('line_id', $line->id)->get();

            $allCompleted = $tasks->every(fn($task) => $task->status == 1);

            return [
                'id' => strval($line->id),
                'line' => $linea,
                'status' => $allCompleted ? true : false
            ];
        });

        return response()->json([
            'data' => $lineas
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        try {
            Excel::import(new WeeklyProductionPlanImport, $request->file('file'));

            return response()->json([
                'msg' => 'Plan Creado Correctamente'
            ], 200);
        } catch (\Throwable  $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    public function createAssigments(Request $request, string $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        try {
            Excel::import(new CreateAssignmentsProductionImport($id), $request->file('file'));

            return response()->json([
                'msg' => 'Assignments Created Successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    public function getAllTasks(string $weekly_plan_id, string $line_id)
    {
        $weekly_plan = WeeklyProductionPlan::find($weekly_plan_id);

        if (!$weekly_plan) {
            return response()->json([
                'msg' => 'Plan Semanal Not Found'
            ], 404);
        }

        return TaskProductionPlanResource::collection($weekly_plan->tasks()->where('line_id',$line_id)->get());
    }
}
