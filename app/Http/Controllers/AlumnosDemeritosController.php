<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AlumnosDemerito;

class AlumnosDemeritosController extends Controller
{
    public function index()
    {
        $codigo_docente = Auth::user()->codigo_personal;

        $secciones = DB::table('public.encargado_grado as eg')
            ->join('public.bachillerato_ciclo as b', DB::raw('TRIM(eg.codigo_bachillerato)'), '=', DB::raw('TRIM(b.codigo)'))
            ->join('public.grado_ano as g', DB::raw('TRIM(eg.codigo_grado)'), '=', DB::raw('TRIM(g.codigo)'))
            ->join('public.seccion as s', DB::raw('TRIM(eg.codigo_seccion)'), '=', DB::raw('TRIM(s.codigo)'))
            ->join('public.turno as t', DB::raw('TRIM(eg.codigo_turno)'), '=', DB::raw('TRIM(t.codigo)'))
            ->where('eg.codigo_docente', $codigo_docente)
            ->where('eg.codigo_ann_lectivo', '26') 
            ->select(
                'eg.id_encargado_grado', 
                'b.nombre as bachi',
                'g.nombre as grado',
                's.nombre as seccion',
                't.nombre as turno'
            )->get();

        return view('conducta.consolidado_mensual', compact('secciones'));
    }

    public function cargarDatosMes($id_encargado_grado, $mes)
    {
        $codigo_ann_lectivo = '26';

        $consolidado = AlumnosDemerito::where('id_encargado_grado', $id_encargado_grado)
            ->where('mes_evaluacion', $mes)
            ->where('codigo_ann_lectivo', $codigo_ann_lectivo)
            ->first();

        if ($consolidado) {
            return response()->json([
                'existe' => true,
                'datos' => $consolidado
            ]);
        }

        // Si es nuevo, calculamos la matrícula inicial de hombres y mujeres
        $aula = DB::table('public.encargado_grado')->where('id_encargado_grado', $id_encargado_grado)->first();
        $hombres = 0;
        $mujeres = 0;

        if ($aula) {
            $alumnosMatriculados = DB::table('public.alumno_matricula as m')
                ->join('public.alumno as a', 'm.codigo_alumno', '=', 'a.id_alumno')
                ->where('m.codigo_bach_o_ciclo', trim($aula->codigo_bachillerato))
                ->where('m.codigo_grado', trim($aula->codigo_grado))
                ->where('m.codigo_seccion', trim($aula->codigo_seccion))
                ->where('m.codigo_turno', trim($aula->codigo_turno))
                ->where('m.codigo_ann_lectivo', $codigo_ann_lectivo)
                ->where('m.retirado', false)
                ->select('a.codigo_genero')
                ->get();

            foreach ($alumnosMatriculados as $al) {
                $gen = trim($al->codigo_genero);
                if ($gen === '01' || $gen === 'M') $hombres++;
                if ($gen === '02' || $gen === 'F') $mujeres++;
            }
        }

        // Estructura limpia alineada con las causales y normativas reales
        return response()->json([
            'existe' => false,
            'datos' => [
                'matricula_hombres' => $hombres,
                'matricula_mujeres' => $mujeres,
                'total_demeritos_hombres' => 0,
                'total_demeritos_mujeres' => 0,
                'dem_causal_a' => 0,
                'dem_causal_b' => 0,
                'dem_causal_c' => 0,
                'dem_causal_d' => 0,
                'redenciones_hombres' => 0,
                'redenciones_mujeres' => 0,
                'redencion_opcion_a' => 0,
                'redencion_opcion_b' => 0,
                'redencion_opcion_c' => 0,
                'reconocimientos_hombres' => 0,
                'reconocimientos_mujeres' => 0,
                'reconocimiento_diploma' => 0,
                'reconocimiento_mural' => 0
            ]
        ]);
    }

    public function guardarMasivo(Request $request)
    {
        try {
            $consolidado = AlumnosDemerito::updateOrCreate(
                [
                    'id_encargado_grado' => $request->id_encargado_grado,
                    'mes_evaluacion' => $request->mes_evaluacion,
                    'codigo_ann_lectivo' => '26',
                ],
                $request->all()
            );

            return response()->json(['success' => true, 'message' => 'Consolidado mensual guardado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    public function eliminarMes($id_encargado_grado, $mes)
    {
        try {
            AlumnosDemerito::where('id_encargado_grado', $id_encargado_grado)
                ->where('mes_evaluacion', $mes)
                ->where('codigo_ann_lectivo', '26')
                ->delete();

            return response()->json(['success' => true, 'message' => 'Registro mensual eliminado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar.'], 500);
        }
    }

public function verificarMesesSeccion($id_encargado_grado)
{
    // Obtenemos todos los registros guardados de esta sección en el año '26'
    $registrados = DB::table('public.alumnos_demeritos')
        ->where('id_encargado_grado', $id_encargado_grado)
        ->where('codigo_ann_lectivo', '26')
        ->pluck('mes_evaluacion') // Nos devuelve un arreglo con los números de mes guardados (ej: [1, 2])
        ->toArray();

    return response()->json([
        'meses_registrados' => $registrados
    ]);
}


}