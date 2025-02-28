<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskProductionPlanDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'line' => $this->line->code,
            'operation_date' => $this->operation_date,
            'total_tarimas' => $this->skus->sum('tarimas'),
            'skus' => TaskProductionSKUPlanResource::collection($this->skus)
        ];
    }
}
