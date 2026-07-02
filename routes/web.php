<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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
use App\Http\Controllers\PrematriculaController;
use App\Http\Controllers\AlumnoInformacionController;
use App\Http\Controllers\AlumnoBitacoraController;


// emailes
use App\Mail\BoletaEstudiantes;
use App\Mail\CorreoConAdjunto;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderShipped;

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

use App\Http\Controllers\AlumnosDemeritosController;

Route::middleware(['auth'])->group(function () {
    // Pantalla del formulario de captura masiva
    Route::get('/consolidado-conducta', [AlumnosDemeritosController::class, 'index'])->name('consolidado.index');
    
    // AJAX: Carga la matrícula y totales existentes de la sección y mes seleccionados
    Route::get('/consolidado-conducta/cargar/{id_encargado_grado}/{mes}', [AlumnosDemeritosController::class, 'cargarDatosMes']);
    
    // AJAX: Guarda o actualiza los datos recolectados (Mecanismo Upsert)
    Route::post('/consolidado-conducta/guardar', [AlumnosDemeritosController::class, 'guardarMasivo']);
    
    // AJAX: Resetea o elimina las estadísticas del mes seleccionado
    Route::delete('/consolidado-conducta/eliminar/{id_encargado_grado}/{mes}', [AlumnosDemeritosController::class, 'eliminarMes']);

Route::get('/consolidado-conducta/verificar-meses/{id_encargado_grado}', [AlumnosDemeritosController::class, 'verificarMesesSeccion']);

});

