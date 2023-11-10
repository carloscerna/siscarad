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
        $this->fpdf->SetAutoPageBreak(true,5);
        $this->fpdf->SetX(30);
        //(numero, tipo de permiso, turno, fecha, dia, hora, minutos)
            $w=array(5,90,30,20,15,15,15); //determina el ancho de las columnas
            $w2=array(40,45); //determina el ancho de las columnas
        // colores del fondo, texto, línea.
            $this->fpdf->SetFillColor(230,227,227);
            $fill=false; $num=1;
            $fill2=true;
        
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
             // LOGO DE LA INSTITUCIÓN
                $this->fpdf->image(URL::to($logo_uno),10,5,15,20);
                $this->fpdf->SetX(5);
                $this->fpdf->SetFont('Arial','I',14);
                    $this->fpdf->Cell(205, $alto_cell[0],"CONTROL DE LICENCIAS Y PERMISOS DEL PERSONAL DOCENTE",0,1,'C');       
                $this->fpdf->SetFont('Arial','',10);
                $this->fpdf->Cell(205, $alto_cell[0],$codigo_institucion . " - " .$nombre_institucion,0,1,'C');       
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
                $this->fpdf->Cell(205, $alto_cell[0],mb_convert_encoding("Tipo de Contratación: $nombre_contratacion[$PersonalArray] - Turno: $nombre_turno[$PersonalArray]","ISO-8859-1","UTF-8"),0,1,'C');       
                $this->fpdf->Cell(205, $alto_cell[2],"$nombre_personal_ - $nombre_annlectivo",1,1,'C');   
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
                            )
                    ->where([
                        ['lp.codigo_personal', '=', $codigo_personal],
                        ['lp.codigo_contratacion', '=', $codigo_contratacion[$PersonalArray]],
                        ['lp.codigo_turno', '=', $codigo_turno[$PersonalArray]],
                        ])
                    ->orderBy('lp.fecha','asc')
                    ->get();
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
                    }   // fin del for de la busqueda de registros por licencias y permisos.
                }
            }
    // Cierre y exit.
        $this->fpdf->Output();
            exit;
    }
    function Footer(){
        // Establecer formato para la fecha.
          date_default_timezone_set('America/El_Salvador');
          setlocale(LC_TIME, 'spanish');						
          //Posición: a 1,5 cm del final
          $this->fpdf->SetY(-20);
          //Arial italic 8
          $this->fpdf->SetFont('Arial','I',8);
           //Crear una línea de la primera firma.
          $this->fpdf->Line(15,270,90,270);
          //Crear una línea de la segunda firma.
          $this->fpdf->Line(130,270,190,270);
          //Crear ubna línea
          $this->fpdf->Line(10,285,200,285);
          //Número de página
          $fecha = date("l, F jS Y "); $this->fpdf->Cell(0,10,'Page '.$this->fpdf->PageNo().'/{nb}       '.$fecha,0,0,'C');
              //Nombre Subdirector(a)
          $this->fpdf->SetXY(40,270);
          $this->fpdf->Cell(20,6,'Subdirector(a)',0,0,'C');
              //Nombre Director
          $this->fpdf->SetXY(150,270);
          $this->fpdf->Cell(20,6,'Director',0,0,'C');
          }
}