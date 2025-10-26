<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ChargeController;
use App\Http\Middleware\ForceJsonResponse;

// Rotas da API com middleware para garantir respostas em JSON
// O middleware ForceJsonResponse evita que erros de validação retornem HTML
Route::group(['middleware' => ForceJsonResponse::class], function () {
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::post('/charges', [ChargeController::class, 'store']);
});

