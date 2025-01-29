<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCropResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $flag = false;
        $employees_without_lbs = false;

        foreach($this->assigments as $assigment){
            if($assigment->lbs_planta == null){
                $flag = true;
            }

            // if($assigment->)
        }

        return [
            'id' => strval($this->id),
            'task' => $this->task->name,
            'finca_id' => strval($this->plan->finca->id),
            'cultivo' => $this->task->crop->name,
            'assigment_today' => ($this->assignment_today) ? true : false,
            'finished_assigment_today' => $this->assignment_today?->end_date && $this->assignment_today?->lbs_finca ? true : false,
            'closed' => $this->status ? false : true,
            'incomplete' => $flag
        ];
    }
}
