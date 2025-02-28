<?php

namespace App\Http\Controllers;

use App\Imports\WeeklyProductionPlanImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WeeklyProductionPlanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        try {
            Excel::import(new WeeklyProductionPlanImport, $request->file('file'));

            return response()->json([
                'msg' => 'Plan Creado Correctamente'
            ],200);
        } catch (\Throwable  $th) {
            return response()->json([
                'msg' => $th->getMessage()
            ], 500);
        }
    }
}
