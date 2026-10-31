<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlquileresController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/recover', [AuthController::class, 'passwordRecover']);
Route::post('/validate', [AuthController::class, 'validateCodeRecover']);
Route::post('/changepassword', [AuthController::class, 'changePasswordRecover']);
Route::get('/correo', function () {
    return view('correos.recovery', ['codigo' => 56516156]);
})->name('correo.recovery');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/perfil', [AuthController::class, 'miProfile'])->name('perfil');
    Route::post('/perfil', [AuthController::class, 'procesaProfile']);
    Route::post('/resetpass', [AuthController::class, 'resetPass']);

    Route::prefix('alquileres')->name('alquileres.')->group(function () {
        Route::get('/', fn () => redirect()->route('alquileres.dashboard'))->name('index');

        Route::get('/dashboard', [AlquileresController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/datos', [AlquileresController::class, 'dashboardDatos'])->name('dashboard.datos');

        Route::get('/propiedades', [AlquileresController::class, 'cargaAdminPropiedades'])->name('propiedades');
        Route::prefix('propiedades')->group(function () {
            Route::get('/listado', [AlquileresController::class, 'listaPropiedad'])->name('propiedades.listado');
            Route::post('/procesar', [AlquileresController::class, 'procesaPropiedad'])->name('propiedades.procesar');
            Route::delete('/borrar', [AlquileresController::class, 'borraPropiedad'])->name('propiedades.borrar');
        });

        Route::get('/inquilinos', [AlquileresController::class, 'cargaAdminInquilinos'])->name('inquilinos');
        Route::prefix('inquilinos')->group(function () {
            Route::get('/listado', [AlquileresController::class, 'listaInquilino'])->name('inquilinos.listado');
            Route::post('/procesar', [AlquileresController::class, 'procesaInquilino'])->name('inquilinos.procesar');
            Route::delete('/borrar', [AlquileresController::class, 'borraInquilino'])->name('inquilinos.borrar');
        });

        Route::get('/contratos', [AlquileresController::class, 'cargaAdminContratos'])->name('contratos');
        Route::prefix('contratos')->group(function () {
            Route::get('/listado', [AlquileresController::class, 'listaContrato'])->name('contratos.listado');
            Route::post('/procesar', [AlquileresController::class, 'procesaContrato'])->name('contratos.procesar');
            Route::delete('/borrar', [AlquileresController::class, 'borraContrato'])->name('contratos.borrar');
        });

        Route::get('/pagos', [AlquileresController::class, 'cargaAdminPagos'])->name('pagos');
        Route::prefix('pagos')->group(function () {
            Route::get('/listado', [AlquileresController::class, 'listaPagos'])->name('pagos.listado');
            Route::post('/procesar', [AlquileresController::class, 'procesaPago'])->name('pagos.procesar');
            Route::delete('/borrar', [AlquileresController::class, 'borraPago'])->name('pagos.borrar');
        });

        Route::prefix('formatos')->name('formatos.')->group(function () {
            Route::get('/contrato', [AlquileresController::class, 'listaContratoFormato'])->name('contrato');
            Route::get('/descargar-pdf', [AlquileresController::class, 'descargarContratoPDF'])->name('descargar-pdf');
            Route::get('/descargar-recibo', [AlquileresController::class, 'descargarReciboPDF'])->name('descargar-recibo');
            Route::get('/editarContrato', [AlquileresController::class, 'editarContratoVista'])->name('editar');
            Route::post('/editarContrato', [AlquileresController::class, 'editarContrato']);
            Route::post('/vista-previa', [AlquileresController::class, 'vistaPrevia'])->name('vista-previa');
            Route::post('/restablecer', [AlquileresController::class, 'restablecerContrato'])->name('restablecer');
        });
    });
});
