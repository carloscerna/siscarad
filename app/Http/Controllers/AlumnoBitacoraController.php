<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AlumnoBitacoraController extends Controller
{
    // Vista Principal: Buscador por Carga Académica
public function indexDocente()
{
$idDocente = 53; // ID del docente logueado

    // Traemos la carga cruzando con los catálogos para obtener los nombres reales
    $cargas = DB::table('public.carga_docente as c')
        ->leftJoin('public.bachillerato_ciclo as b', DB::raw('TRIM(c.codigo_bachillerato)'), '=', DB::raw('TRIM(b.codigo)'))
        ->leftJoin('public.grado_ano as g', DB::raw('TRIM(c.codigo_grado)'), '=', DB::raw('TRIM(g.codigo)'))
        ->leftJoin('public.seccion as s', DB::raw('TRIM(c.codigo_seccion)'), '=', DB::raw('TRIM(s.codigo)'))
        ->leftJoin('public.turno as t', DB::raw('TRIM(c.codigo_turno)'), '=', DB::raw('TRIM(t.codigo)'))
        ->where('c.codigo_docente', $idDocente)
        ->where('c.codigo_ann_lectivo', '26') // Año 2026
        ->select(
            'c.id_carga_docente',
            'c.codigo_ann_lectivo',
            DB::raw('TRIM(b.nombre) as nombre_bachillerato'), // Ej: General / Comercial
            DB::raw('TRIM(g.nombre) as nombre_grado'),        // Ej: Cuarto Grado
            DB::raw('TRIM(s.nombre) as nombre_seccion'),      // Ej: A, B
            DB::raw('TRIM(t.nombre) as nombre_turno')         // Ej: Mañana / Tarde
        )
        ->get();

    return view('bitacora.index_docente', compact('cargas'));
}

public function getAlumnosPorCarga($id_carga_docente)
{
    $carga = DB::table('public.carga_docente')->where('id_carga_docente', $id_carga_docente)->first();

    if (!$carga) {
        return response()->json([]);
    }

    $alumnos = DB::table('public.alumno as al')
        ->join('public.alumno_matricula as mat', 'al.id_alumno', '=', 'mat.codigo_alumno') 
        ->whereRaw('TRIM(mat.codigo_grado) = ?', [trim($carga->codigo_grado)])
        ->whereRaw('TRIM(mat.codigo_seccion) = ?', [trim($carga->codigo_seccion)])
        ->whereRaw('TRIM(mat.codigo_ann_lectivo) = ?', [trim($carga->codigo_ann_lectivo)])
        ->select(
            'al.id_alumno',
            'al.codigo_nie', // <-- IMPORTANTE: Lo agregamos para que lo lea tu JS en la tabla
            DB::raw("CONCAT_WS(' ', TRIM(al.apellido_paterno), TRIM(al.apellido_materno), TRIM(al.nombre_completo)) as nombre_completo_formateado")
        )
        ->orderBy('al.apellido_paterno', 'asc')
        ->orderBy('al.apellido_materno', 'asc')
        ->get();

    return response()->json($alumnos);
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



    
}