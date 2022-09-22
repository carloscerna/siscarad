<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use GuzzleHttp\Psr7\Header;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PdfRPAController extends Controller
{
    protected $fpdf;

    public function __construct()
    {
        $this->fpdf = new Fpdf('P','mm','Letter');	// Formato Letter;
    }

    public function index($id) 
    {
        // Configurar PDF.
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->AddPage();
            $this->fpdf->SetMargins(5, 5, 5);
            $this->fpdf->SetAutoPageBreak(true,5);
            $this->fpdf->SetX(30);
        // Variables
        // NIE - ID - CODIGO MATRICULOA - (CODIGO GRADO - SECCION - TURNO -MODALIDAD) - ANNLECTIVO
            $EstudianteMatricula = explode("-",$id);
            $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[0];
            $codigo_modalidad = substr($codigo_gradoseccionturnomodalidad,6,2);
            $codigo_turno = substr($codigo_gradoseccionturnomodalidad,4,2);
            $codigo_seccion = substr($codigo_gradoseccionturnomodalidad,2,2);
            $codigo_grado = substr($codigo_gradoseccionturnomodalidad,0,2);
            $codigo_annlectivo = $EstudianteMatricula[1];
            $codigo_institucion = $EstudianteMatricula[2];
            $codigo_asignatura = $EstudianteMatricula[3];
            $codigo_area_asignatura = $EstudianteMatricula[4];
            $codigo_personal = $EstudianteMatricula[5];
            ////////////////////////////////////////////////////////////////////
            //////// crear matriz para la tabla CATALOGO_AREA_ASIGNATURA.
            //////////////////////////////////////////////////////////////////
            $catalogo_area_asignatura_codigo = array();	// matriz para los diferentes código y descripción.
            $catalogo_area_asignatura_area = array();
            $catalogo_area_basica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_formativa = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_tecnica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_edps = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_edecr = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_edre = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_complementaria = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_cc = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            $catalogo_area_alertas = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
            // CATALOGO ASIGNATURA
                //
                $CatalogoAreaAsignatura = DB::table('catalogo_area_asignatura')
                ->select('codigo','descripcion')
                ->get();
                foreach($CatalogoAreaAsignatura as $response_area){  //Llenar el arreglo con datos
                    $catalogo_area_asignatura_codigo[] = (trim($response_area->codigo));
                    $catalogo_area_asignatura_area[] = (trim($response_area->descripcion));
                    //* incrementar valor de la fila para la array asociativa
                } // FIN DEL FOREACH para los datos de la insitucion.

            // RELLENAR LAS ASIGNATURAS SEGUN CICLO
                // Cabecera - INFORMACION GENERAL DE LA INSTITUCION
                $AsignacionAsignatura = DB::table('a_a_a_bach_o_ciclo as aaa')
                ->join('asignatura as a','a.codigo','=','aaa.codigo_asignatura')
                ->select('aaa.orden','a.nombre as nombre_asignatura','a.codigo as codigo_asignatura','a.codigo_cc as concepto_calificacion','a.codigo_area'
                        )
                ->where([
                    ['codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['codigo_grado', '=', $codigo_grado],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ])
                ->orderBy('aaa.orden','asc')
                ->get();
                // extgraer datos para el encabezado
                $datos_asignatura = array(); $fila_array_asignatura = 0;
                $datos_asignatura = [
                    "codigo" => [""],
                    "nombre" => [""],
                    "concepto" => [""],
                    "codigo_area" => [""]
                ];                   
            foreach($AsignacionAsignatura as $response_i){  //Llenar el arreglo con datos
                $nombre_asignatura_a = utf8_decode(trim($response_i->nombre_asignatura));
                $codigo_asignatura_a = utf8_decode(trim($response_i->codigo_asignatura));
                $concepto_calificacion_a = utf8_decode(trim($response_i->concepto_calificacion));
                $codigo_area_a = utf8_decode(trim($response_i->codigo_area));

                    $datos_asignatura["codigo"][$fila_array_asignatura] = $codigo_asignatura_a;
                    $datos_asignatura["nombre"][$fila_array_asignatura] = $nombre_asignatura_a;
                    $datos_asignatura["concepto"][$fila_array_asignatura] = $concepto_calificacion_a;
                    $datos_asignatura["codigo_area"][$fila_array_asignatura] = $codigo_area_a;
                // capturar el nombre de la asignatura.
                    if($codigo_asignatura_a == $codigo_asignatura){
                        $nombre_asignatura_boleta = $nombre_asignatura_a;
                    }
                     
                //* incrementar valor de la fila para la array asociativa
                $fila_array_asignatura++;
            } // FIN DEL FOREACH para los datos de la insitucion.

            // Cabecera - DOCENE ENCARGADO DE LA SECCION
            $EncargadoGrado = DB::table('encargado_grado as eg')
            ->join('personal as p','p.id_personal','=','eg.codigo_docente')
            ->select('p.id_personal',
                    DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                    )
            ->where([
                ['codigo_bachillerato', '=', $codigo_modalidad],
                ['codigo_grado', '=', $codigo_grado],
                ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                ['codigo_seccion', '=', $codigo_seccion],
                ['codigo_turno', '=', $codigo_turno],
                ['encargado', '=', 'true'],
                ])
            ->orderBy('p.id_personal','asc')
            ->get();

            foreach($EncargadoGrado as $response_eg){  //Llenar el arreglo con datos
                $codigo_personal_ = utf8_decode(trim($response_eg->id_personal));
                $nombre_personal_ = utf8_decode(trim($response_eg->full_name));
            } // FIN DEL FOREACH para los datos de la insitucion.

                        // Cabecera - DOCENE ENCARGADO DE LA SECCION
                        $EncargadoAsignatura = DB::table('personal as p')
                        ->select('p.id_personal',
                                DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                                )
                        ->where([
                            ['p.id_personal', '=', $codigo_personal],
                            ])
                        ->orderBy('p.id_personal','asc')
                        ->get();
            
                        foreach($EncargadoAsignatura as $response_eg){  //Llenar el arreglo con datos
                            $codigo_personal_ = utf8_decode(trim($response_eg->id_personal));
                            $nombre_personal_ea = utf8_decode(trim($response_eg->full_name));
                        } // FIN DEL FOREACH para los datos de la insitucion.

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
            $alto_cell = array('5'); $ancho_cell = array('60','6','24','30','15');
            foreach($EstudianteInformacionInstitucion as $response_i){  //Llenar el arreglo con datos
                $nombre_institucion = utf8_decode(trim($response_i->nombre_institucion));
                $nombre_director = utf8_decode(trim($response_i->full_name));
                $codigo_institucion = utf8_decode(trim($response_i->codigo_institucion));
                $logo_uno = "/img/".utf8_decode(trim($response_i->logo_uno));
                $firma_director = "/img/".utf8_decode(trim($response_i->logo_dos));
                $sello_direccion = "/img/".utf8_decode(trim($response_i->logo_tres));
                // LOGO DE LA INSTITUCIÓN
                    $this->fpdf->image(URL::to($logo_uno),10,5,15,20);
                    $this->fpdf->Cell(40, $alto_cell[0],"CENTRO ESCOLAR:",1,0,'L');       
                    $this->fpdf->Cell(135, $alto_cell[0],$codigo_institucion . " - " .$nombre_institucion,1,1,'L');       
            } // FIN DEL FOREACH para los datos de la insitucion.
             //
            $EstudianteBoleta = DB::table('alumno as a')
                ->join('alumno_matricula AS am','a.id_alumno','=','am.codigo_alumno')
                ->join('nota AS n','am.id_alumno_matricula','=','n.codigo_matricula')
                ->join('bachillerato_ciclo AS bach', 'bach.codigo','=','am.codigo_bach_o_ciclo')
                ->join('grado_ano AS gr', 'gr.codigo','=','am.codigo_grado')
                ->join('seccion AS sec', 'sec.codigo','=','am.codigo_seccion')
                ->join('turno AS tur', 'tur.codigo','=','am.codigo_turno')
                ->join('asignatura AS asig','asig.codigo','=','n.codigo_asignatura')
                ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.nombre_completo',"a.apellido_paterno",'a.apellido_materno', 'a.foto', 'a.codigo_genero',
                        'am.id_alumno_matricula as codigo_matricula','am.codigo_bach_o_ciclo as codigo_modalidad','am.codigo_grado','am.codigo_seccion','am.codigo_turno','am.codigo_ann_lectivo',
                        'n.id_notas','n.codigo_asignatura',
                        'bach.nombre AS nombre_modalidad', 'gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion','tur.nombre as nombre_turno',
                        'n.nota_a1_1', 'n.nota_a2_1', 'n.nota_a3_1', 'n.nota_p_p_1', 'n.nota_a1_2', 'n.nota_a2_2', 'n.nota_a3_2', 'n.nota_p_p_2',
                        'n.nota_a1_3', 'n.nota_a2_3', 'n.nota_a3_3', 'n.nota_p_p_3', 'n.nota_a1_4', 'n.nota_a2_4', 'n.nota_a3_4', 'n.nota_p_p_4',
                        'n.nota_a1_5', 'n.nota_a2_5', 'n.nota_a3_5', 'n.nota_p_p_5', 'n.nota_final', 'n.recuperacion', 'n.nota_recuperacion_2',
                        'asig.codigo_area','asig.codigo as codigo_asignatura','asig.nombre as nombre_asignatura',
                    DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"),
                    DB::raw("TRIM(CONCAT(BTRIM(a.nombre_completo), CAST(' ' AS VARCHAR), BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno))) as full_nombres_apellidos")
                    )
                        ->where([
                            ['am.codigo_bach_o_ciclo', '=', $codigo_modalidad],
                            ['codigo_grado', '=', $codigo_grado],
                            ['codigo_seccion', '=', $codigo_seccion],
                            ['codigo_turno', '=', $codigo_turno],
                            ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                            ['codigo_asignatura', '=', $codigo_asignatura],
                            ['am.retirado', '=', 'f'],
                            ])
                        ->orderBy('full_name','asc')
                        ->get();

            // variales de entorno para mostrar la información.
            $fila = 1;             
            $this->fpdf->SetX(30); 
            foreach($EstudianteBoleta as $response){  //Llenar el arreglo con datos
                $nombre_completo = utf8_decode(trim($response->full_nombres_apellidos));
                $nombre_estudiante = utf8_decode(trim($response->full_name));
                $codigo_nie = utf8_decode(trim($response->codigo_nie));
                $nombre_modalidad = utf8_decode(trim($response->nombre_modalidad));  
                $nombre_grado = utf8_decode(trim($response->nombre_grado));  
                $nombre_seccion = utf8_decode(trim($response->nombre_seccion));  
                $nombre_turno = utf8_decode(trim($response->nombre_turno));                
                $codigo_asignatura = utf8_decode(trim($response->codigo_asignatura));
                $codigo_area = utf8_decode(trim($response->codigo_area));
                $nota_final = utf8_decode(trim($response->nota_final));
                $nombre_foto = (trim($response->foto));
                $codigo_genero = (trim($response->codigo_genero));
                // NOTA ACTIVIDAD 1, 2 Y PO, NOTA PERIODO 1
                $nota_actividades_0 = array('',
                            $response->nota_a1_1,$response->nota_a2_1,$response->nota_a3_1,$response->nota_p_p_1, // 1
                            $response->nota_a1_2,$response->nota_a2_2,$response->nota_a3_2,$response->nota_p_p_2, // 5
                            $response->nota_a1_3,$response->nota_a2_3,$response->nota_a3_3,$response->nota_p_p_3, // 9
                            $response->nota_a1_4,$response->nota_a2_4,$response->nota_a3_4,$response->nota_p_p_4, // 13
                            $response->nota_a1_5,$response->nota_a2_5,$response->nota_a3_5,$response->nota_p_p_5, // 17
                            $response->recuperacion, $response->nota_recuperacion_2, $response->nota_final);      // 21, 22, 23
                // MATRICES
                $periodos_a = array('PERIODO 1', 'PERIODO 2', 'PERIODO 3', 'PERIODO 4', 'PERIODO 5', 'PROMEDIO FINAL', 'R');
                $actividad_periodo = array('A1','A2','PO','PP','PF');
                // VALIDAR VARIABGLES PARA MOSTRAR CABECERA Y CALIFICACIONES.
                if($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){ // EDUCACI{ON BASICA}
                    $valor_periodo = 2; $valor_actividades = 12; $ancho_area_asignatura = 162;
                }else if($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){   // EDUCACION MEDIA
                    $valor_periodo = 3; $valor_actividades = 16; $ancho_area_asignatura = 186;
                }else if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){   // NOCTURNA
                    $valor_periodo = 4; $valor_actividades = 20; $ancho_area_asignatura = 210;
                }else{
                    $valor_periodo = 2; $valor_actividades = 12; $ancho_area_asignatura = 162;    // DEFAULT PUEDE SER PARVULARIA
                }

                if($fila == 1){
                    // LLAMAR A LA FUNCION QUE POSEE EL ENCAVEZADO DE CADA REA DE LA ASIGNTURA
                    // EncabezadoCatalogoAreaAsignatura($codigo_area);
                    //
 //                   $this->fpdf->Cell(40,$alto_cell[0],"Estudiante",1,0,'L');       
//                    $this->fpdf->Cell(135,$alto_cell[0],$codigo_nie . " - " . $nombre_completo,1,1,'L');       
                    $this->fpdf->SetX(30); 
                    $this->fpdf->Cell(40,$alto_cell[0],utf8_decode("Nivel"),1,0,'L');       
                    $this->fpdf->Cell(135,$alto_cell[0],$nombre_modalidad,1,1,'L');       
                    $this->fpdf->SetX(30); 
                    $this->fpdf->Cell(15,$alto_cell[0],"Grado",1,0,'L');       
                    $this->fpdf->Cell(70,$alto_cell[0],$nombre_grado,1,0,'L');       

                    $this->fpdf->Cell(15,$alto_cell[0],utf8_decode("Sección"),1,0,'L');       
                    $this->fpdf->Cell(10,$alto_cell[0],$nombre_seccion,1,0,'C');       
                    
                    $this->fpdf->Cell(20,$alto_cell[0],"Turno",1,0,'L');       
                    $this->fpdf->Cell(45,$alto_cell[0],$nombre_turno,1,1,'C');       
                    // COMPONENTE DE ESTUDIO Y PRIMER FILA DE LAS ACTIVIDADES Y PROMEDIOS
                    $this->fpdf->SetX(30); 
                    $this->fpdf->Cell(55,$alto_cell[0],"Componente del Plan de Estudio: ",1,0,'L');       
                    $this->fpdf->Cell(120,$alto_cell[0],$nombre_asignatura_boleta,1,1,'L');       
                    // NOMBRE DEL ENCARGADO DE GRADO, Y ENCARGADO DE LA ASIGNATURA
                    $this->fpdf->SetX(30); 
                    $this->fpdf->Cell(55,$alto_cell[0],"Encargado de Grado: ",1,0,'L');       
                    $this->fpdf->Cell(120,$alto_cell[0],$nombre_personal_,1,1,'L');       
                    // NOMBRE DEL ENCARGADO DE GRADO, Y ENCARGADO DE LA ASIGNATURA
                    $this->fpdf->SetX(30); 
                    $this->fpdf->Cell(55,$alto_cell[0],"Encargado de la asignatura: ",1,0,'L');       
                    $this->fpdf->Cell(120,$alto_cell[0],$nombre_personal_ea,1,1,'L');       

                    $this->fpdf->ln();

                    $this->fpdf->SetX(30); 
                    $this->fpdf->SetFont('Arial', 'B', '7');
                    // fila de información
                    $this->fpdf->Cell(27,$alto_cell[0],"A1->Actividad 1 (35%)",'LR',0,'L');       
                    $this->fpdf->Cell(27,$alto_cell[0],"A2->Actividad 2 (35%)",'LR',0,'L');       
                    $this->fpdf->Cell(35,$alto_cell[0],"PO->Prueba Objetiva (30%)",'LR',0,'L'); 
                    $this->fpdf->Cell(35,$alto_cell[0],"PP->Promedio Periodo",'LR',0,'L');      
                    $this->fpdf->Cell(30,$alto_cell[0],"PF->Promedio Final",'LR',1,'L');          
                    // fila de información 
                    $this->fpdf->SetX(30);       
                    $this->fpdf->Cell(35,$alto_cell[0],utf8_decode("NR1->Nota Recuperación 1"),'LR',0,'L');             
                    $this->fpdf->Cell(35,$alto_cell[0],utf8_decode("NR2->Nota Recuperación 2"),'LR',0,'L');                
                    $this->fpdf->Cell(20,$alto_cell[0],utf8_decode("A->Aprobado"),'LR',0,'L');                
                    $this->fpdf->Cell(20,$alto_cell[0],utf8_decode("R->Reprobado"),'LR',0,'L');                
                    $this->fpdf->Cell(20,$alto_cell[0],utf8_decode("NF->Nota Final"),'LR',1,'L');                
                    $this->fpdf->ln();

                    // n.º, NIE. NOMBRE DEL ESTUDIANTE
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'','LT',0,'L',false);            
                        $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'','T',0,'L',false);            
                    // cabecera de la tabla de calificaicone4s por periodo
                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],"",'RT',0,'L');
                    for ($pp=0; $pp <= $valor_periodo; $pp++) { 
                        if($valor_periodo == $pp){
                            $this->fpdf->Cell($ancho_cell[2],$alto_cell[0],$periodos_a[$pp],1,1,'C');
                        }else{
                            $this->fpdf->Cell($ancho_cell[2],$alto_cell[0],$periodos_a[$pp],1,0,'C');
                        }
                    }
                    // COMPONENTE DE ESTUDIO Y PRIMER FILA DE LAS ACTIVIDADES Y PROMEDIOS
                    // n.º, NIE. NOMBRE DEL ESTUDIANTE
                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],utf8_decode('N.º'),1,0,'L',false);            
                    $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'NIE',1,0,'L',false);            
                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],"NOMINA DE ESTUDIANTES",'LRB',0,'C');             
                    //
                    for ($pp=0; $pp <= $valor_periodo; $pp++) { 
                        for ($ap=0; $ap < count($actividad_periodo) -1; $ap++) { 
                                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$actividad_periodo[$ap],1,0,'C');
                        }
                        if($valor_periodo == $pp){
                            // colocar celda PF
                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$actividad_periodo[4],1,0,'C');
                        }
                    }
                        // colocar celda NR1
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'NR1',1,0,'C');
                        // colocar celda NR2
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'NR2',1,0,'C');
                        // colocar celda NF
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'NF',1,0,'C');
                        // COLOCAR CELDA RESULTADO.
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$periodos_a[6],1,1,'C');
                    ///////////////////////////////////////////////////////////////////////////////////////////////////
                    /////VERIFICAR ENCABEZADO de AREA DE ASIGNATURAS///////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////////		
                    /*	"01"	"Básica                                                                     " 0
                        "02"	"Formativa                                                                  " 1
                        "03"	"Técnica                                                                    " 2
                        "04"	"Experiencia y Desarrollo Personal y Social                                 " 3
                        "05"	"Experiencia y Desarrollo de la Expresión, Comunicación y Representación    " 4
                        "06"	"Experiencia y Desarrollo de la Relación con el Entorno                     " 5
                        "07"	"Competencias Ciudadanas                                                    " 6
                        "08"	"Complementaria                                                             " 7
                        "09"	"Alertas                                                                    " 8
                    */                                                                 
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // DAR FORMATO. -1 en la matriz
                        //Colores, ancho de l�nea y fuente en negrita
                        $this->fpdf->SetFillColor(200,200,200);
                        $this->fpdf->SetTextColor(0);
                        $this->fpdf->SetFont('Times','B',12);
        
                        //Restauraci�n de colos y fuentes
                        $this->fpdf->SetFillColor(212, 230, 252);
                        $this->fpdf->SetTextColor(0);
                        $this->fpdf->SetFont('Times','',10);	
                    ///////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////////	
                    // VALOR DE LA CALIFIACION SEGUN PERIODO 
                        $this->fpdf->SetFont('Arial', '', '7');
                    // INFORMACION DE LA ARRAY EXTRAER DE LA MATRIZ
                    // n.º, NIE. NOMBRE DEL ESTUDIANTE
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$fila,1,0,'L',false);            
                        $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$codigo_nie,1,0,'L',false);            
                        $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],$nombre_estudiante,1,0,'L',false);            
                    //  validar la calificación promedio.
                        for ($na=1; $na <= $valor_actividades; $na++) { 
                            if($na == 4 || $na == 8 || $na == 12 || $na == 16 || $na == 20){
                                $this->fpdf->SetFillColor(218,215,215);
                                $this->fpdf->SetFont('Arial', 'B', '7');
                                // Cerificar si la calicación es igual a 0
                                if($nota_actividades_0[$na] == 0){
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C',true);
                                }else{
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[$na],1,0,'C',true);
                                }
                                    
                                $this->fpdf->SetFont('Arial', '', '7');
                                $this->fpdf->SetFillColor(255,255,255);
                            }else{
                                //
                                $this->fpdf->SetFont('Arial', '', '7');
                                $this->fpdf->SetFillColor(255,255,255);
                                // Cerificar si la calicación es igual a 0
                                if($nota_actividades_0[$na] == 0){
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C',true);
                                }else{
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[$na],1,0,'C',true);
                                }
                            }
                        }
                        // NOTA PROMEDIO FINAL.
                        $this->fpdf->SetFont('Arial', 'B', '7');
                            // NOTA PROMEDIO FINAL
                                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[23],1,0,'C');
                            // NOTA RECUPERACION  1
                                if($nota_actividades_0[21] == 0){
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C');
                                }
                                else{
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[21],1,0,'C');
                                }
                            // NOTA RECUPERACION  2
                                if($nota_actividades_0[22] == 0){
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C');
                                }
                                else{
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[22],1,0,'C');
                                }
                            // NOTA PROMEDIO FINAL.
                            if($nota_actividades_0[23] == 0){
                                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C');
                            }
                            else{
                                // CALCULAR SI ES APROBADO O REPROBRADO
                                $result = resultado_final($codigo_modalidad, $nota_actividades_0[21],$nota_actividades_0[22],$nota_actividades_0[23]);
                                
                                    if($result[0] == "R"){
                                        $this->fpdf->SetTextColor(255,0,0);
                                    } 
                                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[23],1,0,'C');
                                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$result[0],1,1,'C');
                                        // restaurar el color
                                        $this->fpdf->SetTextColor(0);
                                        $this->fpdf->SetFillColor(255,255,255);
                            }
                        $this->fpdf->SetFont('Arial', '', '7');
                }else{
                    //Mostrar solamente una vez.
                    ///////////////////////////////////////////////////////////////////////////////////////////////////
                    /////VERIFICAR ENCABEZADO de AREA DE ASIGNATURAS///////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////////		
                    /*	"01"	"Básica                                                                     " 0
                        "02"	"Formativa                                                                  " 1
                        "03"	"Técnica                                                                    " 2
                        "04"	"Experiencia y Desarrollo Personal y Social                                 " 3
                        "05"	"Experiencia y Desarrollo de la Expresión, Comunicación y Representación    " 4
                        "06"	"Experiencia y Desarrollo de la Relación con el Entorno                     " 5
                        "07"	"Competencias Ciudadanas                                                    " 6
                        "08"	"Complementaria                                                             " 7
                        "09"	"Alertas                                                                    " 8
                    */                                                                 
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // DAR FORMATO. -1 en la matriz
                        //Colores, ancho de l�nea y fuente en negrita
                        $this->fpdf->SetFillColor(200,200,200);
                        $this->fpdf->SetTextColor(0);
                        $this->fpdf->SetFont('Times','B',12);
    
                        //Restauraci�n de colos y fuentes
                        $this->fpdf->SetFillColor(212, 230, 252);
                        $this->fpdf->SetTextColor(0);
                        $this->fpdf->SetFont('Times','',10);	
                    ///////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////////	                    	
                    // VALOR DE LA CALIFIACION SEGUN PERIODO 
                    $this->fpdf->SetFont('Arial', '', '7');
                    // INFORMACION DE LA ARRAY EXTRAER DE LA MATRIZ
                    // n.º, NIE. NOMBRE DEL ESTUDIANTE
                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$fila,1,0,'L',false);            
                    $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$codigo_nie,1,0,'L',false);            
                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],$nombre_estudiante,1,0,'L',false);         
                    //
                        for ($na=1; $na <= $valor_actividades; $na++) { 
                            if($na == 4 || $na == 8 || $na == 12 || $na == 16 || $na == 20){
                                $this->fpdf->SetFillColor(218,215,215);
                                $this->fpdf->SetFont('Arial', 'B', '7');
                                // Cerificar si la calicación es igual a 0
                                    if($nota_actividades_0[$na] == 0){
                                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C',true);
                                    }else{
                                        //
                                        // ASIGNATUR ACOMPETENCIA CIUDADNA
                                        //
                                        if($codigo_area == '07'){
                                            $result_concepto = resultado_concepto($codigo_modalidad, $nota_actividades_0[$na]);
                                            // $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[$na],1,0,'C',true);
                                            //$this->fpdf->SetX
                                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$result_concepto,1,'TB','R',true);                                     
                                        }else{
                                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[$na],1,0,'C',true);
                                        }
                                        
                                    }
                                $this->fpdf->SetFont('Arial', '', '7');
                                $this->fpdf->SetFillColor(255,255,255);
                            }else{
                                $this->fpdf->SetFont('Arial', '', '7');
                                $this->fpdf->SetFillColor(255,255,255);
                                // Cerificar si la calicación es igual a 0
                                // VALIDAR CUANDO LA ASIGNATURA ES COMPETENCIA CIUDADANA
                                // BUENO, MUY BUENO, EXCELENTE O VACIO
                                if($nota_actividades_0[$na] == 0){
                                        if($codigo_area == '07'){
                                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'','TB',0,'C',true);
                                        }else{
                                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C',true);
                                        }
                                    
                                }else{
                                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[$na],1,0,'C',true);
                                }
                            }
                                
                        }
                            // NOTA PROMEDIO FINAL.
                            $this->fpdf->SetFont('Arial', 'B', '7');
                            // NOTA PROMEDIO FINAL
                                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[23],1,0,'C');
                            // NOTA RECUPERACION  1
                                if($nota_actividades_0[21] == 0){
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C');
                                }
                                else{
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[21],1,0,'C');
                                }
                            // NOTA RECUPERACION  2
                                if($nota_actividades_0[22] == 0){
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C');
                                }
                                else{
                                    $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[22],1,0,'C');
                                }
                            // NOTA PROMEDIO FINAL.
                            if($nota_actividades_0[23] == 0){
                                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'C');
                            }
                            else{
                                // CALCULAR SI ES APROBADO O REPROBRADO
                                    $result = resultado_final($codigo_modalidad, $nota_actividades_0[21],$nota_actividades_0[22],$nota_actividades_0[23]);
                                        if($result[0] == "R"){
                                            $this->fpdf->SetTextColor(255,0,0);
                                        } 
                                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[23],1,0,'C');
                                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$result[0],1,1,'C');
                                    
                                    $this->fpdf->SetTextColor(0);
                                    $this->fpdf->SetFillColor(255,255,255);
                            }
                }
                // incremento de variable que controla la fila
                    $fila++;
            } // FIN DEL FOREACH
        //
        //  DATOS AL FINAL DE LAS CALIFICACIONES
        //
            $ultima_linea = $this->fpdf->GetY();
            $this->fpdf->SetY($ultima_linea+40);
            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nombre_director,0,0,'L');
            $this->fpdf->Cell(120,$alto_cell[0],'',0,0,'L');
            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nombre_personal_,0,1,'L');
            
            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'Director',0,0,'L');
            $this->fpdf->Cell(120,$alto_cell[0],'',0,0,'L');
            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'Docente responsable',0,1,'L');
        //  Información del docente responsable de la sección.

            
        // agregar firma y sello
            $this->fpdf->image(URL::to($firma_director),15,$ultima_linea+25,40,15);
            $this->fpdf->image(URL::to($sello_direccion),40,$ultima_linea+20,25,25);
        // Construir el nombre del archivo.
            $nombre_archivo = $nombre_modalidad.' '.$nombre_grado . ' ' . $nombre_seccion . ' ' . $nombre_turno . '.pdf';
        // Salida del pdf.
            $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
            $this->fpdf->Output($nombre_archivo,$modo);
                exit;
    }    //
}
