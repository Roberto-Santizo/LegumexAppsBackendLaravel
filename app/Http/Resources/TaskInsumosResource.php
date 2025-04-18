<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskInsumosResource extends JsonResource
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
            'name' => $this->insumo->name,
            'assigned_quantity' => $this->assigned_quantity,
            'measure' => $this->insumo->measure,
            'used_quantity' => $this->used_quantity
        ];
    }
}
