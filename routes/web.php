<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlquileresController;
use App\Http\Controllers\PagosController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {return view('dashboard');})->name('dashboard');
    Route::get('/inquilinos', function () {return view('inquilinos');})->name('inquilinos');
    
    Route::get('/propiedades', [AlquileresController::class, 'cargaAdminPropiedades'])->name('propiedades');
    Route::prefix('propiedades')->group(function () {
        Route::get('/listado', [AlquileresController::class, 'listaPropiedad']);
        Route::post('/procesar', [AlquileresController::class, 'procesaPropiedad']);
        Route::delete('/borrar', [AlquileresController::class, 'borraPropiedad']);
    });

    Route::get('/contratos', [AlquileresController::class, 'cargaAdminContratos'])->name('contratos');
    Route::prefix('contratos')->group(function () {
        Route::get('/listado', [AlquileresController::class, 'listaContrato']);
        Route::post('/procesar', [AlquileresController::class, 'procesaContrato']);
        Route::delete('/borrar', [AlquileresController::class, 'borraContrato']);
    });

    Route::get('/pagos', [PagosController::class, 'cargaAdminPagos'])->name('pagos');
    Route::prefix('pagos')->group(function () {
        Route::get('/listado', [PagosController::class, 'listaPagos']);
        Route::post('/procesar', [PagosController::class, 'procesaPago']);
        Route::delete('/borrar', [PagosController::class, 'borraPago']);
    });

    
});