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
 * Muestra el formulario para editar la Ficha del Estudiante (Literales B y C).
 */
public function edit($id)
{
    $alumno = DB::table('alumno as al')
        ->leftJoin('alumno_matricula as mat', function($join) {
            $join->on('mat.codigo_alumno', '=', 'al.id_alumno')
                 ->where('mat.codigo_ann_lectivo', '=', date('y'));
        })
        ->where('al.id_alumno', $id)
        ->select('al.*', 'mat.telefono_celular as telefono_matricula')
        ->first();

    if (!$alumno) {
        return redirect()->route('ficha.index')->with('error', 'El estudiante no existe.');
    }

    // Catálogos del Literal B
    $nacionalidades       = DB::table('catalogo_nacionalidad')->orderBy('descripcion')->get();
    $etnias               = DB::table('catalogo_etnia')->orderBy('codigo')->get();
    $discapacidades       = DB::table('catalogo_tipo_de_discapacidad')->orderBy('codigo')->get();
    $diagnosticos         = DB::table('catalogo_diagnostico')->orderBy('codigo')->get();
    $apoyosEducativos     = DB::table('catalogo_servicios_de_apoyo_educativo')->orderBy('codigo')->get();
    $actividadesEconomicas = DB::table('catalogo_actividad_economica')->orderBy('codigo')->get();
    $estadosCiviles       = DB::table('catalogo_estado_civil')->orderBy('codigo')->get();
    $estadosFamiliares    = DB::table('catalogo_estado_familiar')->orderBy('codigo')->get();

    // Catálogos iniciales del Literal C (Residencia)
    $zonasResidencia = DB::table('catalogo_zona_residencia')->orderBy('codigo')->get();
    $tiposVivienda   = DB::table('catalogo_tipo_vivienda')->orderBy('codigo')->get();
    $departamentos   = DB::table('catalogo_departamentos')->orderBy('codigo')->get();

    // Carga inicial de dependientes si el alumno ya tiene departamento asignado
    $municipios = [];
    $distritos  = [];
    $cantones   = [];

    if (!empty($alumno->codigo_departamento)) {
        $municipios = DB::table('catalogo_municipios')
            ->where('codigo_departamento', $alumno->codigo_departamento)
            ->orderBy('descripcion')
            ->get();
    }

    if (!empty($alumno->codigo_departamento) && !empty($alumno->codigo_municipio)) {
        $distritos = DB::table('catalogo_distritos')
            ->where('codigo_departamento', $alumno->codigo_departamento)
            ->where('codigo_municipio', $alumno->codigo_municipio)
            ->orderBy('descripcion')
            ->get();
    }

            // Carga inicial de cantones cuando el alumno ya tiene datos guardados
            if (!empty($alumno->codigo_departamento) && !empty($alumno->codigo_municipio) && !empty($alumno->codigo_distrito)) {
                    
                    $dep  = str_pad(trim($alumno->codigo_departamento), 2, '0', STR_PAD_LEFT);
                    $mun  = str_pad(trim($alumno->codigo_municipio), 2, '0', STR_PAD_LEFT);
                    $dist = str_pad(trim($alumno->codigo_distrito), 2, '0', STR_PAD_LEFT);

                    // Filtro estricto: codigo_departamento + codigo_nuevo_municipio + codigo_distrito
                    $cantones = DB::table('catalogo_canton')
                        ->where('codigo_departamento', $dep)
                        ->where('codigo_nuevo_municipio', $mun)
                        ->where('codigo_distrito', $dist)
                        ->orderBy('descripcion', 'asc')
                        ->get();
                }

// Catálogo del Literal D (Servicios Básicos)
    $abastecimientosAgua = DB::table('catalogo_abastecimiento')->orderBy('codigo', 'asc')->get();
// Catálogos del Literal E (Servicios de Comunicación)
    $companiasInternet = DB::table('catalogo_company')->orderBy('descripcion', 'asc')->get();
    $modalidadesClase  = DB::table('catalogo_clase_bajo_modalidad')->orderBy('codigo', 'asc')->get();
    $canalesAtencion   = DB::table('catalogo_clases_canales_atencion')->orderBy('codigo', 'asc')->get();

    // RETURN VIEW
    return view('estudiantes.edit_literal_b', compact(
        'alumno',
        'nacionalidades',
        'etnias',
        'discapacidades',
        'diagnosticos',
        'apoyosEducativos',
        'actividadesEconomicas',
        'estadosCiviles',
        'estadosFamiliares',
        'zonasResidencia',
        'tiposVivienda',
        'departamentos',
        'municipios',
        'distritos',
        'cantones',
        'abastecimientosAgua',
        'companiasInternet',
        'modalidadesClase',
        'canalesAtencion'
    ));
}

