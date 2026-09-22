<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Codedge\Fpdf\Fpdf\Fpdf;
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

    // 2. Año lectivo actual en 2 dígitos
    $annLectivoActual = date('y');

    // 3. Consulta base
    $query = DB::table('alumno as al')

        // Matrícula activa del estudiante
        ->join('alumno_matricula as mat', function ($join) use ($annLectivoActual) {
            $join->on('mat.codigo_alumno', '=', 'al.id_alumno')
                 ->where('mat.codigo_ann_lectivo', '=', $annLectivoActual)
                 ->where('mat.retirado', '=', false);
        })

        // Grado
        ->join('grado_ano as gr', function ($join) {
            $join->on('gr.codigo', '=', 'mat.codigo_grado');
        })

        // Sección
        ->join('seccion as sec', function ($join) {
            $join->on('sec.codigo', '=', 'mat.codigo_seccion');
        })

        // Turno
        ->leftJoin('turno as tur', function ($join) {
            $join->on('tur.codigo', '=', 'mat.codigo_turno');
        })

        // Bachillerato / ciclo
        ->leftJoin('bachillerato_ciclo as bach', function ($join) {
            $join->on('bach.codigo', '=', 'mat.codigo_bach_o_ciclo');
        })

        // Verificar que el docente sea encargado del grupo.
        // EXISTS evita duplicar estudiantes si existen varios registros
        // coincidentes en encargado_grado.
        ->whereExists(function ($subquery) use ($codigoDocente, $annLectivoActual) {
            $subquery->select(DB::raw(1))
                ->from('encargado_grado as enc_gr')
                ->whereColumn('enc_gr.codigo_grado', 'mat.codigo_grado')
                ->whereColumn('enc_gr.codigo_seccion', 'mat.codigo_seccion')
                ->whereColumn('enc_gr.codigo_turno', 'mat.codigo_turno')
                ->whereColumn(
                                'enc_gr.codigo_bachillerato',
                                'mat.codigo_bach_o_ciclo'
                            )
                ->where('enc_gr.codigo_docente', '=', $codigoDocente)
                ->where('enc_gr.encargado', '=', true)
                ->where('enc_gr.codigo_ann_lectivo', '=', $annLectivoActual);
        });

    // 4. Aplicar búsqueda
    if ($buscar) {
        $query->where(function ($q) use ($buscar) {
            $q->where('al.nombre_completo', 'ILIKE', "%{$buscar}%")
              ->orWhere('al.codigo_nie', 'ILIKE', "%{$buscar}%")
              ->orWhere('al.apellido_paterno', 'ILIKE', "%{$buscar}%")
              ->orWhere('al.apellido_materno', 'ILIKE', "%{$buscar}%");
        });
    }

    // 5. Selección
    $alumnos = $query
        ->select(
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

            // Obtener únicamente la firma del encargado principal
            DB::raw("(
                SELECT ae.firma_autorizacion
                FROM alumno_encargado ae
                WHERE ae.codigo_alumno = al.id_alumno
                  AND ae.encargado = true
                ORDER BY ae.id_alumno_encargado ASC
                LIMIT 1
            ) as firma_autorizacion")
        )

        ->orderBy(
            DB::raw("translate(lower(al.apellido_paterno), 'áéíóúü', 'aeiouu')"),
            'asc'
        )
        ->orderBy(
            DB::raw("translate(lower(al.apellido_materno), 'áéíóúü', 'aeiouu')"),
            'asc'
        )
        ->orderBy(
            DB::raw("translate(lower(al.nombre_completo), 'áéíóúü', 'aeiouu')"),
            'asc'
        )
        ->get();

    return view('estudiantes.index_ficha', compact('alumnos'));
}
    /**
 * Muestra el formulario para editar la Ficha del Estudiante (Literales B y C).
 */
