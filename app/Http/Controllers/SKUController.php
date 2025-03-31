<?php

namespace App\Http\Controllers;

use App\Http\Resources\SKUResource;
use App\Http\Resources\SKUSelectResource;
use App\Models\StockKeepingUnit;
use Illuminate\Http\Request;

class SKUController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $skus = StockKeepingUnit::paginate(10);

        return SKUResource::collection($skus);
    }

    public function GetAllSKU()
    {
        $skus = StockKeepingUnit::select('id', 'code')->get();

        return SKUSelectResource::collection($skus);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|unique:stock_keeping_units,code',
            'product_name' => 'required',
            'presentation' => 'sometimes',
            'boxes_pallet' => 'sometimes',
            'config_box' => 'sometimes',
            'config_bag' => 'sometimes',
            'config_inner_bag' => 'sometimes',
            'pallets_container' => 'sometimes',
            'hours_container' => 'sometimes',
            'client_name' => 'required'
        ]);

        try {
            StockKeepingUnit::create($data);

            return response()->json('SKU Creado Correctamente', 200);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }
}
