<?php

namespace App\Http\Resources;

use App\Models\BiometricTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskProductionPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $total_hours = 0;
        $total_in_employees = 0;

        if ($this->start_date && $this->end_date) {
            $total_hours = $this->start_date->diffInHours($this->end_date);
        }

        foreach ($this->employees as $employee) {
            $entrance = BiometricTransaction::where('pin', $employee->position)->first();
            if ($entrance) {
                $total_in_employees += 1;
            }
        }

        return [
            'id' => strval($this->id),
            'line' => $this->line->code,
            'sku' => $this->sku->code,
            'total_tarimas' => $this->tarimas,
            'finished_tarimas' => $this->finished_tarimas,
            'operation_date' => $this->operation_date->format('d-m-Y'),
            'start_date' => $this->start_date ? $this->start_date->format('d-m-Y h:i:s A') : null,
            'end_date' => $this->end_date ? $this->end_date->format('d-m-Y h:i:s A') : null,
            'hours' => $this->total_hours,
            'total_hours' => $total_hours,
            'total_in_employees' => $total_in_employees,
            'total_employees' => $this->employees->count(),
            'priority' => $this->priority,
            'available' => $this->available === null ? false : $this->available
        ];
    }
}