public function edit($id)
{
// 1. Obtener información de la institución con su ubicación geográfica descriptiva
    $institucion = DB::table('informacion_institucion as inst')
        ->leftJoin('catalogo_departamentos as dep', DB::raw('TRIM(inst.codigo_departamento)'), '=', DB::raw('TRIM(dep.codigo)'))
        ->leftJoin('catalogo_municipios as mun', function($join) {
            $join->on(DB::raw('TRIM(inst.codigo_departamento)'), '=', DB::raw('TRIM(mun.codigo_departamento)'))
                 ->on(DB::raw('TRIM(inst.codigo_municipio)'), '=', DB::raw('TRIM(mun.codigo)'));
        })
        ->leftJoin('catalogo_distritos as dist', function($join) {
            $join->on(DB::raw('TRIM(inst.codigo_departamento)'), '=', DB::raw('TRIM(dist.codigo_departamento)'))
                 ->on(DB::raw('TRIM(inst.codigo_municipio)'), '=', DB::raw('TRIM(dist.codigo_municipio)'))
                 ->on(DB::raw('TRIM(inst.codigo_distrito)'), '=', DB::raw('TRIM(dist.codigo)'));
        })
        ->select(
            'inst.*',
            'dep.descripcion as departamento_nombre',
            'mun.descripcion as municipio_nombre',
            'dist.descripcion as distrito_nombre'
        )
        ->first();

    // 2. Obtener el año lectivo actual en 2 dígitos (ejemplo: '26')
    $annLectivoActual = date('y');

    // 3. Obtener la matrícula del estudiante para el año lectivo actual con sus descripciones
    $matricula = DB::table('alumno_matricula as mat')
        ->leftJoin('grado_ano as gra', DB::raw('TRIM(mat.codigo_grado)'), '=', DB::raw('TRIM(gra.codigo)'))
        ->leftJoin('seccion as sec', DB::raw('TRIM(mat.codigo_seccion)'), '=', DB::raw('TRIM(sec.codigo)'))
        ->leftJoin('turno as tur', DB::raw('TRIM(mat.codigo_turno)'), '=', DB::raw('TRIM(tur.codigo)'))
        ->where('mat.codigo_alumno', $id)
        ->where(DB::raw('TRIM(mat.codigo_ann_lectivo)'), $annLectivoActual)
        ->select(
            'mat.*',
            'gra.nombre as grado_nombre',
            'sec.nombre as seccion_nombre',
            'tur.nombre as turno_nombre'
        )
        ->first();



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
    // Catálogos del Literal B
$nacionalidades = DB::table('catalogo_nacionalidad')
    ->select('codigo', 'descripcion', 'gentilicio')
    ->orderBy('descripcion')
    ->get();
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

// Catálogos para Literal G
    $parentescos = DB::table('catalogo_familiar')->orderBy('codigo', 'asc')->get();
    $gradosEscolaridad = DB::table('catalogo_ultimo_grado_aprobado')->orderBy('codigo', 'asc')->get();

    // Obtener los familiares/encargados registrados para el estudiante
    $encargados = DB::table('alumno_encargado')
        ->where('codigo_alumno', $id)
        ->orderBy('encargado', 'desc') // Muestra primero al responsable principal (true)
        ->get();

    // RETURN VIEW
    return view('estudiantes.edit_literal_b', compact(
        'alumno',
        'institucion',
        'matricula',
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
        'canalesAtencion',
        'parentescos',
        'gradosEscolaridad',
        'encargados'
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
 * Guarda o actualiza un registro específico en alumno_encargado.
 */
public function guardarResponsable(Request $request, $id_alumno)
{
    try {
        $id_encargado = $request->input('id_alumno_encargado');
        $esEncargado   = $request->has('encargado') ? true : false;

        // Si este registro se marca como encargado principal, desmarcamos los demás del estudiante
        if ($esEncargado) {
            DB::table('alumno_encargado')
                ->where('codigo_alumno', $id_alumno)
                ->update(['encargado' => false]);
        }

        // Actualizamos los campos del encargado especifico
        DB::table('alumno_encargado')
            ->where('id_alumno_encargado', $id_encargado)
            ->where('codigo_alumno', $id_alumno)
            ->update([
                'dui'                             => $request->input('dui'),
                'pasaporte_otro'                  => $request->input('pasaporte_otro'),
                'codigo_familiar'                 => $request->input('codigo_familiar'),
                'nombres'                         => $request->input('nombres'),
                'telefono'                        => $request->input('telefono'),
                'telefono_alternativo'            => $request->input('telefono_alternativo'),
                'correo_electronico'              => $request->input('correo_electronico'),
                'codigo_ultimo_grado_aprobado'    => $request->input('codigo_ultimo_grado_aprobado'),
                'encargado'                       => $esEncargado
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => '¡Los datos del responsable se actualizaron correctamente!'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'errors' => ['Error al actualizar el responsable: ' . $e->getMessage()]
        ], 500);
    }
}

/**
 * Crea un nuevo registro de encargado si faltan registros (máximo 3).
 */
public function crearResponsable(Request $request, $id_alumno)
{
    try {
        $conteoActual = DB::table('alumno_encargado')->where('codigo_alumno', $id_alumno)->count();

        if ($conteoActual >= 3) {
            return response()->json([
                'status' => 'error',
                'errors' => ['El estudiante ya cuenta con el número máximo de 3 responsables registrados.']
            ], 422);
        }

        $esPrimero = ($conteoActual == 0);

        DB::table('alumno_encargado')->insert([
            'codigo_alumno'                => $id_alumno,
            'dui'                          => $request->input('dui'),
            'pasaporte_otro'             => $request->input('pasaporte_otro'),
            'codigo_familiar'            => $request->input('codigo_familiar'),
            'nombres'                    => $request->input('nombres'),
            'telefono'                   => $request->input('telefono'),
            'telefono_alternativo'       => $request->input('telefono_alternativo'),
            'correo_electronico'         => $request->input('correo_electronico'),
            'codigo_ultimo_grado_aprobado' => $request->input('codigo_ultimo_grado_aprobado'),
            'encargado'                  => $esPrimero ? true : ($request->has('encargado') ? true : false)
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => '¡Nuevo responsable agregado exitosamente!'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'errors' => ['Error al crear el registro: ' . $e->getMessage()]
        ], 500);
    }
}



/**
 * Guarda la información del Literal F (Servicio Social).
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id  ID del alumno
 * @return \Illuminate\Http\JsonResponse
 */
public function guardarLiteralF(Request $request, $id)
{
    try {
        // Actualización de campos en la tabla 'alumno'
        DB::table('alumno')
            ->where('id_alumno', $id)
            ->update([
                'servicio_social_realizado'        => $request->input('servicio_social_realizado'),
                'servicio_social_fecha_finalizado' => $request->input('servicio_social_fecha_finalizado') ?: null,
                'servicio_social_horas'            => $request->input('servicio_social_horas') ?: null,
                'servicio_social_descripcion'      => $request->input('servicio_social_descripcion'),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => '¡Los datos de Servicio Social (Literal F) se guardaron correctamente!'
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

/**
     * Dibuja una casilla de selección circular (Radio button)
     */
    private function drawRadio($pdf, $x, $y, $checked, $label)
    {
        $pdf->SetLineWidth(0.2);
        // Dibuja un círculo pequeño para el radio button
        $pdf->Ellipse($x + 1.5, $y + 1.5, 1.2, 1.2);
        if ($checked) {
            $pdf->SetFillColor(0, 0, 0);
            $pdf->Ellipse($x + 1.5, $y + 1.5, 0.7, 0.7, 'F');
        }
        $pdf->SetXY($x + 3.5, $y - 0.5);
        $pdf->SetFont('Arial', '', 5.5);
        $pdf->Cell(0, 4, utf8_decode($label), 0, 0, 'L');
    }

    /**
     * Dibuja una casilla de selección cuadrada (Checkbox)
     */
    private function drawCheckbox($pdf, $x, $y, $checked, $label)
    {
        $pdf->SetLineWidth(0.2);
        $pdf->Rect($x, $y, 2.5, 2.5);
        if ($checked) {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY($x, $y - 0.4);
            $pdf->Cell(2.5, 2.5, 'X', 0, 0, 'C');
        }
        $pdf->SetXY($x + 3.5, $y - 0.5);
        $pdf->SetFont('Arial', '', 5.5);
        $pdf->Cell(0, 4, utf8_decode($label), 0, 0, 'L');
    }

    /**
     * Dibuja una caja de entrada de texto etiquetada
     */
    private function drawInputBox($pdf, $x, $y, $w, $h, $label, $value, $sublabel = '')
    {
        if (!empty($label)) {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY($x, $y);
            $pdf->Cell($w, 3, utf8_decode($label), 0, 0, 'L');
        }
        
        $boxY = !empty($label) ? $y + 3 : $y;
        $pdf->Rect($x, $boxY, $w, $h);
        
        if (!empty($sublabel)) {
            $pdf->SetFont('Arial', '', 5);
            $pdf->SetXY($x + 1, $boxY - 2.5);
            $pdf->Cell($w, 2, utf8_decode($sublabel), 0, 0, 'L');
        }

        if (!empty($value)) {
            $pdf->SetFont('Arial', '', 6.5);
            $pdf->SetXY($x + 1, $boxY);
            $pdf->Cell($w - 2, $h, utf8_decode($value), 0, 0, 'L');
        }
    }

   public function generarPdf($id)
{

    // =========================================================================
    // 1. PASO OBLIGATORIO: DEFINIR HELPERS AL INICIO DEL MÉTODO
    // =========================================================================
    $txt = function ($str) {
        return utf8_decode($str);
    };

    $normalizar = function ($texto) {
        if (empty($texto)) {
            return '';
        }
        $str = mb_strtoupper((string) $texto, 'UTF-8');
        return str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'],
            ['A', 'E', 'I', 'O', 'U', 'U', 'N'],
            $str
        );
    };

    $tiene = function ($texto, $patron) use ($normalizar) {
        if (empty($texto) || empty($patron)) {
            return false;
        }
        return str_contains($normalizar($texto), $normalizar($patron));
    };

    /*
     * FICHA DEL ESTUDIANTE - MATRÍCULA
     * Versión compactada verticalmente para mantener
     * los puntos 1-19 en página 1 y 20-54 en página 2.
     */

    // ================================================================
    // 1. DATOS PRINCIPALES
    // ================================================================
    $alumno = DB::table('alumno')->where('id_alumno', $id)->first();

    if (!$alumno) {
        return redirect()->route('ficha.index')
            ->with('error', 'El estudiante no existe.');
    }

    $institucion = DB::table('informacion_institucion')->first();
    $annLectivoActual = date('y');
    $annLectivoPdf = '20' . $annLectivoActual;

    $matricula = DB::table('alumno_matricula as mat')
        ->leftJoin(
            'grado_ano as gra',
            DB::raw('TRIM(mat.codigo_grado)'),
            '=',
            DB::raw('TRIM(gra.codigo)')
        )
        ->leftJoin(
            'seccion as sec',
            DB::raw('TRIM(mat.codigo_seccion)'),
            '=',
            DB::raw('TRIM(sec.codigo)')
        )
        ->leftJoin(
            'turno as tur',
            DB::raw('TRIM(mat.codigo_turno)'),
            '=',
            DB::raw('TRIM(tur.codigo)')
        )
        ->where('mat.codigo_alumno', $id)
        ->where(
            DB::raw('TRIM(mat.codigo_ann_lectivo)'),
            $annLectivoActual
        )
        ->select(
            'mat.*',
            'gra.nombre as grado_nombre',
            'sec.nombre as seccion_nombre',
            'tur.nombre as turno_nombre'
        )
        ->first();

    // ================================================================
    // 2. ENCARGADO PRINCIPAL
    // ================================================================
    $encargado = DB::table('alumno_encargado')
        ->where('codigo_alumno', $id)
        ->orderBy('encargado', 'desc')
        ->orderBy('id_alumno_encargado', 'asc')
        ->first();

    // ================================================================
    // 3. CATÁLOGOS
    // ================================================================
    $catalogo = function ($tabla, $codigo) {
        if ($codigo === null || $codigo === '') {
            return '';
        }

        try {
            $valor = DB::table($tabla)
                ->whereRaw(
                    'TRIM(CAST(codigo AS TEXT)) = ?',
                    [trim((string) $codigo)]
                )
                ->value('descripcion');

            return $valor ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    };

// 1. Obtener el gentilicio directamente desde la tabla catalogo_nacionalidad
$gentilicioBd = null;
if (!empty($alumno->codigo_nacionalidad)) {
    try {
        $gentilicioBd = DB::table('catalogo_nacionalidad')
            ->whereRaw('TRIM(CAST(codigo AS TEXT)) = ?', [trim((string) $alumno->codigo_nacionalidad)])
            ->value('gentilicio'); // Extrae la columna 'gentilicio'
    } catch (\Throwable $e) {
        $gentilicioBd = null;
    }
}

// 2. Si no viene en el catálogo o no hay código, evalúa contra campos alternativos o un valor por defecto
if (empty($gentilicioBd)) {
    $gentilicioBd = $alumno->gentilicio ?? $alumno->nacionalidad ?? 'SALVADOREÑA';
}

$nac = $normalizar($gentilicioBd);

    $etnia = $catalogo(
        'catalogo_etnia',
        $alumno->codigo_etnia ?? null
    );

    if ($etnia === '') {
        $etnia = $alumno->etnia ?? 'NO APLICA';
    }

    $discapacidad = $catalogo(
        'catalogo_tipo_de_discapacidad',
        $alumno->codigo_discapacidad ?? null
    );

    if ($discapacidad === '') {
        $discapacidad = $alumno->discapacidad ?? 'NO APLICA';
    }

    $diagnostico = $catalogo(
        'catalogo_diagnostico',
        $alumno->codigo_diagnostico ?? null
    );

    $apoyoEducativo = $catalogo(
        'catalogo_servicios_de_apoyo_educativo',
        $alumno->codigo_apoyo_educativo ?? null
    );

    $actividadEconomica = $catalogo(
        'catalogo_actividad_economica',
        $alumno->codigo_actividad_economica ?? null
    );

    $estadoCivil = $catalogo(
        'catalogo_estado_civil',
        $alumno->codigo_estado_civil ?? null
    );

    $estadoFamiliar = $catalogo(
        'catalogo_estado_familiar',
        $alumno->codigo_estado_familiar ?? null
    );

    $parentesco = $catalogo(
        'catalogo_familiar',
        $encargado->codigo_familiar ?? null
    );

    $escolaridadResponsable = $catalogo(
        'catalogo_ultimo_grado_aprobado',
        $encargado->codigo_ultimo_grado_aprobado ?? null
    );

    $abastecimiento = $catalogo(
        'catalogo_abastecimiento',
        $alumno->codigo_abastecimiento ?? null
    );

    $tipoVivienda = $catalogo(
        'catalogo_tipo_vivienda',
        $alumno->codigo_tipo_vivienda ?? null
    );

    $zonaResidencia = $catalogo(
        'catalogo_zona_residencia',
        $alumno->codigo_zona_residencia ?? null
    );

    $departamento = $catalogo(
        'catalogo_departamentos',
        $alumno->codigo_departamento ?? null
    );

    $municipio = '';

    if (
        !empty($alumno->codigo_departamento) &&
        !empty($alumno->codigo_municipio)
    ) {
        try {
            $municipio = DB::table('catalogo_municipios')
                ->whereRaw(
                    'TRIM(CAST(codigo_departamento AS TEXT)) = ?',
                    [trim((string) $alumno->codigo_departamento)]
                )
                ->whereRaw(
                    'TRIM(CAST(codigo AS TEXT)) = ?',
                    [trim((string) $alumno->codigo_municipio)]
                )
                ->value('descripcion') ?? '';
        } catch (\Throwable $e) {
            $municipio = '';
        }
    }

    $canton = '';

    if (!empty($alumno->codigo_canton)) {
        try {
            $qCanton = DB::table('catalogo_canton')
                ->whereRaw(
                    'TRIM(CAST(codigo AS TEXT)) = ?',
                    [trim((string) $alumno->codigo_canton)]
                );

            if (!empty($alumno->codigo_departamento)) {
                $qCanton->whereRaw(
                    'TRIM(CAST(codigo_departamento AS TEXT)) = ?',
                    [
                        str_pad(
                            trim((string) $alumno->codigo_departamento),
                            2,
                            '0',
                            STR_PAD_LEFT
                        )
                    ]
                );
            }

            if (!empty($alumno->codigo_municipio)) {
                $qCanton->whereRaw(
                    'TRIM(CAST(codigo_nuevo_municipio AS TEXT)) = ?',
                    [
                        str_pad(
                            trim((string) $alumno->codigo_municipio),
                            2,
                            '0',
                            STR_PAD_LEFT
                        )
                    ]
                );
            }

            if (!empty($alumno->codigo_distrito)) {
                $qCanton->whereRaw(
                    'TRIM(CAST(codigo_distrito AS TEXT)) = ?',
                    [
                        str_pad(
                            trim((string) $alumno->codigo_distrito),
                            2,
                            '0',
                            STR_PAD_LEFT
                        )
                    ]
                );
            }

            $canton = $qCanton->value('descripcion') ?? '';
        } catch (\Throwable $e) {
            $canton = '';
        }
    }

    // ================================================================
    // 4. NORMALIZACIÓN
    // ================================================================
    $normalizar = function ($valor) {
        $valor = trim((string) ($valor ?? ''));
        $valor = mb_strtoupper($valor, 'UTF-8');

        return str_replace(
            ['Á','É','Í','Ó','Ú','Ü','Ñ'],
            ['A','E','I','O','U','U','N'],
            $valor
        );
    };

    $tiene = function ($valor, $texto) use ($normalizar) {
        $v = $normalizar($valor);
        $t = $normalizar($texto);

        return $v === $t || strpos($v, $t) !== false;
    };

    // ================================================================
    // 5. NOMBRES
    // ================================================================
    $nombre1 = trim((string) ($alumno->nombre_1 ?? ''));
    $nombre2 = trim((string) ($alumno->nombre_2 ?? ''));
    $nombre3 = trim((string) ($alumno->nombre_3 ?? ''));

    if ($nombre1 === '' && !empty($alumno->nombre_completo)) {

        $partesNombres = preg_split(
            '/\s+/',
            trim((string) $alumno->nombre_completo)
        );

        $nombre1 = $partesNombres[0] ?? '';
        $nombre2 = $partesNombres[1] ?? '';
        $nombre3 = count($partesNombres) > 2
            ? implode(' ', array_slice($partesNombres, 2))
            : '';
    }

    $apellido1 = trim(
        (string) (
            $alumno->apellido_1 ??
            $alumno->apellido_paterno ??
            ''
        )
    );

    $apellido2 = trim(
        (string) (
            $alumno->apellido_2 ??
            $alumno->apellido_materno ??
            ''
        )
    );

    $apellido3 = trim(
        (string) ($alumno->apellido_3 ?? '')
    );

    // ================================================================
    // 6. FECHA DE NACIMIENTO
    // ================================================================
    $diaNac = '';
    $mesNac = '';
    $anioNac = '';

    if (!empty($alumno->fecha_nacimiento)) {

        $fechaComp = strtotime($alumno->fecha_nacimiento);

        if ($fechaComp !== false) {
            $diaNac = date('d', $fechaComp);
            $mesNac = date('m', $fechaComp);
            $anioNac = date('Y', $fechaComp);
        }
    }

    // ================================================================
    // 7. SEXO
    // ================================================================
    $genero = $normalizar(
        $alumno->codigo_genero ??
        ($alumno->sexo ?? '')
    );

    $esMujer = in_array(
        $genero,
        ['F', 'FEMENINO', 'MUJER', '2'],
        true
    );

    $esHombre = in_array(
        $genero,
        ['M', 'MASCULINO', 'HOMBRE', '1'],
        true
    );

    // ================================================================
    // 8. FUNCIONES DE DIBUJO
    // ================================================================
    $txt = function ($texto) {
        return iconv(
            'UTF-8',
            'windows-1252//TRANSLIT',
            (string) ($texto ?? '')
        );
    };

    /*
     * RADIO COMPACTO
     */
    $radio = function (
        $pdf,
        $x,
        $y,
        $checked,
        $label,
        $fontSize = 5.6
    ) use ($txt) {

        $pdf->SetLineWidth(0.20);

        $pdf->Ellipse(
            $x + 1.25,
            $y + 1.55,
            1.15,
            1.15
        );

        if ($checked) {

            $pdf->SetFillColor(0, 0, 0);

            $pdf->Ellipse(
                $x + 1.25,
                $y + 1.55,
                0.68,
                0.68,
                'F'
            );
        }

        $pdf->SetFont(
            'Arial',
            '',
            $fontSize
        );

        $pdf->SetXY(
            $x + 3.1,
            $y - 0.05
        );

        $pdf->Cell(
            0,
            3.2,
            $txt($label),
            0,
            0,
            'L'
        );
    };

    /*
     * CHECKBOX COMPACTO
     */
    $check = function (
        $pdf,
        $x,
        $y,
        $checked,
        $label,
        $fontSize = 5.3
    ) use ($txt) {

        $pdf->SetLineWidth(0.20);

        $pdf->Rect(
            $x,
            $y,
            2.2,
            2.2
        );

        if ($checked) {

            $pdf->SetFont(
                'Arial',
                'B',
                5.8
            );

            $pdf->SetXY(
                $x,
                $y - 0.35
            );

            $pdf->Cell(
                2.2,
                2.2,
                'X',
                0,
                0,
                'C'
            );
        }

        $pdf->SetFont(
            'Arial',
            '',
            $fontSize
        );

        $pdf->SetXY(
            $x + 3.1,
            $y - 0.25
        );

        $pdf->Cell(
            0,
            3.2,
            $txt($label),
            0,
            0,
            'L'
        );
    };

    /*
     * CAJA
     */
    $box = function (
        $pdf,
        $x,
        $y,
        $w,
        $h,
        $value = '',
        $label = '',
        $sub = '',
        $align = 'L'
    ) use ($txt) {

        if ($label !== '') {

            $pdf->SetFont(
                'Arial',
                'B',
                5.4
            );

            $pdf->SetXY(
                $x,
                $y - 2.7
            );

            $pdf->Cell(
                $w,
                2.7,
                $txt($label),
                0,
                0,
                'L'
            );
        }

        $pdf->Rect(
            $x,
            $y,
            $w,
            $h
        );

        if ($sub !== '') {

            $pdf->SetFont(
                'Arial',
                '',
                4.9
            );

            $pdf->SetXY(
                $x + 0.8,
                $y - 2.9
            );

            $pdf->Cell(
                $w - 1.6,
                2.4,
                $txt($sub),
                0,
                0,
                'L'
            );
        }

        if (
            $value !== null &&
            trim((string) $value) !== ''
        ) {

            $pdf->SetFont(
                'Arial',
                '',
                6.3
            );

            $pdf->SetXY(
                $x + 1,
                $y + 0.20
            );

            $pdf->Cell(
                $w - 2,
                $h - 0.4,
                $txt($value),
                0,
                0,
                $align
            );
        }
    };

    $line = function ($pdf, $y) {

        $pdf->SetLineWidth(0.22);

        $pdf->Line(
            8,
            $y,
            200,
            $y
        );
    };

    // Modificar la definición existente de $section
$section = function ($pdf, $y, $titulo) use ($txt) {
    // 1. Configurar colores: Fondo azul claro (RGB: 217, 234, 247), texto y borde negro
    $pdf->SetFillColor(217, 234, 247);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);

    // 2. Fuente negrita
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(7, $y);

    // 3. Imprimir celda: Borde arriba y abajo ('TB'), con relleno activo (1)
    // Se utiliza alto de 4.5 mm (o el alto estándar de tus secciones)
    $pdf->Cell(195, 4.5, $txt($titulo), 'TB', 1, 'C', 1);

    // 4. Retornar la nueva posición de $y para que el texto posterior no quede encimado
    return $y + 4.5;
};

    // ================================================================
    // 9. PDF
    // ================================================================
    $pdf = new Fpdf(
        'P',
        'mm',
        'Letter'
    );

    $pdf->SetMargins(
        8,
        6,
        8
    );

    $pdf->SetAutoPageBreak(false);

    $pdf->SetTitle(
        'Ficha del Estudiante - Matrícula ' .
        $annLectivoPdf
    );

    $pdf->SetAuthor(
        'Sistema Académico'
    );

// ================================================================
// CONFIGURACIÓN Y DIBUJO DE ENCABEZADOS DE SECCIÓN (AZUL CLARO)
// ================================================================

// Definir color azul claro para el fondo (R: 217, G: 234, B: 247)
$pdf->SetFillColor(217, 234, 247); 

// Definir color negro para el texto y bordes
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(0, 0, 0);

// Función helper para imprimir la franja del título de sección
$seccionTitulo = function ($pdf, $y, $texto) use ($txt) {
    $pdf->SetFont('Arial', 'B', 7);  // Fuente negrita
    $pdf->SetXY(7, $y);             // Posición X e Y del encabezado
    
    // Ancho: 162 mm, Alto: 4.5 mm
    // Borde: 'TB' (Top y Bottom / Arriba y Abajo)
    // Relleno: 1 (Activo)
    $pdf->Cell(195, 4.5, $txt($texto), 'TB', 1, 'C', 1); 
};

    // ================================================================
    // PÁGINA 1
    // ================================================================
    $pdf->AddPage();

    $logo = public_path(
        'images/escudo_republica.png'
    );

    if (is_file($logo)) {

        $pdf->Image(
            $logo,
            96,
            7,
            16,
            16
        );
    }

    $pdf->SetFont(
        'Arial',
        '',
        9
    );

    $pdf->SetXY(
        8,
        23
    );

    $pdf->Cell(
        192,
        4,
        $txt('MINISTERIO DE EDUCACIÓN'),
        0,
        1,
        'C'
    );

    $pdf->Cell(
        192,
        4,
        $txt('CIENCIA Y TECNOLOGÍA'),
        0,
        1,
        'C'
    );

    $pdf->SetFont(
        'Arial',
        'B',
        9
    );

    $pdf->Cell(
        192,
        4,
        $txt('DIRECCIÓN DE PLANIFICACIÓN'),
        0,
        1,
        'C'
    );

    $pdf->Cell(
        192,
        4,
        $txt(
            'FICHA DEL ESTUDIANTE – MATRÍCULA ' .
            $annLectivoPdf
        ),
        0,
        1,
        'C'
    );

    $y = 43;

    // ================================================================
    // DATOS INSTITUCIONALES
    // ================================================================
    $pdf->SetFont(
        'Arial',
        'B',
        6.5
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        31,
        4,
        $txt("CÓDIGO\nINFRAESTRUCTURA"),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        43,
        $y,
        25,
        5.2,
        $institucion->codigo_institucion ?? '10391',
        '',
        '',
        'C'
    );

    $pdf->SetXY(
        71,
        $y + 1
    );

    $pdf->Cell(
        30,
        4,
        $txt('CENTRO EDUCATIVO'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        100,
        $y,
        100,
        5.2,
        $institucion->nombre_institucion ??
        'COMPLEJO EDUCATIVO COLONIA RIO ZARCO'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // MATRÍCULA
    // ================================================================
    $y += 9;

    $pdf->SetFont(
        'Arial',
        'B',
        6.2
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        24,
        4,
        $txt('GRADO'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        32,
        $y,
        48,
        5.2,
        $matricula->grado_nombre ?? ''
    );

    $pdf->SetXY(
        84,
        $y + 1
    );

    $pdf->Cell(
        20,
        4,
        $txt('SECCIÓN'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        104,
        $y,
        20,
        5.2,
        $matricula->seccion_nombre ?? '',
        '',
        '',
        'C'
    );

    $pdf->SetXY(
        127,
        $y + 1
    );

    $pdf->Cell(
        22,
        4,
        $txt('JORNADA'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        149,
        $y,
        51,
        5.2,
        $matricula->turno_nombre ?? ''
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // DEPARTAMENTO / MUNICIPIO
    // ================================================================
    $y += 9;

    $pdf->SetFont(
        'Arial',
        'B',
        6.2
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        31,
        4,
        $txt('DEPARTAMENTO'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        39,
        $y,
        35,
        5.2,
        $departamento !== ''
            ? $departamento
            : 'SANTA ANA'
    );

    $pdf->SetXY(
        78,
        $y + 1
    );

    $pdf->Cell(
        23,
        4,
        $txt('MUNICIPIO'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        101,
        $y,
        99,
        5.2,
        $municipio !== ''
            ? $municipio
            : 'SANTA ANA'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // B. IDENTIFICACIÓN
    // ================================================================
    $y += 10;

    $pdf->SetFont(
        'Arial',
        'B',
        7.5
    );

    $pdf->SetXY(
        8,
        $y
    );
    // B. IDENTIFICACIÓN DEL ESTUDIANTE
        $seccionTitulo($pdf, $y, 'B. IDENTIFICACIÓN DEL ESTUDIANTE');

    $line(
        $pdf,
        $y + 5
    );

    // ================================================================
    // 1, 2, 2.5
    // ================================================================
    $y += 9;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        16,
        4,
        $txt('1. NIE'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        66,
        $y,
        28,
        5.3,
        $alumno->codigo_nie ??
        ($alumno->nie ?? '')
    );

    $pdf->SetXY(
        100,
        $y + 1
    );

    $pdf->Cell(
        16,
        4,
        $txt('2. DUI'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        116,
        $y,
        28,
        5.3,
        $alumno->dui ?? ''
    );

    $pdf->SetXY(
        149,
        $y + 1
    );

    $pdf->Cell(
        27,
        4,
        $txt('2.5 Pasaporte/Otro'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        177,
        $y,
        23,
        5.3,
        $alumno->pasaporte ?? ''
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // 3. NOMBRES
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 2
    );

    $pdf->Cell(
        25,
        4,
        $txt('3. Nombres'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        66,
        $y,
        35,
        5.3,
        $nombre1,
        '',
        'Primer'
    );

    $box(
        $pdf,
        107,
        $y,
        35,
        5.3,
        $nombre2,
        '',
        'Segundo'
    );

    $box(
        $pdf,
        147,
        $y,
        53,
        5.3,
        $nombre3,
        '',
        'Tercer'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // 4. APELLIDOS
    // ================================================================
    $y += 8;

    $pdf->SetXY(
        7,
        $y + 2
    );

    $pdf->Cell(
        25,
        4,
        $txt('4. Apellidos'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        66,
        $y,
        35,
        5.3,
        $apellido1,
        '',
        'Primer'
    );

    $box(
        $pdf,
        107,
        $y,
        35,
        5.3,
        $apellido2,
        '',
        'Segundo'
    );

    $box(
        $pdf,
        147,
        $y,
        53,
        5.3,
        $apellido3,
        '',
        'Tercer'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // 5. FECHA DE NACIMIENTO
    // ================================================================
    $y += 8;

    $pdf->SetXY(
        7,
        $y + 2
    );

    $pdf->Cell(
        38,
        4,
        $txt('5. Fecha de nacimiento'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        66,
        $y,
        16,
        5.3,
        $diaNac,
        '',
        'Día',
        'C'
    );

    $box(
        $pdf,
        87,
        $y,
        16,
        5.3,
        $mesNac,
        '',
        'Mes',
        'C'
    );

    $box(
        $pdf,
        108,
        $y,
        24,
        5.3,
        $anioNac,
        '',
        'Año',
        'C'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
// 6. NACIONALIDAD (Comparación por Gentilicio)
// ================================================================
$y += 8;

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(7, $y + 1);
$pdf->Cell(39, 4, $txt('6. Nacionalidad'), 0, 0, 'L');

// Fila 1
$radio($pdf, 66,  $y, $tiene($nac, 'SALVADOR'),   'SALVADOREÑA');
$radio($pdf, 102, $y, $tiene($nac, 'GUATEMAL'),   'GUATEMALTECA');
$radio($pdf, 138, $y, $tiene($nac, 'HONDUR'),     'HONDUREÑA');
$radio($pdf, 174, $y, $tiene($nac, 'NICARAG'),    'NICARAGÜENSE');

$y += 4.5;

// Fila 2
$radio($pdf, 66,  $y, $tiene($nac, 'COSTARRIC'), 'COSTARRICENSE');
$radio($pdf, 102, $y, $tiene($nac, 'PANAME'),     'PANAMEÑA');
$radio($pdf, 138, $y, $tiene($nac, 'BELICE'),     'BELICEÑA');
$radio($pdf, 174, $y, $tiene($nac, 'SURAMERIC'),  'SURAMERICANA');

$y += 4.5;

// Fila 3
$radio($pdf, 66,  $y, $tiene($nac, 'NORTEAMERIC'), 'NORTEAMERICANA');
$radio($pdf, 102, $y, $tiene($nac, 'CARIBE'),      'CARIBEÑA');
$radio($pdf, 138, $y, $tiene($nac, 'EUROPE'),      'EUROPEA');
$radio($pdf, 174, $y, $tiene($nac, 'ASIATIC'),     'ASIÁTICA');

$line($pdf, $y + 5.5);

    // ================================================================
    // 7. RETORNADO
    // ================================================================
    $y += 7;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        23,
        4,
        $txt('7. Retornado'),
        0,
        0,
        'L'
    );

    $retornado = $normalizar(
        $alumno->retornado ?? 'NO'
    );

    $radio(
        $pdf,
        66,
        $y,
        $retornado === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        80,
        $y,
        $retornado === 'NO',
        'NO'
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // 8, 9, 10
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        54,
        4,
        $txt(
            '8. ¿Posee partida de nacimiento?'
        ),
        0,
        0,
        'L'
    );

    $poseePartida = $normalizar(
        $alumno->posee_pn ??
        ($alumno->posee_partida ?? '')
    );

    $radio(
        $pdf,
        63,
        $y,
        $poseePartida === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        76,
        $y,
        $poseePartida === 'NO',
        'NO'
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        43,
        4,
        $txt(
            '9. ¿Presenta partida de nacimiento?'
        ),
        0,
        0,
        'L'
    );

    $presentaPartida = $normalizar(
        $alumno->presenta_pn ??
        ($alumno->presenta_partida ?? '')
    );

    $radio(
        $pdf,
        137,
        $y,
        $presentaPartida === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        151,
        $y,
        $presentaPartida === 'NO',
        'NO'
    );

    $pdf->SetXY(
        164,
        $y + 1
    );

    $pdf->Cell(
        17,
        4,
        $txt('10. Sexo'),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        181,
        $y,
        $esMujer,
        'MUJER'
    );

    $radio(
        $pdf,
        181,
        $y + 4,
        $esHombre,
        'HOMBRE'
    );

    $line(
        $pdf,
        $y + 10
    );

    // ================================================================
    // 11. ETNIA
    // ================================================================
    $y += 12;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        19,
        4,
        $txt('11. Etnia'),
        0,
        0,
        'L'
    );

    $et = $normalizar($etnia);

    $radio(
        $pdf,
        66,
        $y,
        $tiene($et, 'NO APLICA'),
        'NO APLICA'
    );

    $radio(
        $pdf,
        103,
        $y,
        $tiene($et, 'NAHUA'),
        'NAHUA-PIPIL'
    );

    $radio(
        $pdf,
        137,
        $y,
        $tiene($et, 'LENCA'),
        'LENCA'
    );

    $radio(
        $pdf,
        160,
        $y,
        $tiene($et, 'KAKAWIRA'),
        'KAKAWIRA'
    );

    $radio(
        $pdf,
        183,
        $y,
        $tiene($et, 'OTRO'),
        'OTRO'
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // 12. DISCAPACIDAD
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        54,
        4,
        $txt(
            '12. Condición de Discapacidad'
        ),
        0,
        0,
        'L'
    );

    $disc = $normalizar($discapacidad);

    $check(
        $pdf,
        66,
        $y,
        $tiene($disc, 'NO APLICA'),
        'NO APLICA'
    );

    $y += 4.5;

    $check(
        $pdf,
        66,
        $y,
        $tiene($disc, 'CEGUERA'),
        'CEGUERA'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($disc, 'BAJA VISION'),
        'BAJA VISIÓN (REMANENTE VISUAL NO FUNCIONAL)'
    );

    $y += 4.5;

    $check(
        $pdf,
        66,
        $y,
        $tiene($disc, 'SORDERA'),
        'SORDERA'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($disc, 'MULTIDISCAPACIDAD'),
        'MULTIDISCAPACIDAD Y RETOS MÚLTIPLES'
    );

    $y += 4.5;

    $check(
        $pdf,
        66,
        $y,
        $tiene($disc, 'SORDO-CEGUERA'),
        'SORDO-CEGUERA'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($disc, 'INTELECTUAL'),
        'DISCAPACIDAD INTELECTUAL'
    );

    $y += 4.5;

    $check(
        $pdf,
        66,
        $y,
        $tiene($disc, 'DOWN'),
        'SÍNDROME DE DOWN'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($disc, 'MOTORA'),
        'DISCAPACIDAD MOTORA'
    );

    $y += 4.5;

    $check(
        $pdf,
        66,
        $y,
        $tiene($disc, 'AUSENCIA'),
        'AUSENCIA DE MIEMBROS'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($disc, 'AUTISMO') ||
        $tiene($disc, 'ESPECTRO AUTISTA'),
        'TRASTORNO DEL ESPECTRO AUTISTA (AUTISMO, ASPERGER, REET)'
    );

    $y += 4.5;

    $check(
        $pdf,
        66,
        $y,
        $tiene($disc, 'HIPOACUSIA'),
        'HIPOACUSIA (AUDICIÓN BAJA)'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($disc, 'PSICOSOCIAL'),
        'PSICOSOCIAL (ESQUIZOFRENIA, DEPRESIÓN, BIPOLARIDAD)'
    );

    $pdf->SetFont(
        'Arial',
        '',
        5
    );

    $pdf->SetXY(
        66,
        $y + 3.5
    );

    $pdf->Cell(
        134,
        3,
        $txt('(Puedes marcar más de una opción)'),
        0,
        0,
        'L'
    );

    // ================================================================
    // 13. DIAGNÓSTICO
    // ================================================================
    $y += 8;

    $line(
        $pdf,
        $y
    );

    $y += 3;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        53,
        4,
        $txt(
            '13. ¿Posee diagnóstico clínico?'
        ),
        0,
        0,
        'L'
    );

    $diagAplica =
        $diagnostico === '' ||
        $tiene($diagnostico, 'NO APLICA');

    $radio(
        $pdf,
        66,
        $y,
        $diagAplica,
        'NO APLICA'
    );

    $radio(
        $pdf,
        101,
        $y,
        !$diagAplica &&
        $diagnostico !== '',
        'SÍ'
    );

    $radio(
        $pdf,
        116,
        $y,
        false,
        'NO'
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // 14. REFERENCIA
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        50,
        4,
        $txt(
            '14. El estudiante ha sido referido a'
        ),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        66,
        $y,
        true,
        'NO APLICA'
    );

    $radio(
        $pdf,
        101,
        $y,
        false,
        'DOCENTE DE APOYO A LA INCLUSIÓN',
        5.1
    );

    $radio(
        $pdf,
        151,
        $y,
        false,
        'CENTRO DE ORIENTACIÓN Y RECURSO (COR)',
        5.1
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // 15. SERVICIOS DE APOYO
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        48,
        4,
        $txt(
            '15. El estudiante recibe'
        ),
        0,
        0,
        'L'
    );

    $apoyo = $normalizar(
        $apoyoEducativo
    );

    $check(
        $pdf,
        66,
        $y,
        $tiene($apoyo, 'NO APLICA'),
        'NO APLICA'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($apoyo, 'LENGUAJE'),
        'TERAPIA DE LENGUAJE'
    );

    $check(
        $pdf,
        150,
        $y,
        $tiene($apoyo, 'AUDICION') ||
        $tiene($apoyo, 'AUDICIÓN'),
        'TERAPIA DE AUDICIÓN Y LENGUAJE',
        5.0
    );

    $y += 4.2;

    $check(
        $pdf,
        66,
        $y,
        $tiene($apoyo, 'REHABILITACION'),
        'TERAPIA DE REHABILITACIÓN'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($apoyo, 'FISIOTERAPIA'),
        'FISIOTERAPIA'
    );

    $check(
        $pdf,
        150,
        $y,
        $tiene($apoyo, 'PSICOLOG'),
        'ATENCIÓN PSICOLÓGICA'
    );

    $y += 4.2;

    $check(
        $pdf,
        66,
        $y,
        $tiene($apoyo, 'PSIQUIATR'),
        'ATENCIÓN PSIQUIÁTRICA'
    );

    $check(
        $pdf,
        106,
        $y,
        $tiene($apoyo, 'NEUROLOG'),
        'ATENCIÓN NEUROLÓGICA'
    );

    $check(
        $pdf,
        150,
        $y,
        $tiene($apoyo, 'OTRO'),
        'OTRO'
    );

    $pdf->SetFont(
        'Arial',
        '',
        5
    );

    $pdf->SetXY(
        66,
        $y + 3.5
    );

    $pdf->Cell(
        134,
        3,
        $txt('(Puedes marcar más de una opción)'),
        0,
        0,
        'L'
    );

    // ================================================================
    // 16, 17, 18
    // ================================================================
    $y += 8;

    $line(
        $pdf,
        $y
    );

    $y += 3;

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        45,
        4,
        $txt(
            '16. Correo electrónico de contacto'
        ),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        45,
        $y,
        42,
        5.3,
        $alumno->direccion_email ?? ''
    );

    $pdf->SetXY(
        93,
        $y + 1
    );

    $pdf->Cell(
        32,
        4,
        $txt(
            '17. Teléfono de contacto si lo posee'
        ),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        130,
        $y,
        24,
        5.3,
        $alumno->telefono_celular ?? ''
    );

    $pdf->SetXY(
        157,
        $y + 1
    );

    $pdf->Cell(
        24,
        4,
        $txt('18. ¿Tiene WhatsApp?'),
        0,
        0,
        'L'
    );

    $whatsapp = $normalizar(
        $alumno->whatsapp ?? ''
    );

    $radio(
        $pdf,
        182,
        $y,
        $whatsapp === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        182,
        $y + 4,
        $whatsapp === 'NO',
        'NO'
    );

    // ================================================================
    // 19. TIPO DE TRABAJO
    // ================================================================
    $y += 9;

    $line(
        $pdf,
        $y
    );

    $y += 3;

    $pdf->SetFont(
        'Arial',
        'B',
        8
    );

    $pdf->SetXY(
        7,
        $y + 1
    );

    $pdf->Cell(
        48,
        4,
        $txt('19. ¿Tipo de trabajo?'),
        0,
        0,
        'L'
    );

    $trabajo = $normalizar(
        $actividadEconomica
    );

    $trabajosIzq = [
        ['NO TRABAJA', 'NO TRABAJA'],
        ['CANA DE AZUCAR', 'CAÑA DE AZÚCAR'],
        ['PEPENADOR DE BASURA', 'PEPENADOR DE BASURA'],
        ['COHETERIA', 'COHETERÍA'],
        ['VENTAS', 'VENTAS (AMBULATORIAS POR MAYOR Y MENOR)'],
        ['TRABAJO AGRICOLA', 'TRABAJO AGRÍCOLA (DIFERENTE DEL CAFÉ Y CAÑA)'],
        ['AVES', 'AVES DE CORRAL U OTROS ANIMALES'],
        ['ALIMENTACION', 'ACTIVIDADES DE ALIMENTACIÓN (VER INSTRUCTIVO)'],
    ];

    $trabajosDer = [
        ['OTRAS ACTIVIDADES', 'OTRAS ACTIVIDADES (REMUNERADAS O NO)'],
        ['PESCA', 'PESCA'],
        ['TRABAJO DOMESTICO', 'TRABAJO DOMÉSTICO REMUNERADO'],
        ['CAFE', 'CAFÉ'],
        ['SERVICIOS', 'SERVICIOS (VER INSTRUCTIVO)'],
        ['GANADO', 'CRÍA DE GANADO'],
        ['CONSTRUCCION', 'CONSTRUCCIÓN'],
        ['MANUFACTURERAS', 'ACTIVIDADES MANUFACTURERAS'],
    ];

    $yy = $y;

    foreach ($trabajosIzq as $item) {

        $radio(
            $pdf,
            66,
            $yy,
            $tiene($trabajo, $item[0]),
            $item[1],
            5.1
        );

        $yy += 4.4;
    }

    $yy = $y;

    foreach ($trabajosDer as $item) {

        $radio(
            $pdf,
            132,
            $yy,
            $tiene($trabajo, $item[0]),
            $item[1],
            5.1
        );

        $yy += 4.4;
    }

    // ================================================================
    // PÁGINA 2
    // ================================================================
    $pdf->AddPage();

    $y = 9;

    // ================================================================
    // 20. ESTADO CIVIL
    // ================================================================
    $line(
        $pdf,
        $y
    );

    $y += 3;

    $pdf->SetFont(
        'Arial',
        'B',
        5.8
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        43,
        4,
        $txt('20. Estado familiar'),
        0,
        0,
        'L'
    );

    $ec = $normalizar(
        $estadoCivil
    );

    $radio(
        $pdf,
        52,
        $y,
        $tiene($ec, 'SOLTER'),
        'SOLTERO'
    );

    $radio(
        $pdf,
        76,
        $y,
        $tiene($ec, 'ACOMPA'),
        'ACOMPAÑADO'
    );

    $radio(
        $pdf,
        106,
        $y,
        $tiene($ec, 'CASAD'),
        'CASADO'
    );

    $radio(
        $pdf,
        132,
        $y,
        $tiene($ec, 'DIVORCI'),
        'DIVORCIADO'
    );

    $radio(
        $pdf,
        163,
        $y,
        $tiene($ec, 'VIUD'),
        'VIUDO'
    );

    $radio(
        $pdf,
        181,
        $y,
        $tiene($ec, 'NO APLICA'),
        'NO APLICA'
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // 21. CONVIVENCIA FAMILIAR
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        5.8
    );

    $pdf->SetXY(
        8,
        $y + 4
    );

    $pdf->Cell(
        45,
        4,
        $txt('21. Convivencia familiar'),
        0,
        0,
        'L'
    );

    $conv = $normalizar(
        $estadoFamiliar
    );

    $convRows = [
        ['VIVE SOLO CON LA MADRE', 52, 0],
        ['VIVE SOLO CON EL PADRE', 94, 0],
        ['VIVE CON MADRE Y PADRE', 139, 0],

        ['VIVE CON FAMILIARES', 52, 4.4],
        ['NO VIVE CON FAMILIARES', 94, 4.4],
        ['VIVE CON MADRE Y PADRASTRO', 139, 4.4],

        ['VIVE CON PADRE Y MADRASTRA', 52, 8.8],
        ['VIVE SOLO', 94, 8.8],
        ['VIVE SOLO CON SU CÓNYUGE', 139, 8.8],

        ['VIVE CON SU CÓNYUGE E HIJOS', 52, 13.2],
        ['VIVE SOLO CON SUS HIJOS', 94, 13.2],
    ];

    foreach ($convRows as $r) {

        $radio(
            $pdf,
            $r[1],
            $y + $r[2],
            $tiene($conv, $r[0]),
            $r[0],
            5.1
        );
    }

    $line(
        $pdf,
        $y + 20
    );

    // ================================================================
    // 22
    // ================================================================
    $y += 22;

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        53,
        4,
        $txt(
            '22. ¿Está embarazada la estudiante?'
        ),
        0,
        0,
        'L'
    );

    $embarazada = $normalizar(
        $alumno->embarazada ?? ''
    );

    $radio(
        $pdf,
        63,
        $y,
        $embarazada === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        77,
        $y,
        $embarazada === 'NO',
        'NO'
    );

    $radio(
        $pdf,
        92,
        $y,
        $embarazada === 'NO APLICA',
        'NO APLICA'
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // 23 Y 24
    // ================================================================
    $y += 8;

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        53,
        4,
        $txt(
            '23. ¿El estudiante tiene hijos o hijas?'
        ),
        0,
        0,
        'L'
    );

    $hijos = $normalizar(
        $alumno->tiene_hijos ?? ''
    );

    $radio(
        $pdf,
        63,
        $y,
        $hijos === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        77,
        $y,
        $hijos === 'NO',
        'NO'
    );

    $pdf->SetXY(
        101,
        $y + 1
    );

    $pdf->Cell(
        46,
        4,
        $txt(
            '24. Cantidad de hijos del o de la estudiante'
        ),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        169,
        $y,
        20,
        5.3,
        $alumno->cantidad_hijos ?? '0',
        '',
        '',
        'C'
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // C. RESIDENCIA
    // ================================================================

    
    $y = $section(
        $pdf,
        $y + 8,
        'C. RESIDENCIA'
    );

    $zona = $normalizar(
        $zonaResidencia
    );

    $vivienda = $normalizar(
        $tipoVivienda
    );

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        29,
        4,
        $txt('25. Zona'),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        37,
        $y+1,
        $tiene($zona, 'URBANA'),
        'URBANA'
    );

    $radio(
        $pdf,
        67,
        $y+1,
        $tiene($zona, 'RURAL'),
        'RURAL'
    );

    $pdf->SetXY(
        93,
        $y + 1
    );

    $pdf->Cell(
        42,
        4,
        $txt(
            '26. ¿Tipo de vivienda del estudiante?'
        ),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        137,
        $y+1,
        $tiene($vivienda, 'MIXTA'),
        'MIXTA'
    );

    $radio(
        $pdf,
        157,
        $y+1,
        $tiene($vivienda, 'ADOBE'),
        'ADOBE'
    );

    $radio(
        $pdf,
        177,
        $y+1,
        $tiene($vivienda, 'BAHAREQUE'),
        'BAHAREQUE'
    );

    $radio(
        $pdf,
        195,
        $y+1,
        $tiene($vivienda, 'LAMINA'),
        'LÁMINA'
    );

    $y += 7;

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        27,
        4,
        $txt('27. Departamento'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        36,
        $y,
        32,
        5.3,
        $departamento
    );

    $pdf->SetXY(
        70,
        $y + 1
    );

    $pdf->Cell(
        29,
        4,
        $txt('28. Municipio'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        99,
        $y,
        101,
        5.3,
        $municipio
    );

    $y += 7;

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        27,
        4,
        $txt('29. Cantón'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        36,
        $y,
        48,
        5.3,
        $canton
    );

    $pdf->SetXY(
        87,
        $y + 1
    );

    $pdf->Cell(
        24,
        4,
        $txt('30. Caserío'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        111,
        $y,
        89,
        5.3,
        $alumno->caserio ?? ''
    );

    $y += 7;

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        27,
        4,
        $txt('31. Dirección'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        36,
        $y,
        164,
        5.3,
        $alumno->direccion_alumno ?? ''
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // D. SERVICIOS BÁSICOS
    // ================================================================
    $y = $section(
        $pdf,
        $y + 8,
        'D. SERVICIOS BÁSICOS'
    );

    $energia = $normalizar(
        $alumno->servicio_energia ?? ''
    );

    $basura = $normalizar(
        $alumno->recoleccion_basura ?? ''
    );

    $pdf->SetFont(
        'Arial',
        'B',
        5.6
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        72,
        4,
        $txt(
            '32. ¿Cuenta con servicio de energía eléctrica en su casa?'
        ),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        81,
        $y+1,
        $energia === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        95,
        $y+1,
        $energia === 'NO',
        'NO'
    );

    $pdf->SetXY(
        107,
        $y + 1
    );

    $pdf->Cell(
        66,
        4,
        $txt(
            '33. ¿Cuenta con servicio de recolección de basura?'
        ),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        176,
        $y+1,
        $basura === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        190,
        $y+1,
        $basura === 'NO',
        'NO'
    );

    $line(
        $pdf,
        $y + 6
    );

    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        5.5
    );

    $pdf->SetXY(
        8,
        $y - 2
    );

    $pdf->MultiCell(
        40,
        3.2,
        $txt(
            '34. ¿Cuál es la fuente principal de abastecimiento de agua de su casa?'
        ),
        0,
        'L'
    );

$y = $pdf->GetY() - 5;

    $agua = $normalizar(
        $abastecimiento
    );

    $radio(
        $pdf,
        66,
        $y,
        $tiene($agua, 'ACARREO'),
        'ACARREO (RÍO, LAGO, NACIMIENTO DE AGUA, CHORRO PÚBLICO CANTARERA)',
        4.8
    );

    $radio(
        $pdf,
        170,
        $y,
        $tiene($agua, 'PIPA'),
        'PIPA',
        4.8
    );

    $radio(
        $pdf,
        66,
        $y + 5,
        $tiene($agua, 'CAÑERIA INTERNA') ||
        $tiene($agua, 'SERVICIO DE AGUA'),
        'SERVICIO DE AGUA POR CAÑERÍA INTERNA A LA CASA',
        4.8
    );

    $radio(
        $pdf,
        170,
        $y + 5,
        $tiene($agua, 'POZO'),
        'POZO',
        4.8
    );

    $radio(
        $pdf,
        66,
        $y + 10,
        $tiene($agua, 'LLUVIA'),
        'AGUA LLUVIA',
        4.8
    );

    //$line(        $pdf,        $y + 17    );

    // ================================================================
    // E. SERVICIOS DE COMUNICACIÓN
    // ================================================================
    $y = $section(
        $pdf,
        $y + 8,
        'E. SERVICIOS DE COMUNICACIÓN'
    );

    $internet = $normalizar(
        $alumno->acceso_internet ?? ''
    );

    $conexion = $normalizar(
        $alumno->tipo_conexion_internet ?? ''
    );

    $radioCasa = $normalizar(
        $alumno->posee_radio ?? ''
    );

    $tv = $normalizar(
        $alumno->posee_tv ?? ''
    );

    $canal10 = $normalizar(
        $alumno->sintoniza_canal_10 ?? ''
    );

    $computadora = $normalizar(
        $alumno->posee_computadora ?? ''
    );

    $pdf->SetFont(
        'Arial',
        'B',
        5.5
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        38,
        4,
        $txt('35. Acceso a Internet'),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        40,
        $y + 1,
        $internet === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        54,
        $y+ 1,
        $internet === 'NO',
        'NO'
    );

    $pdf->SetXY(
        76,
        $y + 1
    );

    $pdf->Cell(
        53,
        4,
        $txt(
            '36. ¿Tiene algún tipo de conexión a Internet residencial?'
        ),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        131,
        $y +1,
        $conexion !== '' &&
        $conexion !== 'NO',
        'SÍ'
    );

    $radio(
        $pdf,
        145,
        $y + 1,
        $conexion === 'NO',
        'NO'
    );

    $pdf->SetXY(
        159,
        $y + 1
    );

    $pdf->Cell(
        28,
        4,
        $txt('37. ¿Posee radio?'),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        187,
        $y,
        $radioCasa === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        187,
        $y + 4,
        $radioCasa === 'NO',
        'NO'
    );

    $line(
        $pdf,
        $y + 10
    );

    // ================================================================
    // 38, 39, 40, 
    // ================================================================
    $y += 12;

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        30,
        4,
        $txt('38. ¿Posee T.V.?'),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        42,
        $y +1,
        $tv === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        56,
        $y +1,
        $tv === 'NO',
        'NO'
    );

    $pdf->SetXY(
        73,
        $y + 1
    );

    $pdf->Cell(
        51,
        4,
        $txt('39. ¿Sintoniza canal 10?'),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        100,
        $y + 1,
        $canal10 === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        114,
        $y +1,
        $canal10 === 'NO',
        'NO'
    );

    $radio(
        $pdf,
        128,
        $y+ 1,
        $canal10 === 'NO APLICA',
        'NO APLICA'
    );

    $pdf->SetXY(
        164,
        $y + 1
    );

    $pdf->Cell(
        26,
        4,
        $txt('40. ¿Posee computadora?'),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        191,
        $y,
        $computadora === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        191,
        $y + 4,
        $computadora === 'NO',
        'NO'
    );

    $line(
        $pdf,
        $y + 10
    );

    // ================================================================
    // 41. MODALIDAD
    // ================================================================
    $y += 12;

    $pdf->SetFont(
        'Arial',
        'B',
        5.5
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        74,
        4,
        $txt(
            '41. El estudiante ha recibido sus clases bajo la siguiente modalidad'
        ),
        0,
        0,
        'L'
    );

    $modalidad = $normalizar(
        $catalogo(
            'catalogo_clase_bajo_modalidad',
            $alumno->codigo_clases_bajo_modalidad ?? null
        )
    );

    $radio(
        $pdf,
        83,
        $y,
        $tiene($modalidad, 'PRESENCIAL'),
        'PRESENCIAL'
    );

    $radio(
        $pdf,
        117,
        $y,
        $tiene($modalidad, 'SEMIPRESENCIAL'),
        'SEMIPRESENCIAL'
    );

    $radio(
        $pdf,
        159,
        $y,
        $tiene($modalidad, 'VIRTUAL'),
        'VIRTUAL (DESDE CASA)'
    );

    $line(
        $pdf,
        $y + 6
    );


  
// ================================================================
// 42. CANALES DE ATENCIÓN
// ================================================================

$y += 8;

$pdf->SetFont(
    'Arial',
    'B',
    5.3
);

$pdf->SetXY(
    8,
    $y + 5
);

$pdf->MultiCell(
    50,        // Ancho disponible
    3.2,        // Interlineado
    $txt(
        '42. El estudiante ha recibido sus clases de acuerdo a los siguientes canales de atención'
    ),
    0,          // Sin borde
    'L'         // Alineación izquierda
);

// Actualizar $y para que lo que venga después
// no se monte sobre el texto
$y = $pdf->GetY() - 10;

    $canalAtencion = $normalizar(
        $catalogo(
            'catalogo_clases_canales_atencion',
            $alumno->codigo_clases_canales_atencion ?? null
        )
    );

    $canalesIzq = [
        [
            'IMPRESOS',
            'IMPRESOS - GUÍAS DE APRENDIZAJE'
        ],
        [
            'TELECLASES',
            'TELECLASES DE LA FRANJA "APRENDAMOS EN CASA"; TELEVISIÓN DE EL SALVADOR'
        ],
        [
            'RADIO',
            'RADIO CLASES "APRENDAMOS EN CASA CON LA RADIO"'
        ],
        [
            'REDES',
            'REDES SOCIALES (WHATSAPP, FACEBOOK, YOUTUBE)'
        ],
    ];

    $canalesDer = [
        [
            'LIBRO',
            'IMPRESOS-LIBRO DE TEXTO'
        ],
        [
            'CORREO',
            'CORREO ELECTRÓNICO'
        ],
        [
            'GOOGLE',
            'GOOGLE CLASSROOM'
        ],
        [
            'OTRAS',
            'OTRAS PLATAFORMAS'
        ],
        [
            'LINEA',
            'EDUCACIÓN EN LÍNEA - GOOGLE SITES.'
        ],
    ];

    $yy = $y;

    foreach ($canalesIzq as $item) {

        $check(
            $pdf,
            66,
            $yy,
            $tiene($canalAtencion, $item[0]),
            $item[1],
            4.7
        );

        $yy += 4.4;
    }

    $yy = $y;

    foreach ($canalesDer as $item) {

        $check(
            $pdf,
            145,
            $yy,
            $tiene($canalAtencion, $item[0]),
            $item[1],
            4.7
        );

        $yy += 4.4;
    }

    $pdf->SetFont(
        'Arial',
        '',
        5
    );

    $pdf->SetXY(
        66,
        $y + 19
    );

    $pdf->Cell(
        134,
        3,
        $txt('(Puedes marcar más de una opción)'),
        0,
        0,
        'L'
    );

    // ================================================================
    // F. SERVICIO SOCIAL
    // ================================================================
    $y = $section(
        $pdf,
        $y + 23,
        'F. SERVICIO SOCIAL (Solo Educación Media)'
    );

    $servSocial = $normalizar(
        $alumno->servicio_social_realizado ?? ''
    );

    $pdf->SetFont(
        'Arial',
        'B',
        5.6
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        65,
        4,
        $txt(
            '43. ¿Ha realizado las horas de servicio social?'
        ),
        0,
        0,
        'L'
    );

    $radio(
        $pdf,
        76,
        $y,
        $servSocial === 'SI',
        'SÍ'
    );

    $radio(
        $pdf,
        90,
        $y,
        $servSocial === 'NO',
        'NO'
    );

    $pdf->SetXY(
        108,
        $y + 1
    );

    $pdf->Cell(
        40,
        4,
        $txt(
            '44. Fecha finalización del servicio social'
        ),
        0,
        0,
        'L'
    );

    $fechaSS =
        $alumno->servicio_social_fecha_finalizado ?? '';

    $dSS = '';
    $mSS = '';
    $aSS = '';

    if ($fechaSS) {

        $tsSS = strtotime($fechaSS);

        if ($tsSS !== false) {

            $dSS = date('d', $tsSS);
            $mSS = date('m', $tsSS);
            $aSS = date('Y', $tsSS);
        }
    }

    $box(
        $pdf,
        149,
        $y,
        16,
        5.3,
        $dSS,
        '',
        'Día',
        'C'
    );

    $box(
        $pdf,
        169,
        $y,
        16,
        5.3,
        $mSS,
        '',
        'Mes',
        'C'
    );

    $box(
        $pdf,
        189,
        $y,
        11,
        5.3,
        $aSS,
        '',
        'Año',
        'C'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // 45
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        50,
        4,
        $txt('45. Cantidad de horas'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        58,
        $y,
        24,
        5.3,
        $alumno->servicio_social_horas ?? '',
        '',
        '',
        'C'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // 46
    // ================================================================
    $y += 8;

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        30,
        4,
        $txt('46. Descripción'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        38,
        $y,
        162,
        5.3,
        $alumno->servicio_social_descripcion ?? ''
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // G. DATOS DEL RESPONSABLE
    // ================================================================
    $y = $section(
        $pdf,
        $y + 8,
        'G. DATOS DEL RESPONSABLE'
    );

    // ================================================================
    // 47
    // ================================================================
    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        20,
        4,
        $txt('47. DUI'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        28,
        $y,
        34,
        5.3,
        $encargado->dui ?? ''
    );

    $pdf->SetXY(
        126,
        $y + 1
    );

    $pdf->Cell(
        28,
        4,
        $txt('47.5 Pasaporte/Otro'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        154,
        $y,
        46,
        5.3,
        $encargado->pasaporte_otro ?? ''
    );

    $line(
        $pdf,
        $y + 6.5
    );

    // ================================================================
    // 48. PARENTESCO
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        27,
        4,
        $txt('48. Tipo parentesco'),
        0,
        0,
        'L'
    );

    $par = $normalizar(
        $parentesco
    );

    $radio(
        $pdf,
        36,
        $y,
        $tiene($par, 'PADRE'),
        'PADRE'
    );

    $radio(
        $pdf,
        56,
        $y,
        $tiene($par, 'MADRE'),
        'MADRE'
    );

    $radio(
        $pdf,
        78,
        $y,
        $tiene($par, 'HERMANO'),
        'HERMANO/A'
    );

    $radio(
        $pdf,
        104,
        $y,
        $tiene($par, 'TIO'),
        'TÍO/A'
    );

    $radio(
        $pdf,
        126,
        $y,
        $tiene($par, 'ABUELO'),
        'ABUELO/A'
    );

    $radio(
        $pdf,
        153,
        $y,
        $tiene($par, 'HIJO'),
        'HIJO/A'
    );

    $y += 4.5;

    $radio(
        $pdf,
        36,
        $y,
        $tiene($par, 'PRIMO'),
        'PRIMO/A'
    );

    $radio(
        $pdf,
        56,
        $y,
        $tiene($par, 'SOBRINO'),
        'SOBRINO/A'
    );

    $radio(
        $pdf,
        84,
        $y,
        $tiene($par, 'CONYUGE') ||
        $tiene($par, 'CÓNYUGE'),
        'CÓNYUGE'
    );

    $radio(
        $pdf,
        114,
        $y,
        $tiene($par, 'PADRASTRO'),
        'PADRASTRO'
    );

    $radio(
        $pdf,
        146,
        $y,
        $tiene($par, 'MADRASTRA'),
        'MADRASTRA'
    );

    $line(
        $pdf,
        $y + 5
    );

    // ================================================================
    // 49. NOMBRES RESPONSABLE
    // ================================================================
    $y += 8;

    $nomResp = trim(
        (string) ($encargado->nombres ?? '')
    );

    $rn1 = '';
    $rn2 = '';
    $rn3 = '';

    if ($nomResp !== '') {

        $pp = preg_split(
            '/\s+/',
            $nomResp
        );

        $rn1 = $pp[0] ?? '';
        $rn2 = $pp[1] ?? '';

        $rn3 = count($pp) > 2
            ? implode(
                ' ',
                array_slice($pp, 2)
            )
            : '';
    }

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 2
    );

    $pdf->Cell(
        27,
        4,
        $txt(
            '49. Nombres responsable'
        ),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        53,
        $y,
        38,
        5.3,
        $rn1,
        '',
        'Primer'
    );

    $box(
        $pdf,
        101,
        $y,
        38,
        5.3,
        $rn2,
        '',
        'Segundo'
    );

    $box(
        $pdf,
        149,
        $y,
        51,
        5.3,
        $rn3,
        '',
        'Tercer'
    );

   // $line(        $pdf,        $y + 7);

    // ================================================================
    // 50. APELLIDOS RESPONSABLE
    // ================================================================
    $y += 8;

    $apResp = trim(
        (string) ($encargado->apellidos ?? '')
    );

    $ra1 = '';
    $ra2 = '';
    $ra3 = '';

    if ($apResp !== '') {

        $pp = preg_split(
            '/\s+/',
            $apResp
        );

        $ra1 = $pp[0] ?? '';
        $ra2 = $pp[1] ?? '';

        $ra3 = count($pp) > 2
            ? implode(
                ' ',
                array_slice($pp, 2)
            )
            : '';
    }

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 2
    );

    $pdf->Cell(
        27,
        4,
        $txt(
            '50. Apellidos responsable'
        ),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        53,
        $y,
        38,
        5.3,
        $ra1,
        '',
        'Primer'
    );

    $box(
        $pdf,
        101,
        $y,
        38,
        5.3,
        $ra2,
        '',
        'Segundo'
    );

    $box(
        $pdf,
        149,
        $y,
        51,
        5.3,
        $ra3,
        '',
        'Tercer'
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // 51, 52, 53
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        5.7
    );

    $pdf->SetXY(
        8,
        $y + 2
    );

    $pdf->Cell(
        21,
        4,
        $txt('51. Teléfono'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        29,
        $y,
        23,
        5.3,
        $encargado->telefono ?? ''
    );

    $pdf->SetXY(
        58,
        $y + 2
    );

    $pdf->Cell(
        31,
        4,
        $txt('52. Teléfono alternativo'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        89,
        $y,
        23,
        5.3,
        $encargado->telefono_alternativo ?? ''
    );

    $pdf->SetXY(
        118,
        $y + 2
    );

    $pdf->Cell(
        29,
        4,
        $txt('53. Correo electrónico'),
        0,
        0,
        'L'
    );

    $box(
        $pdf,
        147,
        $y,
        53,
        5.3,
        $encargado->correo_electronico ?? ''
    );

    $line(
        $pdf,
        $y + 7
    );

    // ================================================================
    // 54. ESCOLARIDAD DEL RESPONSABLE
    // ================================================================
    $y += 8;

    $pdf->SetFont(
        'Arial',
        'B',
        5.5
    );

    $pdf->SetXY(
        8,
        $y + 1
    );

    $pdf->Cell(
        53,
        4,
        $txt(
            '54. Último grado de Escolaridad Aprobado'
        ),
        0,
        0,
        'L'
    );

    $esc = $normalizar(
        $escolaridadResponsable
    );

    $radio(
        $pdf,
        66,
        $y,
        $tiene($esc, 'NINGUNA'),
        'NINGUNA',
        5.2
    );

    $radio(
        $pdf,
        94,
        $y,
        $tiene($esc, 'INICIAL'),
        'INICIAL',
        5.2
    );

    $radio(
        $pdf,
        118,
        $y,
        $tiene($esc, 'PARVULARIA'),
        'PARVULARIA',
        5.2
    );

    $radio(
        $pdf,
        151,
        $y,
        $tiene($esc, 'BASICA CICLO I') ||
        $tiene($esc, 'CICLO I'),
        'BÁSICA CICLO I (1°, 2° Y 3°)',
        4.9
    );

    $y += 4.5;

    $radio(
        $pdf,
        66,
        $y,
        $tiene($esc, 'BASICA CICLO II') ||
        $tiene($esc, 'CICLO II'),
        'BÁSICA CICLO II (4°, 5° Y 6°)',
        4.9
    );

    $radio(
        $pdf,
        119,
        $y,
        $tiene($esc, 'BASICA CICLO III') ||
        $tiene($esc, 'CICLO III'),
        'BÁSICA CICLO III (7°, 8° Y 9°)',
        4.9
    );

    $radio(
        $pdf,
        166,
        $y,
        $tiene($esc, 'MEDIA'),
        'MEDIA',
        5.2
    );

    $radio(
        $pdf,
        185,
        $y,
        $tiene($esc, 'SUPERIOR'),
        'SUPERIOR',
        5.2
    );

    $line(
        $pdf,
        $y + 6
    );

    // ================================================================
    // SALIDA
    // ================================================================
    return response(
        $pdf->Output(
            'S',
            'Ficha_Estudiante_Matricula_' .
            $annLectivoPdf .
            '.pdf'
        )
    )
    ->header(
        'Content-Type',
        'application/pdf'
    )
    ->header(
        'Content-Disposition',
        'inline; filename="Ficha_Estudiante_Matricula_' .
        $annLectivoPdf .
        '.pdf"'
    );
}
   
private function drawPreguntaLarga(
    $pdf,
    &$y,
    $numero,
    $texto,
    $respuesta,
    $opciones = ['SI', 'NO']
) {
    // Texto de la pregunta
    $pdf->SetFont('Arial', 'B', 6);

    $textoCompleto = utf8_decode($numero . '. ' . $texto);

    $pdf->SetXY(8, $y);

    $pdf->MultiCell(
        192,
        3.2,
        $textoCompleto,
        0,
        'L'
    );

    // Nueva posición después del texto
    $y = $pdf->GetY() + 1;

    // Respuestas
    $pdf->SetFont('Arial', '', 6);

    $x = 60;

    foreach ($opciones as $opcion) {

        $this->drawRadio(
            $pdf,
            $x,
            $y,
            $respuesta == $opcion,
            $opcion == 'SI' ? 'SÍ' : ($opcion == 'NO' ? 'NO' : $opcion)
        );

        $x += 25;
    }

    // Espacio antes del siguiente numeral
    $y += 5;
}


}


