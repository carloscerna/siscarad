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
        // Supongamos que recuperamos el id_personal del docente logueado
        $idDocente = Auth::user()->id_personal ?? 53; // Ejemplo con tu dato del docente 53

        // Obtener la carga académica del docente para el año lectivo actual (ej: '26')
        $cargas = DB::table('carga_docente')
            ->where('codigo_docente', $idDocente)
            ->where('codigo_ann_lectivo', '26') 
            ->get();

        return view('bitacora.index_docente', compact('cargas'));
    }

    // Cargar alumnos vía AJAX según la sección/carga elegida
public function getAlumnosPorCarga($id_carga_docente)
{
    // 1. Obtener la configuración de la carga académica del docente
    $carga = DB::table('carga_docente')->where('id_carga_docente', $id_carga_docente)->first();

    if (!$carga) {
        return response()->json([]);
    }

    // LOG DE CONTROL: Si tu búsqueda falla, puedes descomentar la línea de abajo en desarrollo para ver qué llega
    // \Log::info('Buscando alumnos para Grado: '.$carga->codigo_grado.' Seccion: '.$carga->codigo_seccion);

    // 2. Buscar en tu tabla real de estudiantes. 
    // Reemplaza 'alumnos' por el nombre EXACTO de tu tabla de estudiantes (donde guardas el nombre y grado)
    $alumnos = DB::table('alumno') 
        ->where('codigo_grado', trim($carga->codigo_grado)) // trim quita espacios fantasmas del tipo character
        ->where('codigo_seccion', trim($carga->codigo_seccion))
        ->select('codigo as codigo_alumno', 'nombre_completo') // Asegura que devuelva 'codigo_alumno' para el JS
        ->orderBy('nombre_completo', 'asc')
        ->get();

    return response()->json($alumnos);
}

    // Abrir la bitácora específica del alumno seleccionado
    public function create($codigo_alumno, $id_carga_docente)
    {
        // Obtener datos del alumno
        $alumno = DB::table('alumno_matricula') // Ajustar a tu tabla
            ->where('codigo_alumno', $codigo_alumno)
            ->first();

        // Obtener el historial de la bitácora de este alumno
        $historial = DB::table('alumno_bitacora as b')
            ->join('carga_docente as c', 'b.id_carga_docente', '=', 'c.id_carga_docente')
            ->where('b.codigo_alumno', $codigo_alumno)
            ->orderBy('b.fecha', 'desc')
            ->orderBy('b.hora', 'desc')
            ->select('b.*')
            ->get();

        return view('bitacora.create', compact('alumno', 'id_carga_docente', 'historial'));
    }
}