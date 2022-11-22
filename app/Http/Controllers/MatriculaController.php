<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//
use App\Models\Tablas\Annlectivo;
use App\Models\Tablas\Calificaciones;
use App\Models\Tablas\CatalogoFamiliar;
use App\Models\Tablas\EstudianteMatricula;

use Illuminate\Support\Facades\DB;

class MatriculaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // vERIFICAR EL AÑO LECTIVO ACTIVO
        $annlectivo=Annlectivo::where('estatus', true)->orderBy('codigo', 'asc')->pluck('nombre','codigo')->toarray();
        // vERIFICAR EL AÑO LECTIVO ACTIVO
        $codigo_familiar_0=CatalogoFamiliar::all()->pluck('descripcion','codigo')->toarray();
        // return view
        return view('matricula.index', compact('annlectivo','codigo_familiar_0'));
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
    public function update(Request $request, $id)
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
    public function getGradoSeccionMatricula()
    {
        $codigo_personal = $_POST['id'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $GradoSeccionTurno = array();

            $CargaDocente = DB::table('encargado_grado')
                ->distinct()
                ->join('bachillerato_ciclo','encargado_grado.codigo_bachillerato','=','bachillerato_ciclo.codigo')
                ->join('grado_ano','encargado_grado.codigo_grado','=','grado_ano.codigo')
                ->join('seccion', 'encargado_grado.codigo_seccion', '=', 'seccion.codigo')                
                ->join('turno','encargado_grado.codigo_turno', '=', 'turno.codigo')
                ->select('codigo_bachillerato', 'codigo_grado','codigo_seccion', 'codigo_turno', 'codigo_docente','bachillerato_ciclo.nombre as nombre_bachillerato', 'grado_ano.nombre as nombre_grado'
                ,'seccion.nombre as nombre_seccion', 'turno.nombre as nombre_turno')
                ->where('codigo_docente', '=', $codigo_personal)
                ->where([
                    ['codigo_docente', '=', $codigo_personal],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['encargado', '=', 'true'],
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
    // NOMINA DE ESTUDIANTES.
    public function getGradoSeccionMatriculaTodos()
    {
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $codigo_institucion = $_POST['codigo_institucion'];
        $codigo_gradoseccionturno = $_POST['codigo_gradoseccionturno'];
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
             $nombre_institucion = utf8_decode(trim($response_i->nombre_institucion));
             $nombre_director = utf8_decode(trim($response_i->full_name));
             $codigo_institucion = utf8_decode(trim($response_i->codigo_institucion));
             $logo_uno = "/img/".utf8_decode(trim($response_i->logo_uno));
             $firma_director = "/img/".utf8_decode(trim($response_i->logo_dos));
             $sello_direccion = "/img/".utf8_decode(trim($response_i->logo_tres));
             // LOGO DE LA INSTITUCIÓN
         //        $this->fpdf->image(URL::to($logo_uno),10,5,15,20);
           //      $this->fpdf->Cell(40, $alto_cell[0],"CENTRO ESCOLAR:",1,0,'L');       
             //    $this->fpdf->Cell(135, $alto_cell[0],$codigo_institucion . " - " .$nombre_institucion,1,1,'L');       
         } // FIN DEL FOREACH para los datos de la insitucion.

        $EstudiantesPresentes = DB::table('alumno as a')
                ->join('alumno_matricula as am','a.id_alumno','=','am.codigo_alumno')
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.codigo_genero','a.foto','a.ruta_pn', 'a.edad',
                        'am.retirado','am.sobreedad','am.repitente', 'am.codigo_resultado',
                        DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"))
                ->where([
                    ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['am.codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ['am.codigo_grado', '=', $codigo_grado],
                    ['am.codigo_seccion', '=', $codigo_seccion],
                    ['am.codigo_turno', '=', $codigo_turno],
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
                    $edad = trim($response->edad);
                    $sobreedad = trim($response->sobreedad);
                    $retirado = trim($response->retirado);
                    $codigo_resultado = trim($response->codigo_resultado);
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
                            "edad"=> $edad,
                            "retirado"=>$retirado,
                            "sobreedad"=>$sobreedad,
                            "codigo_resultado"=>$codigo_resultado,
                            "codigo_grado"=>$codigo_grado
                        ); 
                    // total estudiantes
                    $total_++;
                    $fila_array++;
                };
           return $Presentes;
    }

    public function getDatosResponsables()
    {
        $codigo_alumno = $_POST['id'];
        $codigo_modalidad = $_POST['codigo_modalidad'];
        $codigo_grado = $_POST['codigo_grado'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];

        $DatosResponsables = array();

        // Eloquiente verificar si ya existe la matricula.
            $estado_matricular = "no";
            $EstudianteMatriculaCount = EstudianteMatricula::where('codigo_alumno', '=', $codigo_alumno)
            ->where('codigo_bach_o_ciclo',"=",$codigo_modalidad)
            ->where('codigo_grado','=',$codigo_grado)
            ->where('codigo_ann_lectivo','=',$codigo_annlectivo)
            ->count();
        // VALIDAR SI ES IGUAL A CERO.
            if($EstudianteMatriculaCount != 0){
                $estado_matricular = "si";
            }
        //
            $DatosResponsablesEstudiantes = DB::table('alumno_encargado')
                ->select('id_alumno_encargado', 'nombres','telefono', 'direccion', 'encargado','codigo_familiar', 'dui')
                ->where('codigo_alumno', '=', $codigo_alumno)
                ->orderBy('id_alumno_encargado','asc')
                ->get();
                
                $fila_array = 0;
                foreach($DatosResponsablesEstudiantes as $response){  //Llenar el arreglo con datos
                    $id_encargado_ = $response->id_alumno_encargado; 
                    $nombres = trim($response->nombres); 
                    $telefono = trim($response->telefono); 
                    $direccion = $response->direccion; 
                    $encargado  = $response->encargado; 
                    $codigo_familiar = $response->codigo_familiar; 
                    $dui = $response->dui;

                    $DatosResponsables[$fila_array] = array ( 
                        "id_" => $id_encargado_,
                        "nombres"=>$nombres,
                        "telefono"=>$telefono,
                        "direccion"=>$direccion,
                        "encargado"=>$encargado,
                        "codigo_familiar"=>$codigo_familiar,
                        "dui"=>$dui,
                        "fila"=>$fila_array,
                        "estado_matricular"=> $estado_matricular
                    ); 
                    $fila_array++;
                }
            return $DatosResponsables;
    }

    public function getDatosMatriculaGuardar()
    {
        $codigo_alumno = $_POST['codigo_alumno'];
        $codigo_modalidad = $_POST['codigo_modalidad'];
        $codigo_grado = $_POST['codigo_grado'];
        $codigo_seccion = $_POST['codigo_seccion'];
        $codigo_turno = $_POST['codigo_turno'];
        $codigo_annlectivo = $_POST['codigo_annlectivo'];
        $DatosMatriculaMensaje = array();
        // datos del familiar.
        $codigo_id = $_POST["codigo_id"];
        $codigo_familiar = $_POST["codigo_familiar"];
        $telefono = $_POST["telefono"];
        $nombre_encargado_otro = $_POST["nombreotro"];
        $direccion = $_POST["direccion"];

        // Eloquent
        $fila_array = 0;
        $EstudianteMatriculaCount = EstudianteMatricula::where('codigo_alumno', '=', $codigo_alumno)
                    ->where('codigo_bach_o_ciclo',"=",$codigo_modalidad)
                    ->where('codigo_grado','=',$codigo_grado)
                    ->where('codigo_ann_lectivo','=',$codigo_annlectivo)
                    ->count();

        if($EstudianteMatriculaCount != 0) {
            //la matricula ya esta realizada.
            $mensaje_01 = "Ya Matriculado...";
            $DatosMatriculaMensaje[$fila_array] = array ( 
                "matricula_mensaje" => $mensaje_01,
                "fila"=>$fila_array
            ); 
            $fila_array++;
        }else{
            //la matricula ya esta realizada.
            $mensaje_01 = "Realizar Matricula...";
            $DatosMatriculaMensaje[$fila_array] = array ( 
                "matricula_mensaje" => $mensaje_01,
                "fila"=>$fila_array
            ); 


            // GUARDAR CODIGO DEL ALUMNO EN LA TABLA ALUMNO MATRICULA.
            EstudianteMatricula::create([
                'codigo_alumno' => $codigo_alumno
            ]);
            // SELECT EN ALUMNO MATRICULA Y ACTULIZAR CON LOS DATO ENCONTRADOS
                $consultaIdMatricula = DB::table('alumno_matricula')
                    ->select('id_alumno_matricula')
                    ->where('codigo_alumno', '=', $codigo_alumno)
                    ->orderBy('id_alumno_matricula','asc')
                    ->get();
                    foreach($consultaIdMatricula as $response){  //Llenar el arreglo con datos
                        $id_ultimo = $response->id_alumno_matricula;
                        $codigo_matricula = $id_ultimo;
                        $DatosMatriculaMensaje[$fila_array] = array ( 
                            "id_alumno_matricula" => $id_ultimo
                        ); 
                    }
                // ACTUALIZAR LA TABLA ALUMNOMATRICULA PERO CON LOS DATOS DE CODIGO MODALIDAD - GRADO -SECCION - TURNO
                    $actual['update'] = DB::update("UPDATE alumno_matricula set codigo_bach_o_ciclo = ?, codigo_grado = ?, codigo_seccion = ?, codigo_turno = ?, codigo_ann_lectivo = ?
                                                        WHERE id_alumno_matricula = ? and codigo_alumno = ?", 
                                                            [$codigo_modalidad, $codigo_grado, $codigo_seccion, $codigo_turno, $codigo_annlectivo, $id_ultimo, $codigo_alumno]);
                // CONSULTAR LA TABLA asignacion de aignaturas por modalidad, grado, seccion y turno.
                    $consultaIdAA = DB::table('a_a_a_bach_o_ciclo')
                        ->select('codigo_asignatura','orden')
                        ->where('codigo_bach_o_ciclo',"=",$codigo_modalidad)
                        ->where('codigo_grado','=',$codigo_grado)
                        ->where('codigo_ann_lectivo','=',$codigo_annlectivo)
                        ->orderBy('orden','asc')
                        ->get();
                        foreach($consultaIdAA as $response){  //Llenar el arreglo con datos
                            $codigo_asignatura = $response->codigo_asignatura;
                            $orden = $response->orden;
                                //
                                // GUARDAR EN LA TABLA NOTA LA INFORMACION CORRESPONDIENTE AL CODIGO ASIGNATURA, CODIGO ALUMNO, CODIGO MATRICULA.
                                    Calificaciones::create([
                                        'codigo_alumno' => $codigo_alumno,
                                        'codigo_matricula' => $codigo_matricula,
                                        'codigo_asignatura' => $codigo_asignatura,
                                        'orden' => $orden
                                    ]);
                                //
                                //
                        }
                        // Ultimo mensaje.
                        $calificacion_mensaje = "Códigos de asignaturas guarados...";
                        $DatosMatriculaMensaje[$fila_array] = array ( 
                            "calificacion_mensaje" => $calificacion_mensaje
                        );

                        //
                        //  FIN DE CONSULTA DE MATRICULA Y GUARADODO DE CALIFICACIONES.
                        //

                        //
                        //  ACTUALIZAR DATOS DE LOS RESPONSABLES.
                        //
                            for ($i=0; $i < count($codigo_id); $i++) { 
                                // CONDICIONAR PARA QUE SOLO GUARDE EL NOMBRE4 DEL ENCARGADO.
                                $telefonos = trim($telefono[$i]);
                                if($i == 2){
                                    $actualiars['update'] = DB::update("UPDATE alumno_encargado set codigo_familiar = ?, telefono = ?, nombres = ?, direccion = ?
                                    WHERE id_alumno_encargado = ?", 
                                        [$codigo_familiar[$i], $telefonos, $nombre_encargado_otro, $direccion, $codigo_id[$i]]);
                                }else{
                                    $actualiars['update'] = DB::update("UPDATE alumno_encargado set codigo_familiar = ?, telefono = ?, direccion = ?
                                    WHERE id_alumno_encargado = ?", 
                                        [$codigo_familiar[$i], $telefonos, $direccion, $codigo_id[$i]]);    
                                }
                        //
                        //  ACTUALIZAR DIRECCION DEL ESTUDIANTE.
                        //
                            $actualiarles['update'] = DB::update("UPDATE alumno set direccion_alumno = ?
                            WHERE id_alumno = ?", 
                                [$direccion, $codigo_alumno]);

                    }

                //
                //
                //
            // fila incrementar.
                $fila_array++;
        }   // TERMINA EL COUNT


        //retornar valores
        return $DatosMatriculaMensaje;
    }
}
