<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskCropWeeklyPlanRequest;
use App\Http\Resources\EmployeeTaskCropResource;
use App\Http\Resources\EmployeeTaskCropSummaryResource;
use App\Http\Resources\TaskCropIncomplemeteAssignmentResource;
use App\Http\Resources\TaskCropResource;
use App\Http\Resources\TaskCropWeeklyPlanDetailsResource;
use App\Models\DailyAssignments;
use App\Models\EmployeeTaskCrop;
use App\Models\Lote;
use App\Models\TaskCropWeeklyPlan;
use App\Models\WeeklyPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TasksCropController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'lote_plantation_control_id' => 'required|string',
            'weekly_plan_id' => 'required|string'
        ]);

        $tasks = TaskCropWeeklyPlan::where('lote_plantation_control_id', $data['lote_plantation_control_id'])->where('weekly_plan_id', $data['weekly_plan_id'])->get();
        return [
            'week' => $tasks->first()->plan->week,
            'finca' => $tasks->first()->plan->finca->name,
            'lote' => $tasks->first()->lotePlantationControl->lote->name,
            'tasks' =>   TaskCropResource::collection($tasks),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTaskCropWeeklyPlanRequest $request)
    {
        $data = $request->validated();
        $lote = Lote::find($data['lote_id']);
        $weekly_plan = WeeklyPlan::find($data['weekly_plan_id']);
        if (!$lote || !$weekly_plan) {
            return response()->json([
                'msg' => "Data not found"
            ], 404);
        }
        if ($lote->finca_id !== $weekly_plan->finca->id) {
            return response()->json([
                'msg' => "Not valid data"
            ], 500);
        }


        try {
            TaskCropWeeklyPlan::create([
                'weekly_plan_id' => $weekly_plan->id,
                'lote_plantation_control_id' => $lote->cdp->id,
                'task_crop_id' => $data['task_crop_id']
            ]);

            return response()->json('Cosecha Creada Correctamente',200);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = TaskCropWeeklyPlan::find($id);

        if (!$task) {
            return response()->json([
                'error' => "Task not found"
            ], 404);
        }

        return response()->json([
            'data' =>  new TaskCropResource($task)
        ]);
    }
    
    public function update(Request $request, string $id)
    {
        $task = TaskCropWeeklyPlan::find($id);
        $task->status = 0;
        $task->save();

        return response()->json([
            'message' => 'Task Closed'
        ]);
    }

    public function CloseAssigment(Request $request, string $id)
    {
        $data = $request->input('data');
        $task = TaskCropWeeklyPlan::find($id);

        $daily_assigment = DailyAssignments::create([
            'task_crop_weekly_plan_id' => $task->id,
            'start_date' => Carbon::now(),
        ]);

        foreach ($data as $item) {
            EmployeeTaskCrop::create([
                'task_crop_weekly_plan_id' => $task->id,
                'employee_id' => $item['emp_id'],
                'code' => $item['code'],
                'name' => $item['name'],
                'daily_assignment_id' => $daily_assigment->id
            ]);
        }


        return response()->json([
            'message' => 'Assignment closed'
        ]);
    }

    public function CloseDailyAssigment(Request $request, string $id)
    {
        $data = $request->validate([
            'task_crop_id' => 'required',
            'plants' => 'required|numeric',
            'assigments' => 'required',
            'lbs_finca' => 'required|numeric'
        ]);

        $task = TaskCropWeeklyPlan::find($id);
        $task_crop_daily_assigment = $task->assignment_today;
        $assigments = $task->employees;
        foreach ($data['assigments'] as $assigment) {
            foreach ($assigments as $assigment_crop) {
                if ($assigment_crop->id === (int)$assigment['id']) {
                    $assigment_crop->lbs = $assigment['lbs'];
                    $assigment_crop->save();
                }
            }
        }
        $task_crop_daily_assigment->end_date = Carbon::now();
        $task_crop_daily_assigment->plants = $data['plants'];
        $task_crop_daily_assigment->lbs_finca = $data['lbs_finca'];
        $task_crop_daily_assigment->save();

        return response()->json([
            'message' => 'Daily Assigment Closed'
        ]);
    }

    public function GetIncompleteAssignments(string $id)
    {
        $task = TaskCropWeeklyPlan::find($id);

        return response()->json([
            'data' => TaskCropIncomplemeteAssignmentResource::collection($task->assigments()->where('lbs_planta', null)->orderBy('start_date')->get())
        ]);
    }

    public function RegisterDailyAssigment(Request $request)
    {

        foreach ($request->all() as $data) {
            $task = DailyAssignments::find($data['id']);
            $task->lbs_planta = $data['lbs_planta'];
            $task->save();
        }


        return response()->json([
            'message' => 'Task Closed Successfully'
        ]);
    }

    public function TaskCropDetail(string $id)
    {
        $task = TaskCropWeeklyPlan::find($id);
        $plan = $task->plan;
        return response()->json([
            'finca' => $plan->finca->name,
            'week' => $plan->week,
            'lote' => $task->lotePlantationControl->lote->name,
            'cdp' => $task->lotePlantationControl->cdp->name,
            'assigments' => new TaskCropWeeklyPlanDetailsResource($task),
            'employees' => EmployeeTaskCropSummaryResource::collection($task->employees)
        ]);
    }

    public function EmployeesAssignment(string $id)
    {
        $task = TaskCropWeeklyPlan::find($id);

        return response()->json([
            'task' => $task->task->name,
            'week' => $task->plan->week,
            'finca' => $task->plan->finca->name,
            'date_assignment' => $task->assignment_today->start_date,
            'data' => EmployeeTaskCropResource::collection($task->employees()->where('daily_assignment_id', $task->assignment_today->id)->get())
        ]);
    }

    public function GetAssignedEmployees(string $id)
    {
        $assigment = DailyAssignments::find($id);

        return response()->json([
            'task' => $assigment->TaskCropWeeklyPlan->task->name,
            'week' => $assigment->TaskCropWeeklyPlan->plan->week,
            'finca' => $assigment->TaskCropWeeklyPlan->plan->finca->name,
            'date_assignment' => $assigment->start_date,
            'data' => EmployeeTaskCropResource::collection($assigment->employees)
        ]);
    }
}
