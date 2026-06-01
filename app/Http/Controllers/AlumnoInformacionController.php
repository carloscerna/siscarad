<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alumno;
use App\Models\AlumnoEncargado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlumnoInformacionController extends Controller
{
    /**
     * Muestra la lista de estudiantes filtrada por la sección que lidera el Docente
     */
  public function index(Request $request)
{

// LÍNEA TEMPORAL DE PRUEBA:
    //dd(auth()->user()->toArray());

    $buscar = $request->get('buscar');
    
    // 1. Conseguimos el código del docente logueado
    // NOTA: Si tu columna en la tabla de usuarios se llama diferente (ej: 'codigo' o 'id_docente'), cámbialo aquí.
    // Para pruebas, si es null, puedes asignarle un código fijo temporal, por ejemplo: $codigoDocente = 123;
    // CORRECCIÓN: Tu campo real de la tabla users es 'codigo_personal'
    $codigoDocente = auth()->user()->codigo_personal; 
    $annLectivoActual = date('y');

    // 2. Construimos la consulta base corregida
    $query = DB::table('alumno as al')
        ->join('alumno_matricula as mat', 'mat.codigo_alumno', '=', 'al.id_alumno')
        ->join('grado_ano as gr', 'gr.codigo', '=', 'mat.codigo_grado')
        ->join('seccion as sec', 'sec.codigo', '=', 'mat.codigo_seccion')
        ->leftJoin('turno as tur', 'tur.codigo', '=', 'mat.codigo_turno')
        ->leftJoin('bachillerato_ciclo as bach', 'bach.codigo', '=', 'mat.codigo_bach_o_ciclo')
        // CORRECCIÓN DE ALIAS AQUÍ: Todo usando estricta y únicamente 'enc_gr'
        ->join('encargado_grado as enc_gr', function($join) use ($codigoDocente, $annLectivoActual) {
            $join->on('enc_gr.codigo_grado', '=', 'mat.codigo_grado')
                 ->on('enc_gr.codigo_seccion', '=', 'mat.codigo_seccion')
                 ->on('enc_gr.codigo_turno', '=', 'mat.codigo_turno') // <-- Corregido con el alias enc_gr
                 ->where('enc_gr.codigo_docente', '=', $codigoDocente)
                 ->where('enc_gr.encargado', '=', true)
                 ->where('enc_gr.codigo_ann_lectivo', '=', $annLectivoActual);
        })
        ->leftJoin('alumno_encargado as enc', function($join) {
            $join->on('enc.codigo_alumno', '=', 'al.id_alumno')
                 ->where('enc.encargado', '=', true);
        })
        ->where('mat.codigo_ann_lectivo', $annLectivoActual)
        ->where('mat.retirado', false);

    // 3. Aplicar el buscador
    if ($buscar) {
        $query->where(function($q) use ($buscar) {
            $q->where('al.nombre_completo', 'LIKE', "%{$buscar}%")
              ->orWhere('al.codigo_nie', 'LIKE', "%{$buscar}%")
              ->orWhere('al.apellido_paterno', 'LIKE', "%{$buscar}%")
              ->orWhere('al.apellido_materno', 'LIKE', "%{$buscar}%");
        });
    }

    // 4. Selección y paginación
    $alumnos = $query->select(
            'al.id_alumno',
            'al.codigo_nie',
            'al.apellido_paterno',
            'al.apellido_materno',
            'al.nombre_completo',
            'al.foto',
            'al.codigo_genero',
            'gr.nombre as grado_nombre',
            'sec.nombre as seccion_nombre',
            'tur.nombre as turno_nombre',
            'bach.nombre as bachillerato_nombre',
            'enc.firma_autorizacion'
        )
/* USAMOS DB::raw PARA ELIMINAR LAS TILDES EN EL ORDENAMIENTO DE POSTGRESQL
           Esto traduce las vocales con tilde a vocales normales SOLO para decidir la posición
        */
        ->orderBy(DB::raw("translate(lower(al.apellido_paterno), 'áéíóúü', 'aeiouu')"), 'asc')
        ->orderBy(DB::raw("translate(lower(al.apellido_materno), 'áéíóúü', 'aeiouu')"), 'asc')
        ->orderBy(DB::raw("translate(lower(al.nombre_completo), 'áéíóúü', 'aeiouu')"), 'asc')
        ->get();

    return view('layouts.index_informacion', compact('alumnos', 'buscar'));
}
/**
 * Muestra el formulario con TODA la información completa del estudiante
 */
public function edit($id_alumno)
{
    // 1. Buscamos usando explícitamente la columna 'id_alumno' 
    $alumno = Alumno::where('id_alumno', $id_alumno)->first();

    // Si no lo encuentra por el modelo, usamos Query Builder como plan de respaldo
    if (!$alumno) {
        $alumnoData = DB::table('alumno')->where('id_alumno', $id_alumno)->first();
        
        if (!$alumnoData) {
            return redirect()->route('estudiante.informacion.index')
                ->with('error', 'El estudiante con ID ' . $id_alumno . ' no existe.');
        }
        $alumno = $alumnoData; 
    }

    // Aseguramos la limpieza de espacios en el campo foto y dirección del alumno
    if (isset($alumno->foto)) {
        $alumno->foto = trim($alumno->foto);
    }
    if (isset($alumno->direccion_alumno)) {
        // Mapeamos a la propiedad exacta que busca tu textarea: {{ $alumno->direccion_alumno }}
        $alumno->direccion_alumno = trim($alumno->direccion_alumno);
    }

    // 2. Traemos de forma independiente los nombres de grado y sección
    $matriculaActual = DB::table('alumno_matricula as mat')
        ->join('grado_ano as gr', 'gr.codigo', '=', 'mat.codigo_grado')
        ->join('seccion as sec', 'sec.codigo', '=', 'mat.codigo_seccion')
        ->where('mat.codigo_alumno', $id_alumno)
        ->where('mat.codigo_ann_lectivo', date('y'))
        ->select('gr.nombre as grado_nombre', 'sec.nombre as seccion_nombre')
        ->first();

    $alumno->grado_nombre = $matriculaActual->grado_nombre ?? 'N/A';
    $alumno->seccion_nombre = $matriculaActual->seccion_nombre ?? 'N/A';

    // 3. Extraemos el encargado desde la base de datos
    $encargadoData = DB::table('alumno_encargado')
        ->where('codigo_alumno', $id_alumno)
        ->where('encargado', true)
        ->select('nombres', 'telefono', 'direccion', 'firma_autorizacion')
        ->first();

    // CREAMOS EL OBJETO INTERNO QUE TU BLADE BUSCA: $alumno->encargadoPrincipal
    $alumno->encargadoPrincipal = (object)[
        'nombres'            => isset($encargadoData->nombres) ? trim($encargadoData->nombres) : '',
        'telefono'           => isset($encargadoData->telefono) ? trim($encargadoData->telefono) : '',
        'direccion'          => isset($encargadoData->direccion) ? trim($encargadoData->direccion) : '',
        'firma_autorizacion' => isset($encargadoData->firma_autorizacion) ? trim($encargadoData->firma_autorizacion) : ''
    ];

    return view('layouts.informacion', compact('alumno'));
}
   /**
 * Procesa, comprime la fotografía y guarda la firma del encargado
 */
public function update(Request $request, $id_alumno)
{
    // 1. PROCESAMIENTO Y COMPRESIÓN DE LA FOTO (Bajar de MB a KB con excelente calidad)
    if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        $extension = strtolower($file->getClientOriginalExtension());
        $nombreArchivo = 'foto_' . $id_alumno . '_' . time() . '.jpg'; // Forzamos salida JPG para optimizar
        $rutaDestino = public_path('fotos_origen/' . $nombreArchivo);

        // Creamos la imagen en memoria según el formato de origen
        if ($extension === 'png') {
            $imagenOriginal = imagecreatefrompng($file->getRealPath());
        } elseif ($extension === 'gif') {
            $imagenOriginal = imagecreatefromgif($file->getRealPath());
        } else {
            $imagenOriginal = imagecreatefromjpeg($file->getRealPath());
        }

        if ($imagenOriginal) {
            // Guardamos la imagen aplicando compresión (Calidad 75: balance perfecto peso/nitidez)
            // Esto bajará el peso drásticamente de 2.8 MB a ~100-150 KB
            imagejpeg($imagenOriginal, $rutaDestino, 75);
            imagedestroy($imagenOriginal); // Liberamos memoria de WAMP

            // Actualizamos el nombre en la tabla alumno
            DB::table('alumno')->where('id_alumno', $id_alumno)->update([
                'foto' => $nombreArchivo
            ]);
        }
    }

    // 2. PROCESAMIENTO SEGURO DE LA FIRMA
    if ($request->filled('firma_autorizacion_base64')) {
        $firmaData = $request->input('firma_autorizacion_base64');

        // Validación: Asegurarnos de que realmente viaja un trazo de imagen Base64 válido
        if (strpos($firmaData, 'data:image/png;base64,') !== false) {
            
            // Verificamos si ya existe el registro del encargado para este alumno
            $existeEncargado = DB::table('alumno_encargado')
                ->where('codigo_alumno', $id_alumno)
                ->where('encargado', true)
                ->exists();

            $nombreEncargado = $request->input('nombres_encargado') ?? 'Encargado';

            if ($existeEncargado) {
                DB::table('alumno_encargado')
                    ->where('codigo_alumno', $id_alumno)
                    ->where('encargado', true)
                    ->update([
                        'nombres' => $nombreEncargado,
                        'firma_autorizacion' => $firmaData,
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('alumno_encargado')->insert([
                    'codigo_alumno' => $id_alumno,
                    'encargado' => true,
                    'nombres' => $nombreEncargado,
                    'firma_autorizacion' => $firmaData,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    return redirect()->route('estudiante.informacion.index')
        ->with('success', 'Expediente actualizado: Foto optimizada y firma registrada con éxito.');
}
}