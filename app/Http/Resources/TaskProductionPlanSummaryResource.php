<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskProductionPlanSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
            'priority' => $this->priority,
        ];
    }
}
