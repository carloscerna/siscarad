<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use GuzzleHttp\Psr7\Header;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PdfRPGController extends Controller
{
    protected $fpdf;

    public function __construct()
    {
        $this->fpdf = new Fpdf('L','mm','Legal');	// Formato Letter;
    }
    
    public function index($id) 
    {
        // Configurar PDF.
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->AddPage();
            $this->fpdf->SetMargins(5, 5, 5);
            $this->fpdf->SetAutoPageBreak(true,5);
            $this->fpdf->SetX(30);
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
        //  CREACION DE MATRIZ CON ANCHO Y ALTO DE CADA CELDA.
            $alto_cell = array('5','40'); $ancho_cell = array('60','6','24','30','12');        
        //  consulta a las diferentes tablas
            // CATALOGO ASIGNATURA
                $CatalogoAreaAsignatura = DB::table('catalogo_area_asignatura')
                ->select('codigo','descripcion')
                ->get();
                foreach($CatalogoAreaAsignatura as $response_area){  //Llenar el arreglo con datos
                    $catalogo_area_asignatura_codigo[] = (trim($response_area->codigo));
                    $catalogo_area_asignatura_area[] = (trim($response_area->descripcion));
                    //* incrementar valor de la fila para la array asociativa
                } // FIN DEL FOREACH para los datos de la insitucion.

            // RELLENAR DATOS DE LAS ASIGNATURAS.
                $AsignacionAsignatura = DB::table('a_a_a_bach_o_ciclo as aaa')
                ->join('asignatura as a','a.codigo','=','aaa.codigo_asignatura')
                ->join('catalogo_area_asignatura AS cat_area','cat_area.codigo','=','a.codigo_area')
                    ->select('aaa.orden',
                            'a.nombre as nombre_asignatura','a.codigo as codigo_asignatura','a.codigo_cc as concepto_calificacion','a.codigo_area',
                            'cat_area.descripcion as nombre_area'
                        )
                ->where([
                    ['codigo_bach_o_ciclo', '=', $codigo_modalidad],
                    ['codigo_grado', '=', $codigo_grado],
                    ['codigo_ann_lectivo', '=', $codigo_annlectivo],
                    ])
                ->orderBy('aaa.orden','asc')
                ->get();
                // extgraer datos para el encabezado
                $datos_asignatura = array(); $fila_array_asignatura = 0; $count_asignaturas = array();
                $datos_asignatura = [
                    "codigo" => [""],
                    "nombre" => [""],
                    "concepto" => [""],
                    "codigo_area" => [""],
                    "nombre_area" => [""]
                ];       
                 //echo "<pre>";
                // print_r($AsignacionAsignatura);
                // echo "</pre>";
                foreach($AsignacionAsignatura as $response_i){  //Llenar el arreglo con datos
                    $nombre_asignatura_a = utf8_decode(trim($response_i->nombre_asignatura));
                    $codigo_asignatura_a = utf8_decode(trim($response_i->codigo_asignatura));
                    $concepto_calificacion_a = utf8_decode(trim($response_i->concepto_calificacion));
                    $codigo_area_a = utf8_decode(trim($response_i->codigo_area));
                    $nombre_area_a = utf8_decode(trim($response_i->nombre_area));

                    $count_asignaturas[] = $codigo_area_a;
                    $datos_asignatura["codigo"][$fila_array_asignatura] = $codigo_asignatura_a;
                    $datos_asignatura["nombre"][$fila_array_asignatura] = $nombre_asignatura_a;
                    $datos_asignatura["concepto"][$fila_array_asignatura] = $concepto_calificacion_a;
                    $datos_asignatura["codigo_area"][$fila_array_asignatura] = $codigo_area_a;                        
                    $datos_asignatura["nombre_area"][$fila_array_asignatura] = $nombre_area_a;                        
                    //* incrementar valor de la fila para la array asociativa
                    $total_asignaturas = count($count_asignaturas);
                    $fila_array_asignatura++;
                } // FIN DEL FOREACH para los datos de la insitucion.
                    // CONTAR CUENTAS ASIGANTURAS HAY POR AREA...
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
                        // variables para el ancho de cada area.
                            $codigo_area_existentes = array();
                        // Obtenemos las repeticiones
                            $counter = array_count_values($datos_asignatura['codigo_area']);
                        // ordenamos las repeticiones
                            //ksort($counter);
                        // Recorremos las repeticiones cib kis codigos de las areas y cantidad.
                            foreach($counter as $key => $cantidad) {
                                $ancho_area[] = $cantidad;
                                $codigo_area_existentes[] = $key;
                            }
                    //
                    //
            // Cabecera - DOCENE ENCARGADO DE LA SECCION
                $EncargadoGrado = DB::table('encargado_grado as eg')
                ->join('personal as p','p.id_personal','=','eg.codigo_docente')
                ->select('p.id_personal', 'p.firma',
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
                    $firma_docente = utf8_decode(trim($response_eg->firma));
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
                // extgraer datos para el encabezado INFORMACION DE LA INSTITUCION
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
            // cabecera informacion de la boleta de calificiaones
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
                        'n.id_notas','n.codigo_asignatura', 'n.orden',
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
                            ['am.retirado', '=', 'f'],
                            ])
                        ->orderBy('full_name','asc')
                        ->orderBy('orden','asc')
                        ->get();
            //  ************************************************************************************************************
            //  INICIA EL PROCESO DE RECORRER LA INFORMACION PARA LA BOLETA DE CALIFICACIONES.
            //  variales de entorno para mostrar la información.
            //  ************************************************************************************************************
         /* echo "<pre>";
         print_r($EstudianteBoleta);
         echo "</pre>"; */
     
            $fila = 1; $fila_asignatura = 0; $fila_numero = 1; $fill = true;
            $this->fpdf->SetX(30); 
            foreach($EstudianteBoleta as $response){  //Llenar el arreglo con datos
                // VARIABLES CON LA NFORMACIONES DEL ESTUDIANTE.
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
                    $nota_recuperacion_1 = utf8_decode(trim($response->recuperacion));
                    $nota_recuperacion_2 = utf8_decode(trim($response->nota_recuperacion_2));
                    $nombre_foto = (trim($response->foto));
                    $codigo_genero = (trim($response->codigo_genero));
                // NOTA ACTIVIDAD 1, 2 Y PO, NOTA PERIODO 1
                    $nota_actividades_0 = array('', $response->recuperacion, $response->nota_recuperacion_2, $response->nota_final);      // 1, 2, 3
                // MATRICES y VARIABLES DE CONTEO.
                    $periodos_a = array('PERIODO 1', 'PERIODO 2', 'PERIODO 3', 'PERIODO 4', 'PERIODO 5', 'PROMEDIO FINAL', 'R');
                    $actividad_periodo = array('NF');
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
                // LLAMAR A LA FUNCION QUE POSEE EL ENCABEZADO DE CADA REA DE LA ASIGNTURA
                    if($fila == 1){
                        // DATOS DE LA PRIMERA LINEA
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
                        // NOMBRE DEL ENCARGADO DE GRADO, Y ENCARGADO DE LA ASIGNATURA
                            $this->fpdf->SetX(30); 
                            $this->fpdf->Cell(55,$alto_cell[0],"Encargado de Grado: ",1,0,'L');       
                            $this->fpdf->Cell(120,$alto_cell[0],$nombre_personal_,1,1,'L');       

                            $this->fpdf->ln(); 
                            $this->fpdf->SetFont('Arial', 'B', '7');
                        // n.º, NIE. NOMBRE DEL ESTUDIANTE
                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'','LT',0,'L',false);            
                            $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'','T',0,'L',false);            
                            $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'','T',0,'L',false);            
                        //  DEFINIR ANCHO DE TITULOS PARA AREA, ASIGNATURAS Y OTROS
                            $mas_ancho = 12; $espacio = 0; 
                            $ancho_titulo_asignatura = count($datos_asignatura['nombre']) * $mas_ancho;
                        // TITULO 1
                            $this->fpdf->SetFont('Arial', 'B', '10');
                                $this->fpdf->Cell($ancho_titulo_asignatura,$alto_cell[0],'COMPONENTES DEL PLAN DE ESTUDIO',1,1,'C');
                        // COMPONENTE DE ESTUDIO Y PRIMER FILA DE LAS ACTIVIDADES Y PROMEDIOS
                        // TITULO 2
                            $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'','L',0,'L',false);            
                            $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',0,0,'L',false);            
                            $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'',0,0,'C');       
                            // LINEA DE DIVISIÓN - PARA EL ÁREA BÁSICA.
                            $this->fpdf->SetFont('Arial', 'B', '10');
                                //recorrer matriz codigos area exsitentes.
                                    for ($oi=0; $oi < count($codigo_area_existentes); $oi++) { 
                                        // INFORMACION DEL CODIGO, NOMBRE DE AREA, CUANTAS VECES EXISTE EL VALOR
                                        $buscar = array_search($codigo_area_existentes[$oi], $datos_asignatura['codigo_area']);
                                        // guscar nombre del area en la matriz.
                                        $Nombre = $datos_asignatura['nombre_area'][$buscar];
                                            $this->fpdf->Cell($mas_ancho*$ancho_area[$oi],$alto_cell[0],$Nombre,1,0,'C');     
                                    }
                                $this->fpdf->ln();
                        // TITULO 3
                            // INFORMACION DE LAS ASIGNATURAS. NOMBRES y definir el ancho para ASIGNATURAS Y AREAS.
                                $this->fpdf->Cell($ancho_cell[1],$alto_cell[1],utf8_decode('N.º'),1,0,'C',false);            
                                $this->fpdf->Cell($ancho_cell[4],$alto_cell[1],'NIE',1,0,'C',false);            
                                $this->fpdf->Cell($ancho_cell[0],$alto_cell[1],"NOMINA DE ESTUDIANTES",1,0,'C');       
                            // nombre de asignaturas
                                for ($ij=0; $ij < count($datos_asignatura['nombre']); $ij++) { 
                                    $this->fpdf->Rect(83+$espacio,45,12,40);
                                    $this->fpdf->SetFont('Arial', '', '9');
                                        $this->fpdf->RotatedTextMultiCell(83+$espacio,85,$datos_asignatura['nombre'][$ij],90);
                                    $this->fpdf->SetFont('Arial', '', '7');
                                    $mas_ancho = $mas_ancho + 12;
                                    $espacio = $espacio + 12;
                                }
                        // MODIFICAR "X" Y "Y" PARA UBICAR LA NOMINA DE ESTUDIANTES
                            $this->fpdf->SetXY(5,85);
                }
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // CUANDO HAY MAS DE UNA ASIGANTURA DEL MISMO ESTUDIANTE
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                 // CALCULAR SI ES APROBADO O REPROBRADO
                //Restauraci�n de colos y fuentes
                    $this->fpdf->SetFillColor(212, 230, 252);
                    $this->fpdf->SetTextColor(0,0,0);
                ///////////////////////////////////////////////////////////////////////////////////////////////////
                // VALOR DE LA CALIFIACION 
                    $this->fpdf->SetFont('Arial', '', 7);
                ///
                if($fila_asignatura == $total_asignaturas){
                    // SALTO Y REINICIAR VALORES DE VARIABLES.
                    $this->fpdf->ln();
                    $fila_asignatura = 0; $fila_numero++; $fill=!$fill;
                }

                if($fila_asignatura == 0)
                {
                    // INFORMACION DE LA ARRAY EXTRAER DE LA MATRIZ
                    // n.º, NIE. NOMBRE DEL ESTUDIANTE
                        $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$fila_numero,1,0,'L',$fill);            
                        $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$codigo_nie,1,0,'L',$fill);            
                        $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],$nombre_estudiante,1,0,'L',$fill); 
                    // VERIFICAR SI LA CALIFIACION ES MENOR A 5 O 6 SEGUN MODALIDAD
                        if($codigo_area == '01' || $codigo_area == '02' || $codigo_area == '03' || $codigo_area == '08'){
                            $result = resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_final);                                
                            if($result[0] == "R"){
                                $this->fpdf->SetTextColor(255,0,0);
                            } 
                        }
                    // PRIMERA NOTA FINAL.           
                         $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$result[1],1,0,'C', $fill);
                }else{
                    if($codigo_area == '07')
                    {
                        $result_concepto = resultado_concepto($codigo_modalidad, $nota_final);
                            if($result_concepto[0] == "R"){
                                $this->fpdf->SetTextColor(255,0,0);
                            } 
                            //
                                $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$result_concepto,1,0,'C', $fill);
                    }else{
                        // VERIFICAR SI LA CALIFIACION ES MENOR A 5 O 6 SEGUN MODALIDAD
                        if($codigo_area == '01' || $codigo_area == '02' || $codigo_area == '03' || $codigo_area == '08'){
                            $result = resultado_final($codigo_modalidad, $nota_recuperacion_1, $nota_recuperacion_2, $nota_final);                                
                            if($result[0] == "R"){
                                $this->fpdf->SetTextColor(255,0,0);
                            } 
                        }
                        // NOTA FINAL
                            $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],$result[1],1,0,'C', $fill);
                    }
                }
                    // incremento de variable que controla la fila
                    $fila_asignatura++; $fila++; 
                     // restaurar el color
                     $this->fpdf->SetTextColor(0,0,0);
                     //$this->fpdf->SetFillColor(255,255,255);
            } // FIN DEL FOREACH
        //
        // agregar filas faltantes
        //
            // Línea diagonal para los cuadros. en la segunda página. 
            $numero = $fila_numero; $linea_faltante = 0;
            $this->fpdf->Ln();
            if($numero > 25){
                // Colocar línea diagonal si es menor a 19.
                    $valor_y1 = 0;
                    $linea_faltante =  50 - $numero;
                    $numero_p = $numero - 1;
            }
            		// Escribir líneas faltantes.  
            for($i=0;$i<=$linea_faltante;$i++)
            {
                // n.º, NIE. NOMBRE DEL ESTUDIANTE
                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'',1,0,'L',$fill);            
                $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',1,0,'L',$fill);            
                $this->fpdf->Cell($ancho_cell[0],$alto_cell[0],'',1,0,'L',$fill); 
                // Para el fondo de la fila.
                $fill=!$fill;
                for($j=1;$j<=$total_asignaturas;$j++){
                    $this->fpdf->Cell($ancho_cell[4],$alto_cell[0],'',1,0,'L',$fill);            
                    //
                    if($j == $total_asignaturas){
                        $ultima_columna = $this->fpdf->GetX();            
                    }
                }
                $this->fpdf->ln();
            }
        //
        //  DATOS AL FINAL DE LAS CALIFICACIONES
        //
            $ultimo_espaciado = 10; $ultima_fila = 100;
            //$ultima_columna = $this->fpdf->GetX();
            // datos del docente
            $this->fpdf->SetXY($ultima_columna + $ultimo_espaciado,$ultima_fila);
                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nombre_personal_,0,0,'L');
            $this->fpdf->SetXY($ultima_columna + $ultimo_espaciado,$this->fpdf->GetY()+5);
                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'Docente responsable',0,0,'L');
                // FOTO DEL ESTUDIANTE.
                if(!empty($firma_docente)){
                    if (file_exists('c:/wamp64/www/registro_academico/img/firmas/'.$codigo_institucion.'/'.$firma_docente))
                    {
                        $img = 'c:/wamp64/www/registro_academico/img/firmas/'.$codigo_institucion.'/'.$firma_docente;	
                        $this->fpdf->image($img,$this->fpdf->GetX(),$this->fpdf->GetY()-30,25,30);
                    }
                }
                    
        //

            // información del director
            $this->fpdf->SetXY($ultima_columna + $ultimo_espaciado,$this->fpdf->GetY()+40);
                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],$nombre_director,0,0,'L');
            $this->fpdf->SetXY($ultima_columna + $ultimo_espaciado,$this->fpdf->GetY()+5);
                $this->fpdf->Cell($ancho_cell[1],$alto_cell[0],'Director',0,0,'L');
        // agregar firma y sello
            $this->fpdf->image(URL::to($firma_director),$this->fpdf->GetX(),$this->fpdf->GetY()-20,40,15);
            $this->fpdf->image(URL::to($sello_direccion),$this->fpdf->GetX(),$this->fpdf->GetY()+15,25,25);
        // Construir el nombre del archivo.
            $nombre_archivo = $nombre_modalidad.' '.$nombre_grado . ' ' . $nombre_seccion . ' ' . $nombre_turno . '.pdf';
        // Salida del pdf.
            $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
            $this->fpdf->Output($nombre_archivo,$modo);
                exit;
    }    //
}
