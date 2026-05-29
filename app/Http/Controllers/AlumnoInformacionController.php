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
    // CORRECCIÓN DE AGUJA: Buscamos usando explícitamente la columna 'id_alumno' 
    // para que Eloquent no intente adivinar la llave primaria.
    $alumno = Alumno::where('id_alumno', $id_alumno)->first();

    // Si no lo encuentra por el modelo, usamos Query Builder como plan de respaldo absoluto
    if (!$alumno) {
        $alumnoData = DB::table('alumno')->where('id_alumno', $id_alumno)->first();
        
        if (!$alumnoData) {
            return redirect()->route('estudiante.informacion.index')
                ->with('error', 'El estudiante con ID ' . $id_alumno . ' no existe en la base de datos.');
        }
        
        // Lo convertimos temporalmente en objeto estándar si falla el modelo
        $alumno = $alumnoData; 
    }

// APLICAMOS TRIM AL CAMPO FOTO PARA ELIMINAR ESPACIOS EN BLANCO OCULTOS DE LA BD
    if (isset($alumno->foto)) {
        $alumno->foto = trim($alumno->foto);
    }

    // 2. Traemos de forma independiente los nombres de grado y sección para el encabezado (Año de 2 dígitos)
    $matriculaActual = DB::table('alumno_matricula as mat')
        ->join('grado_ano as gr', 'gr.codigo', '=', 'mat.codigo_grado')
        ->join('seccion as sec', 'sec.codigo', '=', 'mat.codigo_seccion')
        ->where('mat.codigo_alumno', $id_alumno)
        ->where('mat.codigo_ann_lectivo', date('y'))
        ->select('gr.nombre as grado_nombre', 'sec.nombre as seccion_nombre')
        ->first();

    // 3. Inyectamos los datos del encargado de la tabla alumno_encargado
    $encargado = DB::table('alumno_encargado')
        ->where('codigo_alumno', $id_alumno)
        ->where('encargado', true)
        ->first();

    // Asignamos las propiedades dinámicas para que tu vista Blade las pinte idénticas
    $alumno->grado_nombre = $matriculaActual->grado_nombre ?? 'N/A';
    $alumno->seccion_nombre = $matriculaActual->seccion_nombre ?? 'N/A';
    $alumno->nombre_encargado = $encargado->nombre_completo ?? '';
    $alumno->firma_autorizacion = $encargado->firma_autorizacion ?? '';

    return view('layouts.informacion', compact('alumno'));
}

    /**
 * Procesa y guarda los datos de la captura (Fotografía física y Firma en Base64)
 */
public function update(Request $request, $id_alumno)
{
    // 1. CORRECCIÓN PARA PROCESAR EL ARCHIVO DE FOTO REAL DESDE EL INPUT MULTIPART
    if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        
        // Creamos el nombre del archivo respetando tu estructura (ID_alumno + timestamp para romper caché)
        $nombreArchivo = 'foto_' . $id_alumno . '_' . time() . '.' . $file->getClientOriginalExtension();

        /* Mover el archivo directamente a tu puente virtual 'fotos_origen'.
          Al moverlo aquí, Windows físicamente lo guardará de inmediato en:
          C:\wamp64\www\registro_academico\img\fotos\10391\ sin que Laravel duplique espacio.
        */
        $file->move(public_path('fotos_origen'), $nombreArchivo);

        // Actualizamos el nombre del archivo en la tabla 'alumno' en PostgreSQL
        DB::table('alumno')->where('id_alumno', $id_alumno)->update([
            'foto' => $nombreArchivo
        ]);
    }

    // 2. PROCESAR LA FIRMA EN BASE64 (Se mantiene igual porque el canvas sigue mandando texto)
    if ($request->filled('firma_base64')) {
        $existeEncargado = DB::table('alumno_encargado')
            ->where('codigo_alumno', $id_alumno)
            ->where('encargado', true)
            ->exists();

        if ($existeEncargado) {
            DB::table('alumno_encargado')
                ->where('codigo_alumno', $id_alumno)
                ->where('encargado', true)
                ->update([
                    'nombre_completo' => $request->input('nombre_encargado_input'),
                    'firma_autorizacion' => $request->input('firma_base64'),
                    'updated_at' => now()
                ]);
        } else {
            DB::table('alumno_encargado')->insert([
                'codigo_alumno' => $id_alumno,
                'encargado' => true,
                'nombre_completo' => $request->input('nombre_encargado_input') ?? 'Encargado',
                'firma_autorizacion' => $request->input('firma_base64'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    return redirect()->route('estudiante.informacion.index')->with('success', 'Información guardada con éxito.');
}
}