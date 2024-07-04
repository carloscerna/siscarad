<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\CalificacionesPorAsignaturaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PdfRPAController;
use App\Http\Controllers\PdfRPGController;
use App\Http\Controllers\PdfRLyPController;
use App\Http\Controllers\asistenciaDiariaController;
use App\Http\Controllers\PdfRPGEstudianteController;

// emailes
use App\Mail\BoletaEstudiantes;
use Illuminate\Support\Facades\Mail;

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
    Route::resource('matricula', MatriculaController::class);
    Route::resource("asistenciaDiaria",asistenciaDiariaController::class);
    //Route::get('gradoseccion/{id}', 'CalificacionesPorAsignaturaController@getGradoSeccion');
    //Route::get('buscarGradoSeccion','AsignaturaController@getGradoSeccion');
    Route::post("getGradoSeccion", "App\Http\Controllers\CalificacionesPorAsignaturaController@getGradoSeccion")->name('getGradoSeccion');
    Route::post("getGradoSeccionAsignaturas", "App\Http\Controllers\CalificacionesPorAsignaturaController@getGradoSeccionAsignaturas")->name('getGradoSeccionAsignaturas');
    Route::post("getGradoSeccionCalificacionesAsignaturas", "App\Http\Controllers\CalificacionesPorAsignaturaController@getGradoSeccionCalificacionesAsignaturas")->name('getGradoSeccionCalificacionesAsignaturas');
    Route::post("getPeriodo", "App\Http\Controllers\CalificacionesPorAsignaturaController@getPeriodo")->name('getPeriodo');
    Route::PUT("getActualizarCalificacion", "App\Http\Controllers\CalificacionesPorAsignaturaController@getActualizarCalificacion")->name('getActualizarCalificacion');
    // para HomeController
    Route::post("getGradoSeccion", "App\Http\Controllers\HomeController@getGradoSeccion")->name('getGradoSeccion');
    Route::post("getGradoSeccionIndicadores", "App\Http\Controllers\HomeController@getGradoSeccionIndicadores")->name('getGradoSeccionIndicadores');
    Route::post("getGradoSeccionPresentes", "App\Http\Controllers\HomeController@getGradoSeccionPresentes")->name('getGradoSeccionPresentes');

    // Para MatriculaController
    Route::post("getGradoSeccionMatricula", "App\Http\Controllers\MatriculaController@getGradoSeccionMatricula")->name('getGradoSeccionMatricula');
    // Para MatriculaControllerTodos
    Route::post("getGradoSeccionMatriculaTodos", "App\Http\Controllers\MatriculaController@getGradoSeccionMatriculaTodos")->name('getGradoSeccionMatriculaTodos');
    // para matriculaBuscarDatos.
    Route::post("getGradoSeccionMatriculaBuscar", "App\Http\Controllers\MatriculaController@getGradoSeccionMatriculaBuscar")->name('getGradoSeccionMatriculaBuscar');
    // para matriculaBuscarDatos.
    Route::post("getDatosResponsables", "App\Http\Controllers\MatriculaController@getDatosResponsables")->name('getDatosResponsables');
    // para matricularGuardar
    Route::post("getDatosMatriculaGuardar", "App\Http\Controllers\MatriculaController@getDatosMatriculaGuardar")->name('getDatosMatriculaGuardar');
    // para matriculaBuscarDatosMatriculados.
    Route::post("getGradoSeccionMatriculadosBuscar", "App\Http\Controllers\MatriculaController@getGradoSeccionMatriculadosBuscar")->name('getGradoSeccionMatriculadosBuscar');
    /////////////////////////////////////////
    //** REPORTES */
    ////////////////////////////////////////
    // REPORTES boleta de califiación
    Route::get('pdf/{id}', [PdfController::class, 'index']);
    // REPORTES boleta de califiación por asignatura
    Route::get('pdfRPA/{id}', [PdfRPAController::class, 'index']);
    // REPORTES boleta de califiación por asignatura
    Route::get('pdfRPG/{id}', [PdfRPGController::class, 'index']);
    // REPORTES para Licencias y Permisos
    Route::get('pdfRLyP/{id}', [PdfRLyPController::class,'index']);
    // REPORTES INFORMACIÓN DEL ESTUDIANTE Y ENCARGADO.
    Route::get('pdfRPGEstudiante/{id}', ['\App\Http\Controllers\PdfRPGEstudianteController::class', 'index']);
    // helpers
    Route::resource('funcion','PdfController');
    Route::resource('funcion','PdfRPAController');
    Route::resource('funcion','PdfRPGController');
    Route::resource('funcion','PdfRLyPController');
    Route::resource('funcion','matricula/index');
    // Emails
      //Route::get('/boleta', function(){
        //  return new BoletaEstudiantes("yonYOn");
      //});

});