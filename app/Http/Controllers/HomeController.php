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
    public function getGradoSeccionPresentes()
    {
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_institucion = $_POST['codigo_institucion'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
        $presentes_retirados = $_POST['presentes_retirados'];
        $codigo_grado = substr($codigo_gradoseccionturno,0,2);
        $codigo_seccion = substr($codigo_gradoseccionturno,2,2);
        $codigo_turno = substr($codigo_gradoseccionturno,4,2);
        $codigo_modalidad = substr($codigo_gradoseccionturno,6,2);

         // Cabecera - INFORMACION GENERAL DE LA INSTITUCION
         $EstudianteInformacionInstitucion = DB::table('informacion_institucion as inf')
         ->leftjoin('personal as p','p.codigo_cargo','=','inf.nombre_director')
         ->select('inf.id_institucion','inf.codigo_institucion','inf.nombre_institucion','inf.telefono_uno','inf.logo_uno','inf.direccion_institucion','inf.nombre_director',
                     'inf.logo_dos','inf.logo_tres',
                 DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                 )
         ->where([
             ['id_institucion', '=', $codigo_institucion],
             ])
         ->orderBy('id_institucion','asc')
         ->limit(1)
         ->get();
         // extgraer datos para el encabezado
         $alto_cell = array('5'); $ancho_cell = array('60','6','24','30');
         foreach($EstudianteInformacionInstitucion as $response_i){  //Llenar el arreglo con datos
             $nombre_institucion = convertirTexto(trim($response_i->nombre_institucion));
             $nombre_director = convertirTexto(trim($response_i->full_name));
             $codigo_institucion = convertirTexto(trim($response_i->codigo_institucion));
             $logo_uno = "/img/".convertirTexto(trim($response_i->logo_uno));
             $firma_director = "/img/".convertirTexto(trim($response_i->logo_dos));
             $sello_direccion = "/img/".convertirTexto(trim($response_i->logo_tres));
             // LOGO DE LA INSTITUCIÓN
         //        $this->fpdf->image(URL::to($logo_uno),10,5,15,20);
           //      $this->fpdf->Cell(40, $alto_cell[0],"CENTRO ESCOLAR:",1,0,'L');       
             //    $this->fpdf->Cell(135, $alto_cell[0],$codigo_institucion . " - " .$nombre_institucion,1,1,'L');       
         } // FIN DEL FOREACH para los datos de la insitucion.

        $EstudiantesPresentes = DB::table('alumno as a')
                ->join('alumno_matricula as am','a.id_alumno','=','am.codigo_alumno')
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.codigo_genero','a.foto','a.ruta_pn','am.retirado','am.sobreedad','am.repitente', 'am.id_alumno_matricula as codigo_matricula',
                        DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"))
                ->where([
                    ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['am.codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['am.codigo_grado', '=', $codigo_grado],
                    ['am.codigo_seccion', '=', $codigo_seccion],
                    ['am.codigo_turno', '=', $codigo_turno],
                    ['am.retirado', '=', $presentes_retirados],
                    ])
                ->orderBy('full_name','asc')
                ->get();
                // array
                $Presentes = array(); $contenido = "";
                $fila_array = 0; $total_ = 0;
                foreach($EstudiantesPresentes as $response){  //Llenar el arreglo con datos
                    $retiradoss_ = trim($response->retirado);
                    $repitentess_ = trim($response->repitente);
                    $sobreedads_ = trim($response->sobreedad);
                    $codigo_genero_ = trim($response->codigo_genero); //01- masculino ; 02-Femenino
                    $full_name = trim($response->full_name);
                    $codigo_nie = trim($response->codigo_nie);
                    $codigo_alumno = $response->codigo_alumno;
                    $nombre_foto = trim($response->foto);
                    $foto = trim($response->foto);
                    $ruta_pn = trim($response->ruta_pn);
                    $codigo_genero = trim($response->codigo_genero);
                    $codigo_matricula = trim($response->codigo_matricula);
                        // FOTO DEL ESTUDIANTE.
                        if (file_exists('c:/wamp64/www/registro_academico/img/fotos/'.$codigo_institucion.'/'.$nombre_foto))
                        {
                            //$img = 'c:/wamp64/www/registro_academico/img/fotos/'.$codigo_institucion.'/'.$nombre_foto;	
                            $img = '/siscarad/public/img/fotos/'.$codigo_institucion.'/'.$nombre_foto;	
                            //$this->fpdf->image($img,180,5,25,30);
                        }else if($codigo_genero == '01'){
                                $fotos = 'avatar_masculino.png';
                                $img = '/siscarad/public/img/'.$fotos;
                                //$this->fpdf->image(URL::to($img),180,5,25,30);
                            }
                            else{
                                $fotos = 'avatar_femenino.png';
                                $img = '/siscarad/public/img/'.$fotos;
                                //$this->fpdf->image(URL::to($img),180,5,25,30);
                            }
                        //
                    // calculo de presentes.
                        $Presentes[$fila_array] = array ( 
                            "total_estudiantes" => $total_,
                            "apellidos_nombres_estudiantes" => $full_name,
                            "codigo_nie" => $codigo_nie,
                            "codigo_alumno" => $codigo_alumno,
                            "foto" => $img,
                            "nombre_foto" => $foto,
                            "codigo_institucion" => $codigo_institucion,
                            "ruta_pn" => $ruta_pn,
                            "codigo_matricula"=>$codigo_matricula
                        ); 
                    // total estudiantes
                    $total_++;
                    $fila_array++;
                };
           return $Presentes;
    }
}