/**
 * Métodos AJAX para selectores dependientes
 */
public function getMunicipios($codigo_dep)
{
    $municipios = DB::table('catalogo_municipios')
        ->where('codigo_departamento', $codigo_dep)
        ->orderBy('descripcion')
        ->get();
    return response()->json($municipios);
}

public function getDistritos($codigo_dep, $codigo_mun)
{
    $distritos = DB::table('catalogo_distritos')
        ->where('codigo_departamento', $codigo_dep)
        ->where('codigo_municipio', $codigo_mun)
        ->orderBy('descripcion')
        ->get();
    return response()->json($distritos);
}

/**
 * Petición AJAX: Obtiene cantones filtrados por Departamento, Nuevo Municipio y Distrito.
 */
public function getCantones($codigo_dep, $codigo_mun, $codigo_dist)
{
    $codigo_dep  = str_pad(trim($codigo_dep), 2, '0', STR_PAD_LEFT);
    $codigo_mun  = str_pad(trim($codigo_mun), 2, '0', STR_PAD_LEFT);
    $codigo_dist = str_pad(trim($codigo_dist), 2, '0', STR_PAD_LEFT);

    // Consulta filtrada estrictamente por las tres claves geográficas
    $cantones = DB::table('catalogo_canton')
        ->where('codigo_departamento', $codigo_dep)
        ->where('codigo_nuevo_municipio', $codigo_mun)
        ->where('codigo_distrito', $codigo_dist)
        ->orderBy('descripcion', 'asc')
        ->get();

    return response()->json($cantones);
}

/**
 * Guarda la información del Literal E (Servicios de Comunicación).
 */
public function guardarLiteralE(Request $request, $id)
{
    try {
        DB::table('alumno')
            ->where('id_alumno', $id)
            ->update([
                'acceso_internet'                       => $request->input('acceso_internet'),
                'tipo_conexion_internet'                => $request->input('tipo_conexion_internet'),
                'codigo_tipo_conexion_internet_company' => $request->input('codigo_tipo_conexion_internet_company'),
                'posee_radio'                           => $request->input('posee_radio'),
                'posee_tv'                              => $request->input('posee_tv'),
                'sintoniza_canal_10'                    => $request->input('sintoniza_canal_10'),
                'posee_computadora'                     => $request->input('posee_computadora'),
                'codigo_clases_bajo_modalidad'          => $request->input('codigo_clases_bajo_modalidad'),
                'codigo_clases_canales_atencion'        => $request->input('codigo_clases_canales_atencion'),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => '¡Los datos de servicios de comunicación (Literal E) se guardaron correctamente!'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'errors' => ['Error al guardar en la base de datos: ' . $e->getMessage()]
        ], 500);
    }
}


/**
 * Guarda la información del Literal D (Servicios Básicos).
 */
public function guardarLiteralD(Request $request, $id)
{
    try {
        DB::table('alumno')
            ->where('id_alumno', $id)
            ->update([
                'servicio_energia'      => $request->input('servicio_energia'),
                'recoleccion_basura'    => $request->input('recoleccion_basura'),
                'codigo_abastecimiento' => $request->input('codigo_abastecimiento'),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => '¡Los datos de servicios básicos (Literal D) se guardaron correctamente!'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'errors' => ['Error al guardar en la base de datos: ' . $e->getMessage()]
        ], 500);
    }
}


