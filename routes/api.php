<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CDPController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FincaController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\TasksCropController;
use App\Http\Controllers\TasksLoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WeeklyPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    
    //USUARIOS
    Route::apiResource('/users', UsersController::class);
    Route::get('/users-info/{user}/info', [UsersController::class,'UsersInfo']);
    Route::patch('/users/{user}/status', [UsersController::class, 'updateStatus']);

    //USER
    Route::apiResource('/user',UserController::class);

    //PERMISOS
    Route::get('/permissions/user',[PermissionController::class,'userPermissions']);
    Route::apiResource('/permissions',PermissionController::class);
    
    //ROLES
    Route::get('/roles/user',[RoleController::class,'userRoles']);
    Route::apiResource('/roles',RoleController::class);


    Route::apiResource('/tareas', TareaController::class);
    Route::post('/tareas/upload', [TareaController::class,'UploadTasks']);

    Route::apiResource('/cdps',CDPController::class);
    Route::get('/cdps-list/all',[CDPController::class,'GetAllCDPS']);

    Route::apiResource('/lotes', LoteController::class);

    Route::apiResource('/plans',WeeklyPlanController::class);
    Route::get('/plans-list/all',[WeeklyPlanController::class,'GetAllPlans']);
    
    Route::apiResource('/recipes',RecipeController::class);
    Route::apiResource('/crops',CropController::class);
    Route::apiResource('/fincas',FincaController::class);

    Route::apiResource('/tasks-lotes',TasksLoteController::class);
    Route::get('/tasks-lotes/{id}/details',[TasksLoteController::class,'TaskDetail']);
    Route::post('/tasks-lotes/close-assignment/{id}', [TasksLoteController::class, 'CloseAssigment']);
    Route::patch('/tasks-lotes/close/{id}',[TasksLoteController::class, 'CloseTask']);
    Route::patch('/tasks-lotes/partial-close/close/{id}', [TasksLoteController::class, 'PartialClose']);
    Route::patch('/tasks-lotes/partial-close/open/{id}', [TasksLoteController::class, 'PartialCloseOpen']);
    Route::delete('/tasks-lotes/erase/{id}',[TasksLoteController::class, 'EraseAssignationTask']);

    
    Route::apiResource('/tasks-crops-lotes',TasksCropController::class);
    Route::get('/tasks-crops-lotes/employees/{id}', [TasksCropController::class, 'EmployeesAssignment']);
    Route::get('/tasks-crops-lotes/details/{id}', [TasksCropController::class, 'TaskCropDetail']);
    Route::get('/tasks-crops-lotes/incomplete-assigments/{id}',[TasksCropController::class,'GetIncompleteAssignments']);
    Route::post('/tasks-crops-lotes/close-assignment/{id}', [TasksCropController::class, 'CloseAssigment']);
    Route::post('/tasks-crops-lotes/close-daily-assigment/{id}', [TasksCropController::class, 'CloseDailyAssigment']);
    Route::post('/tasks-crops-lotes/register-daily-assigment',[TasksCropController::class,'RegisterDailyAssigment']);

    Route::apiResource('/employees',EmployeeController::class);
});

//Autenticación
Route::apiResource('/login', AuthController::class);