<?php

namespace App\Http\Resources;

use App\Models\BiometricTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BiometricEmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => strval($this->temp_id),
            'name' => $this->name,
            'code' => $this->last_name,
            'position' => $this->pin,
            'column_id' => strval(2),
            'active' => 1
        ];
    }
}
