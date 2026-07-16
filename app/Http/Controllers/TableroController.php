
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alumno;
use App\Mail\BoletaEstudianteMail;
use Illuminate\Support\Facades\Mail;
// Importa aquí la extensión o clase de FPDF que utilices en tu proyecto
use Codigo\TuClaseFPDF; 

class TableroController extends Controller
{
    public function enviarBoletaIndividual($id)
    {
        // 1. Obtener la información del alumno por su ID primario
        $alumno = Alumno::findOrFail($id);

        // 2. Verificar y actualizar la dirección de correo electrónico si está vacía
        if (empty($alumno->direccion_email)) {
            $alumno->direccion_email = $alumno->codigo_nie . '@clases.edu.sv';
            $alumno->save(); // Actualización directa en la base de datos
        }

        try {
            // 3. Instanciar tu generador de FPDF (Ejemplo de inicialización estándar)
            $fpdf = new TuClaseFPDF();
            
            /* 
               AQUÍ VA TU CÓDIGO ACTUAL DE FPDF 
               (Ejemplo: $fpdf->AddPage(); $fpdf->Cell(...); etc.)
            */
            
            // IMPORTANTE: El método Output con el parámetro 'S' retorna el PDF como un string binario
            $pdfBinario = $fpdf->Output('S', 'boleta.pdf');
            
            $nombreArchivo = 'Boleta_' . $alumno->codigo_nie . '.pdf';

            // 4. Ejecutar el envío utilizando la fachada Mail
            Mail::to($alumno->direccion_email)->send(new BoletaEstudianteMail($alumno, $pdfBinario, $nombreArchivo));

            return redirect()->back()->with('success', 'Boleta enviada con éxito al correo: ' . $alumno->direccion_email);

        } catch (\Exception $e) {
            // Retorna al tablero con el mensaje descriptivo del fallo técnico
            return redirect()->back()->with('error', 'Error al procesar el envío: ' . $e->getMessage());
        }
    }
}