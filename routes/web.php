<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Redirect;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Redirect::to('https://empresta.com.br/');
});

Route::get('instituicoes', [ApiController::class, 'getInstituicoes']);
Route::get('convenios', [ApiController::class, 'getConvenios']);

Route::post('simular_credito', [ApiController::class, 'simularCredito']);