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
            $codigo_annlectivo = $EstudianteMatricula[4];
            $codigo_institucion = $EstudianteMatricula[5];
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
            foreach($EstudianteInformacionInstitucion as $response_i){  //Llenar el arreglo con datos
                $nombre_institucion = utf8_decode(trim($response_i->nombre_institucion));
                $codigo_institucion = utf8_decode(trim($response_i->codigo_institucion));
                $logo_uno = "/img/".utf8_decode(trim($response_i->logo_uno));

                    $this->fpdf->image(URL::to($logo_uno),10,5,15,20);
                    $this->fpdf->Cell(40, 5,"CENTRO ESCOLAR:",1,0,'L');       
                    $this->fpdf->Cell(135, 5,$codigo_institucion . " - " .$nombre_institucion,1,1,'L');       
            } // FIN DEL FOREACH para los datos de la insitucion.
            //
            $EstudianteBoleta = DB::table('alumno as a')
            ->join('alumno_matricula AS am','a.id_alumno','=','am.codigo_alumno')
            ->join('nota AS n','am.id_alumno_matricula','=','n.codigo_matricula')
            ->select('a.id_alumno as codigo_alumno','a.codigo_nie','a.nombre_completo',"a.apellido_paterno",'a.apellido_materno','am.id_alumno_matricula as codigo_matricula','n.id_notas','n.codigo_asignatura',
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
                $codigo_asignatura = utf8_decode(trim($response->codigo_asignatura));

                //Mostrar solamente una vez.

                if($fila == 1){
                    $this->fpdf->Cell(40,5,"Estudiante",1,0,'L');       
                    $this->fpdf->Cell(135,5,$codigo_nie . " - " . $nombre_completo,1,1,'L');       
                    $this->fpdf->Cell(40,5,"Componente",1,1,'L');       
                }else{
                    $this->fpdf->Cell(40,5,$codigo_asignatura,1,1,'L');       
                }
                
                // incremento de variable que controla la fila
                    $fila++;
            } // FIN DEL FOREACH
        
        // FIN DEL FPDF
            $this->fpdf->Output();
                exit;
    }    //
}
