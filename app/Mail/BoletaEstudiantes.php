<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Importante para la cola
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// Necesitarás importar los Facades y Modelos que uses para generar tu PDF
// EJEMPLO:
// use PDF; // (Si usas barryvdh/laravel-dompdf)
// use App\Models\Estudiante;
// use Illuminate\Support\Facades\DB;


// 1. Asegúrate de implementar ShouldQueue
class BoletaEstudiantes extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
 
    // 2. Propiedades públicas para guardar los datos
    public $datosGenerales;
    public $datosPdfString;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    // 3. El constructor ahora acepta los dos parámetros que enviamos
    public function __construct($datosPdfString, $datosGenerales)
    {
        $this->datosPdfString = $datosPdfString;
        $this->datosGenerales = $datosGenerales;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            // 4. Generamos el PDF llamando a una función privada
            $pdfOutput = $this->generarPdf();
            
            // 5. Construimos el email
            return $this->view('mails.BoletaEstudiantes') // Carga tu vista de email
                        ->with([
                            'datosInstitucion' => $this->datosGenerales // Pasa los datos de la institución a la vista
                        ])
                        ->subject('Boleta de Calificaciones') // Asunto del correo
                        ->attachData($pdfOutput, 'Boleta_Calificaciones.pdf', [
                            'mime' => 'application/pdf',
                        ]); // Adjunta el PDF generado

        } catch (\Exception $e) {
            // Si algo falla, lo registra en el log en lugar de romper la cola
            Log::error("Error al generar PDF o construir email: " . $e->getMessage());
            // Puedes decidir si fallar el trabajo o no
            $this->fail($e);
        }
    }

    /**
     * Función privada para generar el PDF.
     * ¡AQUÍ DEBES PONER TU LÓGICA DE PDF CONTROLLER!
     */
    private function generarPdf()
    {
        // IMPORTANTE: Un Mailable en cola no puede llamar a una URL (como /pdf/{id}).
        // Debes replicar la LÓGICA de tu 'PdfController' aquí.

        // ----- INICIO DE EJEMPLO (Debes borrar esto y poner tu lógica) -----
        
            // 1. (Ejemplo) Parsear el string
            // $partes = explode('-', $this->datosPdfString);
            // $nie = $partes[0];
            // $codigo_alumno = $partes[1];
            // ...etc.

            // 2. (Ejemplo) Hacer las consultas a la BD
            // $estudiante = \App\Models\Estudiante::where('codigo_nie', $nie)->first();
            // $notas = \Illuminate\Support\Facades\DB::table('nota')->...
            // $datosParaPdf = compact('estudiante', 'notas', 'partes');

            // 3. (Ejemplo) Cargar la vista del PDF con los datos
            // $pdf = \PDF::loadView('pdf.tu_vista_de_boleta', $datosParaPdf);

            // 4. (Ejemplo) Devolver el PDF como un string
            // return $pdf->output();

        // ----- FIN DE EJEMPLO -----

        // --- INICIO DE CÓDIGO TEMPORAL (Borra esto) ---
        // Pongo esto solo para que el Mailable funcione mientras pegas tu lógica.
        // Asegúrate de tener 'barryvdh/laravel-dompdf' instalado.
        if (class_exists('PDF')) {
            $pdf = \PDF::loadHTML('<h1>PDF de Prueba</h1><p>Por favor, reemplace la lógica en <code>app/Mail/BoletaEstudiantes.php</code> en la función <code>generarPdf()</code>.</p>');
            return $pdf->output();
        }
        
        // Fallback si PDF no está instalado
        return 'Este es un PDF de prueba.';
        // --- FIN DE CÓDIGO TEMPORAL ---
    }
}