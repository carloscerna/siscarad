<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\Tablas\Annlectivo;
use App\Models\Tablas\Calificaciones;
use App\Models\Tablas\CargaDocente;
use App\Models\Tablas\EstudianteMatricula;
use App\Models\Tablas\Institucion;
use Illuminate\Support\Facades\DB;

use Brian2694\Toastr\Facades\Toastr;
use App\Http\Controllers\PdfController;

use Carbon\Carbon;
// mail envio
use Illuminate\Support\Facades\Mail;
use App\Mail\BoletaEstudiantes;


class asistenciaDiariaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // vERIFICAR EL AÑO LECTIVO ACTIVO
        $annlectivo=Annlectivo::where('estatus', true)->orderBy('codigo', 'desc')->pluck('nombre','codigo')->toarray();
            return view('asistenciaDiaria.index', compact("annlectivo"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        //Mail::to('carlos.w.cerna@gmail.com')->send(new BoletaEstudiantes());

        $CargaDocente = CargaDocente::join('bachillerato_ciclo', 'bachillerato_ciclo.codigo', '=', 'carga_docente.codigo_bachillerato')
            ->join('grado_ano', 'grado_ano.codigo', '=', 'carga_docente.codigo')
            ->join('seccion', 'seccion.turno', '=', 'carga_docente.codigo')
            ->join('turno','turno.codigo', '=', 'carga_docente.codigo')
            ->select('carga_docente.codigo_bachillerato','carga_docente.codigo_grado','carga_docente.codigo_seccion','carga_docente.codigo_turno','carga_docente.codigo_ann_lectivo',
            'bachillerato.nombre as nombre_modalidad','grado_ano.nombre as nombre_grado','seccion.nombre as nombre_seccion','turno.nombre as nombre_turno')
            ->get();

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    /**
    * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getGradoSeccionAsistenciaDiaria()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $GradoSeccionTurno = [];

            $CargaDocente = DB::table('encargado_grado') 
            ->join('bachillerato_ciclo','encargado_grado.codigo_bachillerato','=','bachillerato_ciclo.codigo')
            ->join('grado_ano','encargado_grado.codigo_grado','=','grado_ano.codigo')
            ->join('seccion', 'encargado_grado.codigo_seccion', '=', 'seccion.codigo')                
            ->join('turno','encargado_grado.codigo_turno', '=', 'turno.codigo')
            ->select('encargado_grado.codigo_bachillerato', 'encargado_grado.codigo_grado','encargado_grado.codigo_seccion', 'encargado_grado.codigo_turno', 'encargado_grado.codigo_docente','bachillerato_ciclo.nombre as nombre_bachillerato', 'grado_ano.nombre as nombre_grado'
            ,'seccion.nombre as nombre_seccion', 'turno.nombre as nombre_turno')
            ->where('encargado_grado.codigo_docente', '=', $codigo_personal)
            ->where([
                ['encargado_grado.codigo_ann_lectivo', '=', $codigo_annlectivo],
                ['encargado_grado.encargado', '=', 'true'],
                ['encargado_grado.codigo_docente', '=', $codigo_personal],
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
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.codigo_genero','a.foto','a.ruta_pn','am.retirado','am.sobreedad','am.repitente')
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
                $fila_array = 0; 
                $total_ = 0; $total_m_ = 0; $total_f_ = 0;
                $retirados_ = 0; $total_r_m_ = 0; $total_r_f_ = 0;
                $repitentes_ = 0; 
                $sobreedad_ = 0; 
                $presentes_ = 0;
                foreach($EstudiantesIndicadores as $response){  //Llenar el arreglo con datos
                    $retiradoss_ = trim($response->retirado);
                    $repitentess_ = trim($response->repitente);
                    $sobreedads_ = trim($response->sobreedad);
                    $codigo_genero_ = trim($response->codigo_genero); //01- masculino ; 02-Femenino

                    // CONSULTA PARA GENERO MASCULINO
                    if($codigo_genero_ == '01'){
                        $total_m_++;
                            if($retiradoss_ == '1'){
                                $total_r_m_++;
                            }
                    }
                    // CONSULTA PARA GENERO FEMENINO
                    if($codigo_genero_ == '02'){
                        $total_f_++;
                            if($retiradoss_ == '1'){
                                $total_r_f_++;
                            }
                    }

                        if($repitentess_ == '1'){
                            $repitentes_++;
                        }
                    //
                        if($sobreedads_ == '1'){
                            $sobreedad_++;
                        }
                    // total estudiantes
                    $total_++;
                    $fila_array++;
                };
                // calculo de presentes.
                $presentes_ = $total_ - $retirados_;
                $Indicadores[$fila_array] = array ( 
                    "total_estudiantes" => $total_,
                    "total_masculino" => $total_m_,
                    "total_femenino" => $total_f_,
                    "total_retirado_masculino" => $total_r_m_,
                    "total_retirado_femenino" => $total_r_f_,
                    "presentes" => $presentes_,
                    "sobreedad" => $sobreedad_,
                    "repitentes" => $repitentes_,
                ); 
           return $Indicadores;
    }
    // evalua ACTUALIZACIÓN DE CALIFICACIONES CON RESPECTO A MODALIDAD, GRADO, PERIODO Y ACTIVIDAD.
    function getActualizarCalificacion(Request $request){
        $fila = $request->fila;
        $codigo_calificacion['codigo_calificacion'] = $request->codigo_calificacion;
        $calificacion['calificacion'] = $request->calificacion;
        $codigo_actividad = $request->codigo_actividad;
        $codigo_periodo = $request->codigo_periodo;
        $codigo_area = $request->codigo_area;
        $codigo_modalidad = substr($request->codigo_gradoseccionturno,6,2);
        $codigo_asignatura = $request->codigo_asignatura;
        // CAMBIAR EL VALOR DE LA VARIABLE "ACTIVIDAD PORCENTAJE" DEPENDIENDO DEL PERIODO
        // 01 - PERIODO 1 ... 05 - PERIODO 5
        // CODIGO ACTIVIDAD
        // 01- NOTA_A1_1 ... 03 - NOTA_A2_1
                // echo "<pre>";
                // print_r($calificacion);
                //  echo "</pre>";
                $nombre_periodos = array('nota_p_p_','recuperacion','nota_recuperacion_2');
                $nombre_actividades = array('nota_a1_','nota_a2_','nota_a3_');
                $nombre_recuperaciones = array('nota_r_');
                $numero_periodo = 0;
                $numero_p2 = 2; $numero_p3 = 3;
    }
}
