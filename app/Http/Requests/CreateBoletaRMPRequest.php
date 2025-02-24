<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBoletaRMPRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cdp' => 'required',
            'producer_id' => 'required',
            'finca_id'=> 'required',
            'inspector_name' => 'required',
            'pilot_name' => 'required',
            'product_id' => 'required',
            'quality_percentage' => 'required',
            'total_baskets' => 'required',
            'transport' => 'required',
            'transport_plate' => 'required',
            'weight' => 'required',
            'basket_id' => 'required',
            'calidad_signature' => 'required'
        ];
    }
}
