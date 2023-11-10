<?php

namespace App\Http\Controllers;

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PdfRLyPController extends Controller
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
        $this->fpdf->SetAutoPageBreak(true,20);
        $this->fpdf->SetX(30);
// DECLARAR VARIABLES PARA LAS MATRICES.
//
$numero = 1; $margen_inferior = 10; $margen_superior = 20;
//
        //(numero, tipo de permiso, turno, fecha, dia, hora, minutos)
            $w=array(5,90,30,20,15,15,15); //determina el ancho de las columnas
            $w2=array(40,45); //determina el ancho de las columnas
        // colores del fondo, texto, línea.
            $this->fpdf->SetFillColor(230,227,227);
            $fill=false; $num=1;
            $fill2=true;
            $header=array('Nº','Tipo de Licencia o Permiso','Turno','Fecha','Día','Hora','Minutos');        
    // TABLERO - NOMBRE ANNLECTIVO - CODIGO_ANNLECTIVO - CODIGO PERSONAL - CONDIGO INSTITUCION
        $PersonalContratacion = explode("-",$id);
    // Examninar la palabra...
        if($PersonalContratacion[0] == "Tablero"){
            $nombre_annlectivo = $PersonalContratacion[1];
            $codigo_annlectivo = $PersonalContratacion[2];
            $codigo_personal = $PersonalContratacion[3];
            $codigo_institucion = $PersonalContratacion[4];
        }else{

        }
        //  CREACION DE MATRIZ CON ANCHO Y ALTO DE CADA CELDA.
            $alto_cell = array('5','40','12'); $ancho_cell = array('60','6','24','30','12');        
        // extraer los datos de tipo contratacion, turno.

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
             $nombre_institucion = mb_convert_encoding(trim($response_i->nombre_institucion),"ISO-8859-1","UTF-8");
             $nombre_director = mb_convert_encoding(trim($response_i->full_name),"ISO-8859-1","UTF-8");
             $codigo_institucion = mb_convert_encoding(trim($response_i->codigo_institucion),"ISO-8859-1","UTF-8");
             $logo_uno = "/img/".mb_convert_encoding(trim($response_i->logo_uno),"ISO-8859-1","UTF-8");
             $firma_director = "/img/".mb_convert_encoding(trim($response_i->logo_dos),"ISO-8859-1","UTF-8");
             $sello_direccion = "/img/".mb_convert_encoding(trim($response_i->logo_tres),"ISO-8859-1","UTF-8");
                //$this->fpdf->Cell(205, $alto_cell[0],mb_convert_encoding("Tipo de Contratación: " .  " - Turno: ","ISO-8859-1","UTF-8"),0,1,'C');       
         } // FIN DEL FOREACH para los datos de la insitucion.
         //
        // Cabecera - INFORMACION SOBRE LOS CODIGOS DE LIENCIAS Y PERMISOS
        	//	variables array.							
		    $codigo_licencia_o_permiso = array(); $saldo_licencia_o_permiso = array(); $imprimir = array(); $num=0;
            //
            $LicenciasPermisos = DB::table('tipo_licencia_o_permiso')
            ->select('codigo','nombre','saldo','minutos')
            ->orderBy('codigo','asc')
            ->get();
            foreach($LicenciasPermisos as $response_lyp){  //Llenar el arreglo con datos
                $codigo_licencia_o_permiso[] = mb_convert_encoding(trim($response_lyp->codigo),"ISO-8859-1","UTF-8");
                $saldo_licencia_o_permiso[] = $response_lyp->saldo;
                $minutos_licencia_o_permiso[] = $response_lyp->minutos;
            } // FIN DEL FOREACH para los datos de la insitucion.
            //
            //
            // Cabecera - DOCENTE TIPO DE CONTRATACIÓN.
            $PersonalContratacion = DB::table('personal_salario as ps')
            ->join('personal as p','p.id_personal','=','ps.codigo_personal')
            ->join('turno as tur','tur.codigo','=','ps.codigo_turno')
            ->join('tipo_contratacion as tc','tc.codigo','=','ps.codigo_tipo_contratacion')
            ->select('p.id_personal', 'p.firma', 'tur.nombre as nombre_turno', 'tc.nombre as nombre_contratacion','ps.codigo_tipo_contratacion','ps.codigo_turno',
                    DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                    )
            ->where([
                ['p.id_personal', '=', $codigo_personal]
                ])
            ->orderBy('p.id_personal','asc')
            ->get();
            // recorriendo el array
            $nombre_contratacion = array(); $nombre_turno = array();
            foreach($PersonalContratacion as $response_eg){  //Llenar el arreglo con datos
                $codigo_personal_ = mb_convert_encoding(trim($response_eg->id_personal),"ISO-8859-1","UTF-8");
                $nombre_personal_ = mb_convert_encoding(trim($response_eg->full_name),"ISO-8859-1","UTF-8");
                $firma_docente = mb_convert_encoding(trim($response_eg->firma),"ISO-8859-1","UTF-8");
                $codigo_turno[] = mb_convert_encoding(trim($response_eg->codigo_turno),"ISO-8859-1","UTF-8");
                $nombre_turno[] = mb_convert_encoding(trim($response_eg->nombre_turno),"ISO-8859-1","UTF-8");
                $nombre_contratacion[] = mb_convert_encoding(trim($response_eg->nombre_contratacion),"ISO-8859-1","UTF-8");
                $codigo_contratacion[] = mb_convert_encoding(trim($response_eg->codigo_tipo_contratacion),"ISO-8859-1","UTF-8");


            } // FIN DEL FOREACH para los datos de la insitucion.
            // recorrer la matriz para colocar los datos de cada licencia o permiso.
            for ($PersonalArray=0; $PersonalArray < count($codigo_contratacion); $PersonalArray++) { 
                	// Calcular el Disponible segùn Tipo de Contratación.
                $calculo_horas = 5;
                if($codigo_contratacion[$PersonalArray] == "05"){ // PAGADOS POR EL CDE.
                    $calculo_horas = 8;
                }
                // CABECERA POR TIPO DE CONTRATACIÓN.
                // LOGO DE LA INSTITUCIÓN
                $this->fpdf->image(URL::to($logo_uno),10,5,15,20);
                $this->fpdf->SetX(5);
                $this->fpdf->SetFont('Arial','I',14);
                    $this->fpdf->Cell(205, $alto_cell[0],"CONTROL DE LICENCIAS Y PERMISOS DEL PERSONAL DOCENTE",0,1,'C');       
                $this->fpdf->SetFont('Arial','',10);
                $this->fpdf->Cell(205, $alto_cell[0],$codigo_institucion . " - " .$nombre_institucion,0,1,'C');       
                //Colores, ancho de línea y fuente en negrita
                $this->fpdf->SetFillColor(255,255,255);$this->fpdf->SetTextColor(0);$this->fpdf->SetDrawColor(0,0,0);
                $this->fpdf->SetLineWidth(.3);
                    $this->fpdf->Cell(205, $alto_cell[0],mb_convert_encoding("Tipo de Contratación: $nombre_contratacion[$PersonalArray] - Turno: $nombre_turno[$PersonalArray]","ISO-8859-1","UTF-8"),0,1,'C');       
                    $this->fpdf->Cell(205, $alto_cell[2],"$nombre_personal_ - $nombre_annlectivo",1,1,'C');   
                ///
                    $this->fpdf->SetFont('','B',9);
                        for($i=0;$i<count($header);$i++)
                        {
                            $this->fpdf->Cell($w[$i],7,mb_convert_encoding(($header[$i]),'ISO-8859-1','UTF-8'),1,0,'C',1);
                        }
                    $this->fpdf->Ln();
                    //Restauración de colores y fuentes
                    $this->fpdf->SetLineWidth(.3);
                    $this->fpdf->SetFillColor(255,255,255);$this->fpdf->SetTextColor(0);
                    $this->fpdf->SetFont('','',9);
                    //Datos
                    $fill=false;
                    ///
                // BUSCAR SEGUN EL CONDIGO CONTRATACION EN LAS DIFERENTESLICENCIAS.
                for ($LineaLicencia=0; $LineaLicencia < count($codigo_licencia_o_permiso) ; $LineaLicencia++) { 
                    // Cabecera - DOCENTE TIPO DE CONTRATACIÓN (personal_licencias permisos).
                    $PersonalLicenciasPermisos = DB::table('personal_licencias_permisos as lp')
                    ->join('personal as p','p.id_personal','=','lp.codigo_personal')
                    ->join('turno as tur','tur.codigo','=','lp.codigo_turno')
                    ->join('tipo_contratacion as tc','tc.codigo','=','lp.codigo_contratacion')
                    ->join('tipo_licencia_o_permiso as tlp','tlp.codigo','=','lp.codigo_licencia_permiso')
                    ->select('p.id_personal', 'p.firma', 'tur.nombre as nombre_turno', 'tc.nombre as nombre_contratacion','lp.codigo_contratacion','lp.codigo_turno',
                                'lp.fecha','lp.dia','lp.hora','lp.minutos', 'tlp.nombre as nombre_licencia_permiso', 
                            DB::raw("TRIM(CONCAT(BTRIM(p.nombres), CAST(' ' AS VARCHAR), BTRIM(p.apellidos))) as full_name"),
                            DB::raw("TO_CHAR(lp.fecha,'YYYY') as DBannlectivo"),
                            )
                    ->where([
                        ['lp.codigo_personal', '=', $codigo_personal],
                        ['lp.codigo_contratacion', '=', $codigo_contratacion[$PersonalArray]],
                        ['lp.codigo_turno', '=', $codigo_turno[$PersonalArray]],
                        ['lp.codigo_licencia_permiso', '=', $codigo_licencia_o_permiso[$LineaLicencia]],
                        ])
                    ->where(DB::raw("TO_CHAR(lp.fecha,'YYYY')"), $nombre_annlectivo)
                    ->orderBy('lp.fecha','asc')
                    ->get();
            		// declarar matrices.
				        $tramite_dia = array(); $tramite_hora = array(); $tramite_minutos = array();   
                    //
                        $valor_y = $this->fpdf->GetY();
                        $pagina_alto = $this->fpdf->GetPageHeight();
                    // recorrer la matriz con los datos del docente que ha consumido por cada una de las licencias. o permisos.
                        foreach($PersonalLicenciasPermisos as $response_plp){  //Llenar el arreglo con datos
                            $num++;
                            $dia = $response_plp->dia;
                            $hora = $response_plp->hora;
                            $minutos = $response_plp->minutos;
                            $nombre_licencia_permiso = mb_convert_encoding($response_plp->nombre_licencia_permiso,"ISO-8859-1","UTF-8"); 
                            $nombre_turno_ = $nombre_turno[$PersonalArray]; 
                            $fecha = cambiaf_a_normal($response_plp->fecha);
                            // valores en pantalla.
                            $this->fpdf->Cell($w[0],5.8,$num,1,0,'L',$fill);  // NUM
                            $this->fpdf->Cell($w[1],5.8,$nombre_licencia_permiso,1,0,'L',$fill);  // tipo de licencia o permiso.
                            $this->fpdf->Cell($w[2],5.8,$nombre_turno_,1,0,'C',$fill);  // nombre turno
                            $this->fpdf->Cell($w[3],5.8,$fecha,1,0,'L',$fill);  // fecha
                            $this->fpdf->Cell($w[4],5.8,$dia,1,0,'C',$fill);  // dia
                            $this->fpdf->Cell($w[5],5.8,$hora,1,0,'C',$fill);  // hora
                            $this->fpdf->Cell($w[6],5.8,$minutos,1,0,'C',$fill);  // minutos
                            $this->fpdf->Ln();
                            //
                            $valor_y = $this->fpdf->GetY();
                            // Calculos de dia, horas, minutos.
                            $total_minutos = ($dia*$calculo_horas*60) + ($hora*60) + ($minutos);
                            //
                            $tramite_dia[] = segundosToCadenaD($total_minutos,$calculo_horas);
                            $tramite_hora[] = segundosToCadenaH($total_minutos, $calculo_horas);
                            $tramite_minutos[] = segundosToCadenaM($total_minutos, $calculo_horas);
                            // 
                            // Salto de página.
								//SaltoPagina($valor_y);
                        }   // fin del foreach de la busqueda de registros en la tabla Personal Lciencias Permisos.
                        ///////
                            $sub_sin_dia = array_sum($tramite_dia);
                            $sub_sin_hora = array_sum($tramite_hora);
                            $sub_sin_minutos = array_sum($tramite_minutos);
                                        
                            $minutos_x_dias = $minutos_licencia_o_permiso[$LineaLicencia];
                            $minutos_subtotal = ($sub_sin_dia*$calculo_horas*60) + ($sub_sin_hora*60) + ($sub_sin_minutos);
                            $minutos = $minutos_x_dias - $minutos_subtotal;
                            $utilizado = mb_convert_encoding(segundosToCadena($minutos_subtotal, $calculo_horas,$formato=2),'ISO-8859-1','UTF-8');
                            $saldo_disponible = mb_convert_encoding(segundosToCadena($minutos, $calculo_horas, $formato=2),'ISO-8859-1','UTF-8');
                            $DiasLicencia = mb_convert_encoding(segundosToCadena($minutos_x_dias, $calculo_horas, $formato=2),'ISO-8859-1','UTF-8');
                            // validar para que los dstos se impriman en pantalla.
                            if($sub_sin_dia > 0 || $sub_sin_hora > 0 || $sub_sin_minutos > 0 ){
                                $this->fpdf->SetFillColor(230,227,227);
                                $this->fpdf->SetFont('Arial','B',8);														
                                $this->fpdf->Cell($w[0],5.8,'',1,0,'L',$fill2);  // numero
                                $this->fpdf->Cell($w[1],5.8,'Licencia: ' . $DiasLicencia,1,0,'L',$fill2);  // licencia
                                $this->fpdf->SetFont('Arial','B',7);					
                                $this->fpdf->Cell($w[2] + $w[3],5.8,'Disponible: ' . $saldo_disponible,1,0,'L',$fill2);  // turno y fecha
                                $this->fpdf->Cell($w2[1],5.8,'Utilizado: ' . $utilizado,1,1,'C',$fill2);  // dia
                                $this->fpdf->SetFont('Arial','',9);
                                $this->fpdf->SetFillColor(255,255,255);
                            }
                        ////
                        // regresar el valor de num a 0.
							$valor_y = $this->fpdf->GetY();
							$num = 0;
                        // Eliminar los elmentos de la array que acumula los dia, minutos y horas.
						    unset($tramite_dia, $tramite_hora, $tramite_minutos);
                }   // for que que hace la busqueda de los registros de Tipo Licencia o Permiso.
                // Salto de Pa´gina.
                $AltoActual = $valor_y + $margen_inferior + $margen_superior;
                if($AltoActual > $this->fpdf->GetPageHeight()){
                    $this->fpdf->AddPage();
                    $this->fpdf->SetXY(10,24);
                    $valor_y = $this->fpdf->GetY();
                    // colores del fondo, texto, línea.
                    $this->fpdf->SetFillColor(230,227,227);
                }
            }   // for que recorre la tabla Personal Salario.
    // Cierre y exit.
        $this->fpdf->Output();
            exit;
    }
}
