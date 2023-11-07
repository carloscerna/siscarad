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
        //$this->Footer();
        
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
            $alto_cell = array('5','40'); $ancho_cell = array('60','6','24','30','12');        
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
         } // FIN DEL FOREACH para los datos de la insitucion.
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
