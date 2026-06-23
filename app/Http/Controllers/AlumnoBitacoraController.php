<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AlumnoBitacoraController extends Controller
{
    // Vista Principal: Buscador por Carga Académica

public function index_docente()
{
   // 1. Capturamos el código del docente de la sesión activa
    $codigo_docente = Auth::user()->codigo_personal;

    // 2. Realizamos la consulta cruzando la tabla relacional con todos sus catálogos (incluyendo sección)
    $cargas = DB::table('public.encargado_grado as eg')
        ->join('public.bachillerato_ciclo as b', DB::raw('TRIM(eg.codigo_bachillerato)'), '=', DB::raw('TRIM(b.codigo)'))
        ->join('public.grado_ano as g', DB::raw('TRIM(eg.codigo_grado)'), '=', DB::raw('TRIM(g.codigo)'))
        ->join('public.seccion as s', DB::raw('TRIM(eg.codigo_seccion)'), '=', DB::raw('TRIM(s.codigo)')) // <-- Unión de Sección incorporada
        ->join('public.turno as t', DB::raw('TRIM(eg.codigo_turno)'), '=', DB::raw('TRIM(t.codigo)'))
        ->where('eg.codigo_docente', $codigo_docente)
        ->where('eg.codigo_ann_lectivo', '26') // Filtro estricto año 2026
        ->select(
            'eg.id_encargado_grado as id_carga_docente', 
            'b.nombre as nombre_bachillerato',
            'g.nombre as nombre_grado',
            's.nombre as nombre_seccion', // Extraemos el nombre real de la sección ("A", "B", etc.)
            't.nombre as nombre_turno',
            'eg.codigo_ann_lectivo'
        )
        ->groupBy([
            'eg.id_encargado_grado',
            'b.nombre',
            'g.nombre',
            's.nombre',
            't.nombre',
            'eg.codigo_ann_lectivo'
        ])
        ->get();

    // 3. Formateo y limpieza cosmética para asegurar la presentación visual idéntica al Tablero
    $cargas = $cargas->map(function($carga) {
        $carga->nombre_bachillerato = trim(mb_convert_case($carga->nombre_bachillerato, MB_CASE_UPPER, "UTF-8"));
        $carga->nombre_grado = trim($carga->nombre_grado);
        $carga->nombre_seccion = trim(mb_convert_case($carga->nombre_seccion, MB_CASE_UPPER, "UTF-8"));
        $carga->nombre_turno = trim(mb_convert_case($carga->nombre_turno, MB_CASE_UPPER, "UTF-8"));
        return $carga;
    });

    // 4. Retornamos la vista estructurada
    return view('bitacora.index_docente', compact('cargas'));
}

/**
 * Función auxiliar interna para asegurar que las tildes 
 * no se rompan al convertir a mayúsculas sostenidas en español.
 */
private function mb_uppercase($string) {
    return mb_convert_case($string, MB_CASE_UPPER, "UTF-8");
}

public function getAlumnosPorCarga($id_encargado_grado)
{
    // 1. Obtenemos los parámetros de sección y año de la carga seleccionada
    $cargaContexto = DB::table('public.encargado_grado')
        ->where('id_encargado_grado', $id_encargado_grado)
        ->first();

    if (!$cargaContexto) {
        return response()->json([
            'stat' => 'vacio',
            'alumnos' => [],
            'totales' => ['m' => 0, 'f' => 0, 'r' => 0, 't' => 0]
        ]);
    }

    // 2. Traemos ÚNICAMENTE los estudiantes matriculados en el año actual '26' (2026)
    $alumnos = DB::table('public.alumno_matricula as m')
        ->join('public.alumno as a', 'm.codigo_alumno', '=', 'a.id_alumno')
        ->where('m.codigo_bach_o_ciclo', trim($cargaContexto->codigo_bachillerato))
        ->where('m.codigo_grado', trim($cargaContexto->codigo_grado))
        ->where('m.codigo_seccion', trim($cargaContexto->codigo_seccion))
        ->where('m.codigo_turno', trim($cargaContexto->codigo_turno))
        ->where('m.codigo_ann_lectivo', '26') // <-- FILTRO DE ORO: Evita alumnos de años pasados
        ->select(
            'a.id_alumno',
            'a.codigo_nie',
            'a.codigo_genero',
            'm.retirado',
            DB::raw("TRIM(CONCAT(a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombre_completo)) as nombre_completo_formateado")
        )
        ->orderBy('a.apellido_paterno')
        ->get();

    // 3. Inicializamos contadores estadísticos institucionales
    $masculinos = 0;
    $femeninos   = 0;
    $retirados   = 0;

    foreach ($alumnos as $alumno) {
        $genero = trim($alumno->codigo_genero);
        
        // Contadores por género (Asumiendo '01'/M para Masculino y '02'/F para Femenino según tu base)
        if ($genero === '01' || $genero === 'M') {
            $masculinos++;
        } elseif ($genero === '02' || $genero === 'F') {
            $femeninos++;
        }

        // Contador de estudiantes retirados en el periodo
        if ($alumno->retirado === true || $alumno->retirado == 1 || $alumno->retirado == 't') {
            $retirados++;
        }
    }

    // 4. Retornamos la respuesta unificada con la nómina limpia y sus estadísticas
    return response()->json([
        'alumnos' => $alumnos,
        'totales' => [
            'm' => $masculinos,
            'f' => $femeninos,
            'r' => $retirados,
            't' => $alumnos->count()
        ]
    ]);
}
    // Abrir la bitácora específica del alumno seleccionado
    public function create($id_alumno, $id_carga_docente)
{
    // Buscamos al estudiante construyendo la propiedad requerida por la vista
    $alumno = DB::table('public.alumno')
        ->where('id_alumno', $id_alumno)
        ->select(
            'id_alumno',
            'codigo_nie',
            DB::raw("CONCAT_WS(' ', TRIM(apellido_paterno), TRIM(apellido_materno), TRIM(nombre_completo)) as nombre_completo_formateado")
        )
        ->first();

    // Si por algún motivo el ID no existe en la BD, evitamos que rompa mandando un 404
    if (!$alumno) {
        abort(404, 'El estudiante solicitado no existe en el sistema.');
    }

    // Obtener el historial cronológico de bitácoras de este alumno
    $historial = DB::table('public.alumno_bitacora')
        ->where('codigo_alumno', strval($id_alumno)) 
        ->orderBy('fecha', 'desc')
        ->orderBy('hora', 'desc')
        ->get();

    // Enviamos las variables limpias a la vista de Blade
    return view('bitacora.create', compact('alumno', 'id_carga_docente', 'historial'));
}

public function store(Request $request)
{
    try {
        // Id del docente logueado
        $idPersonalLogueado = Auth::user()->id_personal ?? 53; 

        // Creamos la descripción uniendo el asunto
        $textoCompleto = "ASUNTO: " . trim($request->asunto) . "\n\n" . trim($request->descripcion);

        // INSERT BLINDADO: Convertimos explícitamente los campos para evitar incompatibilidades en Postgres
        DB::table('public.alumno_bitacora')->insert([
            'codigo_alumno'          => strval($request->id_alumno), // Forzado a String por si es varchar/character
            'id_carga_docente'       => (int) $request->id_carga_docente, // Forzado a Entero
            'fecha'                  => $request->fecha, // YYYY-MM-DD
            'hora'                   => $request->hora,  // HH:MM
            'descripcion'            => $textoCompleto,
            'codigo_tipo_incidencia' => strval($request->codigo_tipo_incidencia ?? '01'),
            'id_personal_registro'   => (int) $idPersonalLogueado
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'La anotación se ha registrado con éxito en la bitácora.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ], 500);
    }
}

 public function update(Request $request)
{
    try {
        $request->validate([
            'id_bitacora'            => 'required',
            'fecha'                  => 'required|date',
            'hora'                   => 'required',
            'descripcion'            => 'required|string',
            'codigo_tipo_incidencia' => 'nullable|string|max:2'
        ]);

        // Ejecutamos la actualización directamente en la tabla usando Query Builder
        // IMPORTANTE: Cambia 'id_alumno_bitacora' por el nombre real de tu columna ID de bitácora si es diferente
        DB::table('public.alumno_bitacora')
            ->where('id_bitacora', $request->id_bitacora) 
            ->update([
                'fecha'                  => $request->fecha,
                'hora'                   => $request->hora,
                'descripcion'            => trim($request->descripcion),
                'codigo_tipo_incidencia' => strval($request->codigo_tipo_incidencia ?? '01')
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'El registro de la bitácora ha sido modificado correctamente.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Error al modificar: ' . $e->getMessage()
        ], 500);
    }
}
   
}