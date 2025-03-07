<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeAssignmentRequest;
use App\Http\Resources\TaskProductionPlanDetailsResource;
use App\Http\Resources\TaskProductionPlanResource;
use App\Models\Line;
use App\Models\TaskProductionEmployee;
use App\Models\TaskProductionEmployeesBitacora;
use App\Models\TaskProductionPlan;
use App\Models\TaskProductionTimeout;
use App\Models\Timeout;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class TaskProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks_production_plan = TaskProductionPlan::all();
        return TaskProductionPlanResource::collection($tasks_production_plan);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task_production_plan = TaskProductionPlan::find($id);

        if (!$task_production_plan) {
            return response()->json([
                'msg' => 'Task Production Plan Not Found'
            ], 404);
        }

        return new TaskProductionPlanDetailsResource($task_production_plan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'line_id' => 'required',
            'operation_date' => 'required',
            'total_hours' => 'required',
        ]);

        $task_production_plan = TaskProductionPlan::find($id);

        if (!$task_production_plan) {
            return response()->json([
                'msg' => 'Task Production Plan Not Found'
            ], 404);
        }

        try {
            $task_production_plan->update($data);

            return response()->json([
                'msg' => 'Task Production Plan Updated Successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    public function AddTimeOut(Request $request, string $id)
    {
        $data = $request->validate([
            'timeout_id' => 'required'
        ]);

        $task_production_plan = TaskProductionPlan::find($id);

        if (!$task_production_plan) {
            return response()->json([
                'msg' => 'Task Production Plan Not Found'
            ], 404);
        }

        $timeout = Timeout::find($data['timeout_id']);


        if (!$timeout) {
            return response()->json([
                'msg' => 'Timeout Not Found'
            ], 404);
        }

        try {
            TaskProductionTimeout::create([
                'timeout_id' => $data['timeout_id'],
                'task_p_id' => $task_production_plan->id,
            ]);

            return response()->json([
                'msg' => 'Updated Successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);;
        }
    }

    public function Assign(Request $request)
    {
        $data = $request->validate([
            'data' => 'required'
        ]);

        try {
            $groupedByLine = collect($data['data'])->groupBy('line');
            foreach ($groupedByLine as $key => $employees) {
                $line = Line::where('code', $key)->first();
                if (!$line) {
                    return response()->json([
                        'msg' => 'Line Not Found'
                    ], 404);
                }

                $task = $line->tasks->last();

                foreach ($employees as $employee) {
                    TaskProductionEmployee::create([
                        'task_p_id' => $task->id,
                        'name' => $employee['name'],
                        'code' => $employee['code'],
                        'position' => $employee['position'],
                    ]);
                }

                return response()->json([
                    'msg' => 'Created Successfully'
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }

    public function ChangeAssignment(ChangeAssignmentRequest $request, string $id)
    {
        $data = $request->validated();

        $assignment = TaskProductionEmployee::find($data['assignment_id'])->first();

        if (!$assignment) {
            return response()->json([
                'msg' => 'Assignment Not Found'
            ], 400);
        }

        try {
            TaskProductionEmployeesBitacora::create([
                "assignment_id" => $assignment->id,
                "original_name"=> $assignment->name,
                "original_code"=> $assignment->code,
                "original_position"=> $assignment->position,
                "new_name"=> $data['new_name'],
                "new_code"=> $data['new_code'],
                "new_position"=> $data['new_position']
            ]);
            
            $assignment->name = $data['new_name'];
            $assignment->code = $data['new_code'];
            $assignment->position = $data['new_position'];
            $assignment->save();


            return response()->json([
                'msg' => 'Updated Successfully' 
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ],400);
        }
    }
}
