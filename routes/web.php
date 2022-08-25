<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\CalificacionesPorAsignaturaController;
use App\Http\Controllers\PdfController;

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
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware'=> ['auth']], function(){
    Route::resource('roles', RolController::class);
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('asignaturas', AsignaturaController::class);
    Route::resource('calificacionporasignatura', CalificacionesPorAsignaturaController::class);
    //Route::get('gradoseccion/{id}', 'CalificacionesPorAsignaturaController@getGradoSeccion');
    //Route::get('buscarGradoSeccion','AsignaturaController@getGradoSeccion');
    Route::post("getGradoSeccion", "App\Http\Controllers\CalificacionesPorAsignaturaController@getGradoSeccion")->name('getGradoSeccion');
    Route::post("getGradoSeccionAsignaturas", "App\Http\Controllers\CalificacionesPorAsignaturaController@getGradoSeccionAsignaturas")->name('getGradoSeccionAsignaturas');
    Route::post("getGradoSeccionCalificacionesAsignaturas", "App\Http\Controllers\CalificacionesPorAsignaturaController@getGradoSeccionCalificacionesAsignaturas")->name('getGradoSeccionCalificacionesAsignaturas');
    Route::PUT("getActualizarCalificacion", "App\Http\Controllers\CalificacionesPorAsignaturaController@getActualizarCalificacion")->name('getActualizarCalificacion');


    // REPORTES
    Route::get('pdf/{id}', [PdfController::class, 'index']);

    // helpers
    Route::resource('funcion','PdfController');
});