<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskProductionForCalendarResource;
use App\Http\Resources\TaskProductionOperationDateResource;
use App\Http\Resources\TaskProductionPlanNoOperationDateResource;
use App\Http\Resources\TaskProductionPlanByLineResource;
use App\Http\Resources\TaskProductionPlanSummaryResource;
use App\Http\Resources\WeeklyPlanProductionResource;
use App\Imports\CreateAssignmentsProductionImport;
use App\Imports\WeeklyProductionPlanImport;
use App\Models\Line;
use App\Models\TaskProductionPlan;
use App\Models\WeeklyProductionPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WeeklyProductionPlanController extends Controller
{
    public function index(Request $request)
    {

        if ($request->query('paginated')) {
            $plans_production = WeeklyProductionPlan::paginate(10);
        } else {
            $plans_production = WeeklyProductionPlan::get();
        }

        $plans_production->map(function ($plan) {
            if ($plan->tasks->every(fn($task) => $task->end_date !== null)) {
                $plan->completed = true;
            } else {
                $plan->completed = false;
            }

            return $plan;
        });

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

            $allCompleted = $tasks->every(fn($task) => $task->employees->count() > 0);

            return [
                'id' => strval($line->id),
                'line' => $linea,
                'status' => $allCompleted ? true : false,
                'total_employees' => $line->positions->count(),
                'assigned_employees' => $tasks->first()->employees->count(),
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

            return response()->json('Plan Semanal Creado Correctamente', 200);
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

            return response()->json('Asignaciones Cargadas Correctamente', 200);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    public function GetTasksByLineId(string $weekly_plan_id, string $line_id)
    {
        $weekly_plan = WeeklyProductionPlan::find($weekly_plan_id);
        $today = Carbon::today();

        if (!$weekly_plan) {
            return response()->json([
                'msg' => 'Plan Semanal Not Found'
            ], 404);
        }

        $tasks = $weekly_plan->tasks()->where('line_id', $line_id)->whereDate('operation_date', $today)->whereNot('status', 0)->get();

        return TaskProductionPlanByLineResource::collection($tasks->sortBy('operation_date'));
    }

    public function GetTasksForCalendar(string $weekly_plan_id)
    {
        $weekly_plan = WeeklyProductionPlan::find($weekly_plan_id);

        if (!$weekly_plan) {
            return response()->json([
                'msg' => 'Plan Semanal Not Found'
            ], 404);
        }

        $tasks = $weekly_plan->tasks()
            ->orderBy('priority', 'ASC')
            ->orderBy('line_id', 'ASC')
            ->get();


        return TaskProductionForCalendarResource::collection($tasks);
    }

    public function GetTasksByDate(Request $request, string $weekly_plan_id)
    {
        $date = Carbon::parse($request->query('date'));

        $weekly_plan = WeeklyProductionPlan::find($weekly_plan_id);

        if (!$weekly_plan) {
            return response()->json([
                'msg' => 'Plan Semanal Not Found'
            ], 404);
        }

        $tasks = $weekly_plan->tasks()->orderBy('priority', 'ASC')
            ->whereDate('operation_date', $date)
            ->get();

        $newTasks = TaskProductionPlanSummaryResource::collection($tasks);

        $summary = $tasks->groupBy('line')->map(function ($group) {
            return [
                'id' => strval($group->first()->line->id),
                'line' => $group->first()->line->name,
                'total_hours' => round($group->sum(fn($task) => $task->total_hours ?? 0), 2)
            ];
        })->values();

        return response()->json([
            'summary' => $summary,
            'data' => $newTasks
        ], 200);
    }


    public function GetHoursByDate(string $weekly_plan_id)
    {
        $weekly_plan = WeeklyProductionPlan::find($weekly_plan_id);
        if (!$weekly_plan) {
            return response()->json([
                'msg' => 'Plan Semanal Not Found'
            ], 404);
        }

        $startOfWeek = Carbon::now()
            ->setISODate($weekly_plan->year, $weekly_plan->week)
            ->startOfWeek();

        $groupedTasks = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i)->toDateString();
            $tasks_by_line = $weekly_plan->tasks()->whereDate('operation_date', $date)->get()->groupBy('line_id');

            foreach ($tasks_by_line as $line_id => $tasks) {
                $groupedTasks[] = [
                    'date' => $date,
                    'line_id' => strval($line_id),
                    'total_hours' => $tasks->sum('total_hours')
                ];
            }
        }

        return response()->json([
            'data' => $groupedTasks
        ], 200);
    }

    public function GetAllTasksWeeklyPlan(string $id)
    {
        $weekly_plan = WeeklyProductionPlan::find($id);

        if (!$weekly_plan) {
            return response()->json([
                'msg' => 'Plan Semanal No Encontrado'
            ], 404);
        }

        try {
            $tasks_no_operation_date = $weekly_plan->tasks()->where('operation_date', null)->get();
            $tasks = $weekly_plan->tasks()->whereNot('operation_date', null)->get();

            $events = $tasks->groupBy(function ($item) {
                return $item->operation_date->format('Y-m-d') . '_' . $item->line_id;
            })->map(function ($items, $key) {
                $first = $items->first();
                $lbs = $items->sum('total_lbs');
                return [
                    'id' => strval($first->line_id),
                    'title' => $first->line->name . ' | ' . $lbs . ' LBS',
                    'start' => $first->operation_date->format('Y-m-d'),
                ];
            })->values();


            return response()->json([
                'tasks' => TaskProductionPlanNoOperationDateResource::collection($tasks_no_operation_date),
                'events' => $events
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    public function GetTasksOperationDate(Request $request)
    {
        $date = Carbon::parse($request->query('date'));

        try {
            $tasks = TaskProductionPlan::whereDate('operation_date', $date)->get();

            return TaskProductionOperationDateResource::collection($tasks);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }
}
