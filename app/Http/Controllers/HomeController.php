<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tablas\Personal;
use App\Models\Tablas\EncargadoGrado;
use App\Models\Tablas\Annlectivo;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // vERIFICAR EL AÑO LECTIVO ACTIVO
        $annlectivo=Annlectivo::where('estatus', true)->orderBy('codigo', 'desc')->pluck('nombre','codigo')->toarray();
            return view('home', compact('annlectivo'));
    }
    
    public function getGradoSeccion()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $GradoSeccionTurno = array();

            $CargaDocente = DB::table('carga_docente')
                ->distinct()
                ->join('bachillerato_ciclo','carga_docente.codigo_bachillerato','=','bachillerato_ciclo.codigo')
                ->join('grado_ano','carga_docente.codigo_grado','=','grado_ano.codigo')
                ->join('seccion', 'carga_docente.codigo_seccion', '=', 'seccion.codigo')                
                ->join('turno','carga_docente.codigo_turno', '=', 'turno.codigo')
                ->select('codigo_bachillerato', 'codigo_grado','codigo_seccion', 'codigo_turno', 'codigo_docente','bachillerato_ciclo.nombre as nombre_bachillerato', 'grado_ano.nombre as nombre_grado'
                ,'seccion.nombre as nombre_seccion', 'turno.nombre as nombre_turno')
                ->where('codigo_docente', '=', $codigo_personal)
                ->where([
                    ['codigo_docente', '=', $codigo_personal],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ])
                ->get();
                
                $fila_array = 0;
                foreach($CargaDocente as $response){  //Llenar el arreglo con datos
                    $codigos_ = $response->codigo_grado . $response->codigo_seccion . $response->codigo_turno . $response->codigo_bachillerato; 
                    $nombres_ = trim($response->nombre_grado) . ' ' . trim($response->nombre_seccion) . ' - ' . trim($response->nombre_turno) . ' - ' . trim($response->nombre_bachillerato);
                    $GradoSeccionTurno[$fila_array] = array ( 
                        "codigo_gradoseccionturno" => $codigos_,
                        "nombre_gradoseccionturno" => $nombres_,
                    ); 
                    $fila_array++;
                }
            return $GradoSeccionTurno;
    }

    public function getGradoSeccionIndicadores()
    {
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
        $codigo_grado = substr($codigo_gradoseccionturno,0,2);
        $codigo_seccion = substr($codigo_gradoseccionturno,2,2);
        $codigo_turno = substr($codigo_gradoseccionturno,4,2);
        $codigo_modalidad = substr($codigo_gradoseccionturno,6,2);

        $EstudiantesIndicadores = DB::table('alumno as a')
                ->join('alumno_matricula as am','a.id_alumno','=','am.codigo_alumno')
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','am.retirado','am.sobreedad','am.repitente')
                ->where([
                    ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['am.codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['am.codigo_grado', '=', $codigo_grado],
                    ['am.codigo_seccion', '=', $codigo_seccion],
                    ['am.codigo_turno', '=', $codigo_turno],
                    ])
                ->orderBy('codigo_alumno','asc')
                ->get();
                // array
                $Indicadores = array();
                $fila_array = 0; $total_ = 0; $retirados_ = 0; $repitentes_ = 0; $sobreedad_ = 0; $presentes_ = 0;
                foreach($EstudiantesIndicadores as $response){  //Llenar el arreglo con datos
                    $retiradoss_ = trim($response->retirado);
                    $repitentess_ = trim($response->repitente);
                    $sobreedads_ = trim($response->sobreedad);
                    // contar valores
                        if($retiradoss_ == '1'){
                            $retirados_++;
                        }
                    //
                        if($repitentess_ == '1'){
                            $repitentes_++;
                        }
                    //
                        if($sobreedads_ == '1'){
                            $sobreedad_++;
                        }
                    // total
                    $total_++;
                    $fila_array++;
                };
                // calculo de presentes.
                $presentes_ = $total_ - $retirados_;
                $Indicadores[$fila_array] = array ( 
                    "total" => $total_,
                    "presentes" => $presentes_,
                    "retirados" => $retirados_,
                    "sobreedad" => $sobreedad_,
                    "repitentes" => $repitentes_,
                ); 
           return $Indicadores;
    }
}