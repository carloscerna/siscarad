<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class FichaEstudianteController extends Controller
{
    /*
     * Muestra la nómina de estudiantes asociando la matrícula activa 
     * con las tablas exactas del sistema PostgreSQL.
     */
    public function index(Request $request)
    {
       $buscar = $request->get('buscar');
        
        // 1. Obtener el código personal del docente autenticado
        $codigoDocente = Auth::user()->codigo_personal; 
        
        // 2. Obtener el año lectivo en formato de 2 dígitos (ej. '26' para 2026)
        // Nota: Si en tu base de datos el año se guarda como '2026', cambia 'y' por 'Y'.
        $annLectivoActual = date('y');

        // 3. Construcción de la consulta base
      $query = DB::table('alumno as al')
            ->join('alumno_matricula as mat', 'mat.codigo_alumno', '=', 'al.id_alumno')
            ->join('grado_ano as gr', 'gr.codigo', '=', 'mat.codigo_grado')
            ->join('seccion as sec', 'sec.codigo', '=', 'mat.codigo_seccion')
            ->leftJoin('turno as tur', 'tur.codigo', '=', 'mat.codigo_turno')
            ->leftJoin('bachillerato_ciclo as bach', 'bach.codigo', '=', 'mat.codigo_bach_o_ciclo')
            
            // JOIN corregido con la tabla encargado_grado
            ->join('encargado_grado as enc_gr', function($join) use ($codigoDocente, $annLectivoActual) {
                $join->on('enc_gr.codigo_grado', '=', 'mat.codigo_grado')
                     ->on('enc_gr.codigo_seccion', '=', 'mat.codigo_seccion')
                     ->on('enc_gr.codigo_turno', '=', 'mat.codigo_turno')
                     ->where('enc_gr.codigo_docente', '=', $codigoDocente)
                     ->where('enc_gr.encargado', '=', true)
                     ->where('enc_gr.codigo_ann_lectivo', '=', $annLectivoActual);
            })
            
            ->leftJoin('alumno_encargado as enc', function($join) {
                $join->on('enc.codigo_alumno', '=', 'al.id_alumno')
                     ->where('enc.encargado', '=', true);
            })
            
            ->where('mat.codigo_ann_lectivo', '=', $annLectivoActual)
            ->where('mat.retirado', '=', false);

        // 4. Aplicar el buscador si existe un parámetro de búsqueda
        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('al.nombre_completo', 'ILIKE', "%{$buscar}%")
                  ->orWhere('al.codigo_nie', 'ILIKE', "%{$buscar}%")
                  ->orWhere('al.apellido_paterno', 'ILIKE', "%{$buscar}%")
                  ->orWhere('al.apellido_materno', 'ILIKE', "%{$buscar}%");
            });
        }

        // 5. Selección y ordenamiento
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
            ->orderBy(DB::raw("translate(lower(al.apellido_paterno), 'áéíóúü', 'aeiouu')"), 'asc')
            ->orderBy(DB::raw("translate(lower(al.apellido_materno), 'áéíóúü', 'aeiouu')"), 'asc')
            ->orderBy(DB::raw("translate(lower(al.nombre_completo), 'áéíóúü', 'aeiouu')"), 'asc')
            ->get();

        return view('estudiantes.index_ficha', compact('alumnos'));
    }

    /**
     * Carga el formulario con los datos del estudiante y los catálogos del Literal B.
     */
    public function edit($id)
    {
        // 1. Datos del estudiante
        $alumno = DB::table('alumno')->where('id_alumno', $id)->first();

        if (!$alumno) {
            return redirect()->route('ficha.index')->with('error', 'Estudiante no encontrado.');
        }

        // 2. Carga de Catálogos para el Literal B
        $nacionalidades = DB::table('catalogo_nacionalidad')->orderBy('descripcion')->get();
        $etnias = DB::table('catalogo_etnia')->orderBy('codigo')->get();
        $discapacidades = DB::table('catalogo_tipo_de_discapacidad')->orderBy('codigo')->get();
        $diagnosticos = DB::table('catalogo_diagnostico')->orderBy('codigo')->get();
        $apoyosEducativos = DB::table('catalogo_servicios_de_apoyo_educativo')->orderBy('codigo')->get();
        $serviciosRecibe = DB::table('catalogo_alumno_recibe')->orderBy('codigo')->get();
        $actividadesEconomicas = DB::table('catalogo_actividad_economica')->orderBy('codigo')->get();
        $estadosCiviles = DB::table('catalogo_estado_civil')->orderBy('codigo')->get();
        $estadosFamiliares = DB::table('catalogo_estado_familiar')->orderBy('codigo')->get();

        return view('estudiantes.edit_literal_b', compact(
            'alumno',
            'nacionalidades',
            'etnias',
            'discapacidades',
            'diagnosticos',
            'apoyosEducativos',
            'serviciosRecibe',
            'actividadesEconomicas',
            'estadosCiviles',
            'estadosFamiliares'
        ));
    }

    /**
     * Guarda/Actualiza la información del Literal B vía AJAX.
     */
    public function updateLiteralB(Request $request, $id)
    {
        try {
            // Arrays o concatenaciones para campos de selección múltiple si se guardan como cadenas
            $actividadEcon = is_array($request->codigo_actividad_economica) 
                ? implode(',', $request->codigo_actividad_economica) 
                : $request->codigo_actividad_economica;

            $datosLiteralB = [
                'codigo_nie'                  => $request->input('codigo_nie'),
                'dui'                         => $request->input('dui'),
                'pasaporte'                   => $request->input('pasaporte'),
                'nombre_completo'             => $request->input('nombre_completo'),
                'apellido_paterno'            => $request->input('apellido_paterno'),
                'apellido_materno'            => $request->input('apellido_materno'),
                'fecha_nacimiento'            => $request->input('fecha_nacimiento'),
                'codigo_nacionalidad'         => $request->input('codigo_nacionalidad'),
                'retornado'                   => $request->input('retornado'),
                'posee_pn'                    => $request->input('posee_pn'),
                'presenta_pn'                 => $request->input('presenta_pn'),
                'codigo_genero'               => $request->input('codigo_genero'),
                'codigo_etnia'                => $request->input('codigo_etnia'),
                'codigo_discapacidad'         => $request->input('codigo_discapacidad'),
                'codigo_diagnostico'          => $request->input('codigo_diagnostico'),
                'codigo_apoyo_educativo'      => $request->input('codigo_apoyo_educativo'),
                'direccion_email'             => $request->input('direccion_email'),
                'telefono_celular'            => $request->input('telefono_celular'),
                'whatsapp'                    => $request->input('whatsapp'),
                'codigo_actividad_economica'  => $actividadEcon,
                'codigo_estado_civil'         => $request->input('codigo_estado_civil'),
                'codigo_estado_familiar'      => $request->input('codigo_estado_familiar'),
                'embarazada'                  => $request->input('embarazada'),
                'tiene_hijos'                 => $request->has('tiene_hijos') ? true : false,
                'cantidad_hijos'              => $request->input('cantidad_hijos', 0),
                'updated_at'                  => now(),
            ];

            DB::table('alumno')->where('id_alumno', $id)->update($datosLiteralB);

            return response()->json([
                'status'  => 'success',
                'message' => 'Literal B de la Ficha del Estudiante actualizado exitosamente.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }
}