/**
 * Guarda la información del Literal C (Residencia).
 */
public function guardarLiteralC(Request $request, $id)
{
    try {
        DB::table('alumno')
            ->where('id_alumno', $id)
            ->update([
                'codigo_zona_residencia' => $request->input('codigo_zona_residencia'),
                'codigo_tipo_vivienda'   => $request->input('codigo_tipo_vivienda'),
                'codigo_departamento'    => $request->input('codigo_departamento'),
                'codigo_municipio'       => $request->input('codigo_municipio'),
                'codigo_distrito'        => $request->input('codigo_distrito'),
                'codigo_canton'          => $request->input('codigo_canton'),
                'caserio'                => $request->input('caserio'),
                'direccion_alumno'       => $request->input('direccion_alumno'),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => '¡Los datos de residencia del Literal C se guardaron correctamente!'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'errors' => ['Error al guardar en la base de datos: ' . $e->getMessage()]
        ], 500);
    }
}

   /**
 * Guarda los cambios correspondientes al Literal B del estudiante.
 */
    public function guardarLiteralB(Request $request, $id)
    {
        // 1. Validación de campos con mensajes personalizados
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nombre_completo'  => 'required|string|max:150',
            'apellido_paterno' => 'required|string|max:100',
            'direccion_email'  => 'nullable|email|max:150',
            'telefono_celular' => 'nullable|string|max:15',
        ], [
            'nombre_completo.required'  => 'El campo Nombres es obligatorio.',
            'apellido_paterno.required' => 'El Primer Apellido es obligatorio.',
            'direccion_email.email'     => 'El Correo Electrónico no tiene un formato válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        try {
            // 2. Si el correo está vacío, se autogenera con el dominio institucional
            $nie = trim($request->input('codigo_nie'));
            $email = trim($request->input('direccion_email'));
            
            if (empty($email) && !empty($nie)) {
                $email = $nie . '@clases.edu.sv';
            }

            // 3. Actualización en la tabla alumno
            DB::table('alumno')
                ->where('id_alumno', $id)
                ->update([
                    'nombre_completo'            => $request->input('nombre_completo'),
                    'apellido_paterno'           => $request->input('apellido_paterno'),
                    'apellido_materno'           => $request->input('apellido_materno'),
                    'dui'                        => $request->input('dui'),
                    'pasaporte'                  => $request->input('pasaporte'),
                    'fecha_nacimiento'           => $request->input('fecha_nacimiento'),
                    'codigo_nacionalidad'        => $request->input('codigo_nacionalidad'),
                    'retornado'                  => $request->input('retornado'),
                    'posee_pn'                   => $request->input('posee_pn'),
                    'presenta_pn'                => $request->input('presenta_pn'),
                    'codigo_genero'              => $request->input('codigo_genero'),
                    'codigo_etnia'               => $request->input('codigo_etnia'),
                    'codigo_discapacidad'        => $request->input('codigo_discapacidad'),
                    'codigo_diagnostico'         => $request->input('codigo_diagnostico'),
                    'codigo_apoyo_educativo'     => $request->input('codigo_apoyo_educativo'),
                    'direccion_email'            => $email,
                    'telefono_celular'           => $request->input('telefono_celular'),
                    'whatsapp'                   => $request->input('whatsapp'),
                    'codigo_actividad_economica' => $request->input('codigo_actividad_economica'),
                    'codigo_estado_civil'        => $request->input('codigo_estado_civil'),
                    'codigo_estado_familiar'     => $request->input('codigo_estado_familiar'),
                    'embarazada'                 => $request->input('embarazada'),
                    'tiene_hijos'                => $request->input('tiene_hijos'),
                    'cantidad_hijos'             => $request->input('cantidad_hijos', 0),
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => '¡Los datos del Literal B se actualizaron correctamente!'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'errors' => ['Error interno en la base de datos: ' . $e->getMessage()]
            ], 500);
        }
    }
}