Route::get('/', function () {
    //return view('welcome');
    return redirect()->route('login');
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

// Ruta para mostrar la vista principal (podría ser un listado de alumnos para seleccionar uno)
    Route::get('estudiante/informacion', [AlumnoInformacionController::class, 'index'])
        ->name('estudiante.informacion.index');

    // Ruta para mostrar el formulario de un alumno específico
    Route::get('estudiante/informacion/{id_alumno}/editar', [AlumnoInformacionController::class, 'edit'])
        ->name('estudiante.informacion.edit');

    // Ruta para procesar la actualización de la foto, firma y datos del encargado
    Route::put('estudiante/informacion/{id_alumno}', [AlumnoInformacionController::class, 'update'])
        ->name('estudiante.informacion.update');

    Route::resource('roles', RolController::class);
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('asignaturas', AsignaturaController::class);
    Route::resource('calificacionporasignatura', CalificacionesPorAsignaturaController::class);
    Route::resource('matricula', MatriculaController::class);
    Route::resource("asistenciaDiaria",asistenciaDiariaController::class);
    //Route::get('gradoseccion/{id}', 'CalificacionesPorAsignaturaController@getGradoSeccion');
    //Route::get('buscarGradoSeccion','AsignaturaController@getGradoSeccion');
    // para Asistencia Diaria
    Route::post("getGradoSeccionAsistenciaDiaria", "App\Http\Controllers\AsistenciaDiariaController@getGradoSeccionAsistenciaDiaria")->name('getGradoSeccionAsistenciaDiaria');
    //para Calificaciones por Asignatura.
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
    Route::get('/prematricula/{id}', [PrematriculaController::class, 'index']);
    /////////////////////////////////////////
    //** REPORTES */
    ////////////////////////////////////////
    // REPORTES boleta de califiación
    Route::get('pdf/{id}', [PdfController::class, 'index']);
Route::get('reportes/boleta-masiva', [PdfController::class, 'boletaMasiva']);


// Asegúrate de que la ruta acepte el parámetro 'accion'
Route::get('/boleta/pdf/{id}/{accion}', [App\Http\Controllers\PdfController::class, 'boletaMasiva'])->name('boleta.pdf');

    // REPORTES boleta de califiación por asignatura
    Route::get('pdfRPA/{id}', [PdfRPAController::class, 'index']);
    // REPORTES boleta de califiación por asignatura
    Route::get('pdfRPG/{id}', [PdfRPGController::class, 'index']);
    // REPORTES para Licencias y Permisos
    Route::get('pdfRLyP/{id}', [PdfRLyPController::class,'index']);
    // REPORTES INFORMACIÓN DEL ESTUDIANTE Y ENCARGADO.
    //Route::get('pdfRPGEstudiante/{id}', ['\App\Http\Controllers\PdfRPGEstudianteController::class', 'index']);
    // helpers
    //Route::resource('funcion','PdfController');
    //Route::resource('funcion','PdfRPAController');
    //Route::resource('funcion','PdfRPGController');
    //Route::resource('funcion','PdfRLyPController');
    //Route::resource('funcion','matricula/index');

    // Asegúrate que dice Route::post
    Route::post('/calificaciones/enviar-correos', [CalificacionesPorAsignaturaController::class, 'enviarCorreosMasivos'])
    ->name('calificaciones.enviarCorreos');
    // Emails
    //Route::get('/enviar-correo/{nie}', [CorreoConAdjunto::class, 'enviarCorreo'])->name('enviar.correo');

      //Route::get('/boleta', function(){
        //  return new BoletaEstudiantes("yonYOn");
      //});
// Rutas para el rol Docente //
// 1. Ruta para el índice (Unificada a index_docente)
Route::get('/bitacora/docente', [AlumnoBitacoraController::class, 'index_docente'])->name('bitacora.index_docente');

// 2. RUTA FALTANTE CRÍTICA: Carga los alumnos por AJAX según la carga seleccionada
Route::get('/bitacora/alumnos/{id_carga_docente}', [AlumnoBitacoraController::class, 'getAlumnosPorCarga']);

// 3. Ruta para abrir la bitácora individual de un estudiante
Route::get('/bitacora/estudiante/{id_alumno}/{id_carga_docente}', [AlumnoBitacoraController::class, 'create'])->name('bitacora.create');
    
    // 4. Guardar la anotación (AJAX)
    Route::post('/bitacora/guardar', [AlumnoBitacoraController::class, 'store'])->name('bitacora.store');
    Route::post('/bitacora/actualizar', [AlumnoBitacoraController::class, 'update'])->name('bitacora.update');

Route::get('/refresh-csrf', function() {
    return response()->json(['token' => csrf_token()]);




});

// Ruta para obtener los alumnos y sus notas (Matriz)
Route::get('calificaciones/buscar-estudiantes', [CalificacionesPorAsignaturaController::class, 'buscarEstudiantes']);

// Ruta para guardar la matriz (asegúrate de que el nombre coincida con la vista)
Route::post('calificaciones/guardar-matriz', [CalificacionesPorAsignaturaController::class, 'store'])->name('calificaciones.store');

Route::get('get-secciones', [CalificacionesPorAsignaturaController::class, 'getSecciones']);
Route::get('get-asignaturas', [CalificacionesPorAsignaturaController::class, 'getAsignaturas']);
// Busca dónde tienes las rutas de calificaciones y agrega esta línea:
Route::post('calificaciones/guardar-todas', [CalificacionesPorAsignaturaController::class, 'guardarTodas']);
Route::get('/pdfRPA/{id}', [PdfRPAController::class, 'index'])->name('pdf.asignatura');

Route::get('/test-consulta', function() {
$col1 = 'nota_a1_1';
$col2 = 'nota_a2_1';
$col3 = 'nota_a3_1';
$colR = 'nota_r_1';
$colP = 'nota_p_p_1';
$codigo_ann = '2026';
$grado = '10';
$seccion = '01';
$turno = '04';
$codigo_asignatura='720';

$codigo_modalidad = '21';
$codigo_grado = '10';
$codigo_seccion = '02';
$codigo_turno = '04';
$codigo_annlectivo = '26';
$codigo_asignatura = '1092';
$id_matricula = '28096'; // Reemplaza con el ID de matrícula que deseas consultar

$EstudianteBoleta = DB::table('alumno as a')
            ->join('alumno_matricula AS am','a.id_alumno','=','am.codigo_alumno')
            ->join('nota AS n','am.id_alumno_matricula','=','n.codigo_matricula')
            ->join('bachillerato_ciclo AS bach', 'bach.codigo','=','am.codigo_bach_o_ciclo')
            ->join('grado_ano AS gr', 'gr.codigo','=','am.codigo_grado')
            ->join('seccion AS sec', 'sec.codigo','=','am.codigo_seccion')
            ->join('turno AS tur', 'tur.codigo','=','am.codigo_turno')
            ->join('asignatura AS asig','asig.codigo','=','n.codigo_asignatura')
            ->join('ann_lectivo AS ann','ann.codigo','=','am.codigo_ann_lectivo')
            ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.nombre_completo',"a.apellido_paterno",'a.apellido_materno', 'a.foto', 'a.codigo_genero', 'a.direccion_email as correo_estudiante',
                     'am.id_alumno_matricula as codigo_matricula','n.id_notas','n.codigo_asignatura',
                     'bach.nombre AS nombre_modalidad', 'gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion','tur.nombre as nombre_turno',
                     'bach.codigo as codigo_modalidad', 'gr.codigo as codigo_grado', 'sec.codigo as codigo_seccion','tur.codigo as codigo_turno',
                     'asig.codigo_area',
                     'n.nota_a1_1', 'n.nota_a2_1', 'n.nota_a3_1', 'nota_r_1', 'n.nota_p_p_1', 
                     'n.nota_a1_2', 'n.nota_a2_2', 'n.nota_a3_2', 'nota_r_2', 'n.nota_p_p_2',
                     'n.nota_a1_3', 'n.nota_a2_3', 'n.nota_a3_3', 'nota_r_3', 'n.nota_p_p_3', 
                     'n.nota_a1_4', 'n.nota_a2_4', 'n.nota_a3_4', 'nota_r_4', 'n.nota_p_p_4',
                     'n.nota_a1_5', 'n.nota_a2_5', 'n.nota_a3_5', 'nota_r_5', 'n.nota_p_p_5', 
                     'n.nota_final', 'n.recuperacion', 'n.nota_recuperacion_2',
                     'asig.codigo_area', 'ann.nombre as nombre_annlectivo',
                     DB::raw("TRIM(CONCAT(BTRIM(a.nombre_completo), CAST(' ' AS VARCHAR), BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno))) as full_nombres_apellidos"))
                     ->where([
                            ['am.id_alumno_matricula', '=', $id_matricula],
                            ['n.orden', '<>', 0], // Esta es la línea que filtra los que no son cero
                            ])
            ->orderBy('n.orden','asc')
            ->get();


    dd($EstudianteBoleta);
});
});