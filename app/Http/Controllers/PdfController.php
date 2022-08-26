<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use GuzzleHttp\Psr7\Header;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PdfController extends Controller
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
            $codigo_nie = $EstudianteMatricula[0];
            $codigo_alumno = $EstudianteMatricula[1];
            $codigo_matricula = $EstudianteMatricula[2];
            $codigo_gradoseccionturnomodalidad = $EstudianteMatricula[3];
            $codigo_modalidad = substr($codigo_gradoseccionturnomodalidad,6,2);
            $codigo_grado = substr($codigo_gradoseccionturnomodalidad,0,2);
            $codigo_annlectivo = $EstudianteMatricula[4];
            $codigo_institucion = $EstudianteMatricula[5];
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
                //* incrementar valor de la fila para la array asociativa
                $fila_array_asignatura++;
            } // FIN DEL FOREACH para los datos de la insitucion.
        /*
        print_r($datos_asignatura);
        $buscar = array_search("02", $datos_asignatura['codigo']);
        
            $Nombre = $datos_asignatura['nombre'][$buscar];
            $Codigo = $datos_asignatura['codigo'][$buscar];
            $Concepto = $datos_asignatura['concepto'][$buscar];
            print "<br>";
            echo 'Mi amigo ' . $Nombre. ' tiene ' . $Codigo . ' años y vinve en ' . $Concepto;
        
            echo 'Mi amigo ' . $Nombre. ' tiene ' . $Edad . ' años y vinve en ' . $Ciudad;*/
        // Cabecera - INFORMACION GENERAL DE LA INSTITUCION
            $EstudianteInformacionInstitucion = DB::table('informacion_institucion')
            ->select('id_institucion','codigo_institucion','nombre_institucion','telefono_uno','logo_uno','direccion_institucion'
                    )
            ->where([
                ['id_institucion', '=', $codigo_institucion],
                ])
            ->orderBy('id_institucion','asc')
            ->get();
            // extgraer datos para el encabezado
            $alto_cell = array('5'); $ancho_cell = array('60','6','24');
            foreach($EstudianteInformacionInstitucion as $response_i){  //Llenar el arreglo con datos
                $nombre_institucion = utf8_decode(trim($response_i->nombre_institucion));
                $codigo_institucion = utf8_decode(trim($response_i->codigo_institucion));
                $logo_uno = "/img/".utf8_decode(trim($response_i->logo_uno));

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
            ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.nombre_completo',"a.apellido_paterno",'a.apellido_materno','am.id_alumno_matricula as codigo_matricula','n.id_notas','n.codigo_asignatura',
                     'bach.nombre AS nombre_modalidad', 'gr.nombre as nombre_grado', 'sec.nombre as nombre_seccion','tur.nombre as nombre_turno',
                        'n.nota_a1_1', 'n.nota_a2_1', 'n.nota_a3_1', 'n.nota_p_p_1', 'n.nota_a1_2', 'n.nota_a2_2', 'n.nota_a3_2', 'n.nota_p_p_2',
                        'n.nota_a1_3', 'n.nota_a2_3', 'n.nota_a3_3', 'n.nota_p_p_3', 'n.nota_a1_4', 'n.nota_a2_4', 'n.nota_a3_4', 'n.nota_p_p_4',
                        'n.nota_a1_5', 'n.nota_a2_5', 'n.nota_a3_5', 'n.nota_p_p_5', 'n.nota_final', 'n.recuperacion', 'n.nota_recuperacion_2',
                        'asig.codigo_area',
                    DB::raw("TRIM(CONCAT(BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno), CAST(' ' AS VARCHAR), BTRIM(a.nombre_completo))) as full_name"),
                    DB::raw("TRIM(CONCAT(BTRIM(a.nombre_completo), CAST(' ' AS VARCHAR), BTRIM(a.apellido_paterno), CAST(' ' AS VARCHAR), BTRIM(a.apellido_materno))) as full_nombres_apellidos")
                    )
            ->where([
                ['codigo_matricula', '=', $codigo_matricula],
                ])
            ->orderBy('n.id_notas','asc')
            ->get();

            // variales de entorno para mostrar la información.
            $fila = 1;             
            $this->fpdf->SetX(30); 
            foreach($EstudianteBoleta as $response){  //Llenar el arreglo con datos
                $nombre_completo = utf8_decode(trim($response->full_nombres_apellidos));
                $codigo_nie = utf8_decode(trim($response->codigo_nie));
                $nombre_modalidad = utf8_decode(trim($response->nombre_modalidad));  
                $nombre_grado = utf8_decode(trim($response->nombre_grado));  
                $nombre_seccion = utf8_decode(trim($response->nombre_seccion));  
                $nombre_turno = utf8_decode(trim($response->nombre_turno));                
                $codigo_asignatura = utf8_decode(trim($response->codigo_asignatura));
                $codigo_area = utf8_decode(trim($response->codigo_area));
                $nota_final = utf8_decode(trim($response->nota_final));
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
                    $valor_periodo = 2; $valor_actividades = 12; $ancho_area_asignatura = 138;
                }else if($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){   // EDUCACION MEDIA
                    $valor_periodo = 3; $valor_actividades = 16; $ancho_area_asignatura = 162;
                }else if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){   // NOCTURNA
                    $valor_periodo = 4; $valor_actividades = 20; $ancho_area_asignatura = 186;
                }else{
                    $valor_periodo = 2; $valor_actividades = 12; $ancho_area_asignatura = 86;    // DEFAULT PUEDE SER PARVULARIA
                }

                if($fila == 1){
                    // LLAMAR A LA FUNCION QUE POSEE EL ENCAVEZADO DE CADA REA DE LA ASIGNTURA
                       // EncabezadoCatalogoAreaAsignatura($codigo_area);
                    //
                    $this->fpdf->Cell(40,$alto_cell[0],"Estudiante",1,0,'L');       
                    $this->fpdf->Cell(135,$alto_cell[0],$codigo_nie . " - " . $nombre_completo,1,1,'L');       
                    $this->fpdf->SetX(30); 
                    $this->fpdf->Cell(40,$alto_cell[0],utf8_decode("Modalidad de Atención"),1,0,'L');       
                    $this->fpdf->Cell(135,$alto_cell[0],$nombre_modalidad,1,1,'L');       
                    $this->fpdf->SetX(30); 
                    $this->fpdf->Cell(15,$alto_cell[0],"Grado",1,0,'L');       
                    $this->fpdf->Cell(95,$alto_cell[0],$nombre_grado,1,0,'L');       

                    $this->fpdf->Cell(15,$alto_cell[0],utf8_decode("Sección"),1,0,'L');       
                    $this->fpdf->Cell(10,$alto_cell[0],$nombre_seccion,1,0,'C');       
                    
                    $this->fpdf->Cell(20,$alto_cell[0],"Turno",1,0,'L');       
                    $this->fpdf->Cell(20,$alto_cell[0],$nombre_turno,1,1,'C');       
                    $this->fpdf->ln();

                    $this->fpdf->SetX(30); 
                    $this->fpdf->SetFont('Arial', 'B', '7');
                    $this->fpdf->Cell(27,$alto_cell[0],"A1->Actividad 1 (35%)",'LR',0,'L');       
                    $this->fpdf->Cell(27,$alto_cell[0],"A2->Actividad 2 (35%)",'LR',0,'L');       
                    $this->fpdf->Cell(35,$alto_cell[0],"PO->Prueba Objetiva (30%)",'LR',0,'L'); 
                    $this->fpdf->Cell(35,$alto_cell[0],"PP->Promedio Periodo",'LR',0,'L');             
                    $this->fpdf->Cell(30,$alto_cell[0],"PF->Promedio Final",'LR',1,'L');             
                    $this->fpdf->ln();
                    // cabecera de la tabla de calificaicone4s por periodo
                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],"",1,0,'L');
                    for ($pp=0; $pp <= $valor_periodo; $pp++) { 
                        if($valor_periodo == $pp){
                            $this->fpdf->Cell($ancho_cell[2],$alto_cell[0],$periodos_a[$pp],1,1,'C');
                        }else{
                            $this->fpdf->Cell($ancho_cell[2],$alto_cell[0],$periodos_a[$pp],1,0,'C');
                        }
                    }
                    // COMPONENTE DE ESTUDIO Y PRIMER FILA DE LAS ACTIVIDADES Y PROMEDIOS
                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],"Componente de Estudio",1,0,'L');             
                    for ($pp=0; $pp <= $valor_periodo; $pp++) { 
                        for ($ap=0; $ap < count($actividad_periodo) -1; $ap++) { 
                                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$actividad_periodo[$ap],1,0,'C');
                        }
                        if($valor_periodo == $pp){
                            // colocar celda PF
                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$actividad_periodo[4],1,0,'C');
                        }
                    }
                        // colocar celda NR
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'NR',1,0,'C');
                        // colocar celda NR
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
    
                        //print_r($catalogo_area_asignatura_codigo);
                        //$encabezado_ = EncabezadoCatalogoAreaAsignatura($catalogo_area_asignatura_codigo, $codigo_area);
                    //	print $descripcion_area;
                        //exit;
                        // LINEA DE DIVISIÓN - PARA EL ÁREA BÁSICA.
                        if($catalogo_area_asignatura_codigo[0] == $codigo_area){
                            if($catalogo_area_basica == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[0])),1,1,'L',true);
                                $catalogo_area_basica = false;
                            }
                        }
                        //$this->fpdf->Cell(203,6,strtoupper(utf8_decode($encabezado_)),1,1,'L',true);
                        // LINEA DE DIVISIÓN - PARA EL ÁREA FORMATIVA.
                        if($catalogo_area_asignatura_codigo[1] == $codigo_area){
                            if($catalogo_area_formativa == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[1])),1,1,'L',true);
                                $catalogo_area_formativa = false;
                            }
                        }
                        // LINEA DE DIVISIÓN - PARA EL ÁREA TÉCNICA.
                        if($catalogo_area_asignatura_codigo[2] == $codigo_area){
                            if($catalogo_area_tecnica == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[2])),1,1,'L',true);
                                $catalogo_area_tecnica = false;
                            }
                        }
                        // LINEA DE DIVISIÓN - PARA EL ÁREA COMPETENCIAS CIUDADANAS.
                        if($catalogo_area_asignatura_codigo[6] == $codigo_area){
                            if($catalogo_area_cc == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[6])),1,1,'L',true);
                                $catalogo_area_cc = false;
                            }
                        }
                        
                        // LINEA DE DIVISIÓN - PARA EL ÁREA COMPLEMENTARIA.
                        if($catalogo_area_asignatura_codigo[7] == $codigo_area){
                            if($catalogo_area_complementaria == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[7])),1,1,'L',true);
                                $catalogo_area_complementaria = false;
                            }
                        }
                        //Restauraci�n de colos y fuentes
                        $this->fpdf->SetFillColor(212, 230, 252);
                        $this->fpdf->SetTextColor(0);
                        $this->fpdf->SetFont('Times','',10);	
                    ///////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////////	
                    // VALOR DE LA CALIFIACION SEGUN PERIODO 
                        $this->fpdf->SetFont('Arial', '', '7');
                    // INFORMACION DE LA ARRAY EXTRAER DE LA MATRIZ
                            $buscar = array_search($codigo_asignatura, $datos_asignatura['codigo']);
                            
                                $Nombre = $datos_asignatura['nombre'][$buscar];
                                $Codigo = $datos_asignatura['codigo'][$buscar];
                                $Concepto = $datos_asignatura['concepto'][$buscar];
                                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],$codigo_asignatura . "-" . substr($Nombre,0,60),1,0,'L');     
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
                        $this->fpdf->SetFont('Arial', 'B', '7');
                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[23],1,1,'C');
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
    
                        //print_r($catalogo_area_asignatura_codigo);
                    //	print $descripcion_area;
                        //exit;
                        // LINEA DE DIVISIÓN - PARA EL ÁREA BÁSICA.
                      /* if($catalogo_area_asignatura_codigo[0] == $codigo_area){
                            if($catalogo_area_basica == true){
                                $this->fpdf->Cell(203,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[0])),1,1,'L',true);
                                $catalogo_area_basica = false;
                            }
                        }*/
                        // LINEA DE DIVISIÓN - PARA EL ÁREA FORMATIVA.
                        if($catalogo_area_asignatura_codigo[1] == $codigo_area){
                            if($catalogo_area_formativa == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[1])),1,1,'L',true);
                                $catalogo_area_formativa = false;
                            }
                        }
                        // LINEA DE DIVISIÓN - PARA EL ÁREA TÉCNICA.
                        if($catalogo_area_asignatura_codigo[2] == $codigo_area){
                            if($catalogo_area_tecnica == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[2])),1,1,'L',true);
                                $catalogo_area_tecnica = false;
                            }
                        }
                        // LINEA DE DIVISIÓN - PARA EL ÁREA COMPETENCIAS CIUDADANAS.
                        if($catalogo_area_asignatura_codigo[6] == $codigo_area){
                            if($catalogo_area_cc == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[6])),1,1,'L',true);
                                $catalogo_area_cc = false;
                            }
                        }
                        
                        // LINEA DE DIVISIÓN - PARA EL ÁREA COMPLEMENTARIA.
                        if($catalogo_area_asignatura_codigo[7] == $codigo_area){
                            if($catalogo_area_complementaria == true){
                                $this->fpdf->Cell($ancho_area_asignatura,6,strtoupper(utf8_decode($catalogo_area_asignatura_area[7])),1,1,'L',true);
                                $catalogo_area_complementaria = false;
                            }
                        }
                        //Restauraci�n de colos y fuentes
                        $this->fpdf->SetFillColor(212, 230, 252);
                        $this->fpdf->SetTextColor(0);
                        $this->fpdf->SetFont('Times','',10);	
                    ///////////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////////	                    	
                    // VALOR DE LA CALIFIACION SEGUN PERIODO 
                    $this->fpdf->SetFont('Arial', '', '7');
                    // INFORMACION DE LA ARRAY EXTRAER DE LA MATRIZ
                            $buscar = array_search($codigo_asignatura, $datos_asignatura['codigo']);
                            
                                $Nombre = $datos_asignatura['nombre'][$buscar];
                                $Codigo = $datos_asignatura['codigo'][$buscar];
                                $Concepto = $datos_asignatura['concepto'][$buscar];
                                    $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],$codigo_asignatura . "-" . substr($Nombre,0,40),1,0,'L');     
                    //
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
                        // PROMERIO DE LOS PERIODOS PF
                        $this->fpdf->SetFont('Arial', 'B', '7');
                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nota_actividades_0[23],1,1,'C');
                        $this->fpdf->SetFont('Arial', '', '7');
                }
                
                // incremento de variable que controla la fila
                    $fila++;
            } // FIN DEL FOREACH
        
        // FIN DEL FPDF
            $this->fpdf->Output();
                exit;
    }    //
    function EncabezadoCatalogoAreaAsignatura($codigo_area)
    {

    }   
}
