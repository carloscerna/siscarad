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
        // 1. OBTENCIÓN Y PREPARACIÓN DE DATOS
        $alumno = DB::table('alumno')->where('id_alumno', $id)->first();
        $institucion = DB::table('informacion_institucion')->first();
        $annLectivoActual = date('y');

        // Consulta de Matrícula (Grado, Sección y Jornada/Turno)
        $matricula = DB::table('alumno_matricula as mat')
            ->leftJoin('grado_ano as gra', DB::raw('TRIM(mat.codigo_grado)'), '=', DB::raw('TRIM(gra.codigo)'))
            ->leftJoin('seccion as sec', DB::raw('TRIM(mat.codigo_seccion)'), '=', DB::raw('TRIM(sec.codigo)'))
            ->leftJoin('turno as tur', DB::raw('TRIM(mat.codigo_turno)'), '=', DB::raw('TRIM(tur.codigo)'))
            ->where('mat.codigo_alumno', $id)
            ->select('gra.nombre as grado_nombre', 'sec.nombre as seccion_nombre', 'tur.nombre as turno_nombre')
            ->first();

        // Procesamiento de Fecha de Nacimiento
        $diaNac = ''; $mesNac = ''; $anioNac = '';
        if (!empty($alumno->fecha_nacimiento)) {
            $fechaComp = strtotime($alumno->fecha_nacimiento);
            $diaNac = date('d', $fechaComp);
            $mesNac = date('m', $fechaComp);
            $anioNac = date('Y', $fechaComp);
        }

        // Normalización de Nacionalidad
        $nacionalidad = mb_strtoupper($alumno->nacionalidad ?? 'SALVADOREÑA', 'UTF-8');

        // 2. CONSTRUCCIÓN DEL PDF EN FPDF
        $pdf = new Fpdf('P', 'mm', 'Letter');
        $pdf->SetMargins(8, 6, 8);
        $pdf->AddPage();

        // Encabezado
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell(0, 3, utf8_decode('MINISTERIO DE EDUCACIÓN'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('CIENCIA Y TECNOLOGÍA'), 0, 1, 'C');
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 3, utf8_decode('DIRECCIÓN DE PLANIFICACIÓN'), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode('FICHA DEL ESTUDIANTE – MATRÍCULA 2024'), 0, 1, 'C');
        $pdf->Ln(2);

        // Bloque Infraestructura / Ubicación
        $y = $pdf->GetY();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(8, $y);
        $pdf->Cell(32, 4, utf8_decode("CÓDIGO\nINFRAESTRUCTURA"), 0, 0, 'L');
        $pdf->Rect(35, $y, 25, 4.5);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetXY(35, $y);
        $pdf->Cell(25, 4.5, utf8_decode($institucion->codigo_institucion ?? '10391'), 0, 0, 'C');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(63, $y + 0.5);
        $pdf->Cell(28, 4, utf8_decode("CENTRO EDUCATIVO"), 0, 0, 'L');
        $pdf->Rect(90, $y, 110, 4.5);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetXY(91, $y);
        $pdf->Cell(108, 4.5, utf8_decode($institucion->nombre_institucion ?? 'COMPLEJO EDUCATIVO COLONIA RIO ZARCO'), 0, 0, 'L');

        // Grado, Sección y Jornada (Llenado automático)
        $y += 5.5;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(8, $y); $pdf->Cell(15, 4, utf8_decode("GRADO"), 0, 0, 'L');
        $pdf->Rect(22, $y, 50, 4);
        $pdf->SetFont('Arial', '', 6.5); $pdf->SetXY(23, $y); $pdf->Cell(48, 4, utf8_decode($matricula->grado_nombre ?? ''), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $y); $pdf->Cell(15, 4, utf8_decode("SECCIÓN"), 0, 0, 'L');
        $pdf->Rect(90, $y, 20, 4);
        $pdf->SetFont('Arial', '', 6.5); $pdf->SetXY(91, $y); $pdf->Cell(18, 4, utf8_decode($matricula->seccion_nombre ?? ''), 0, 0, 'C');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(113, $y); $pdf->Cell(15, 4, utf8_decode("JORNADA"), 0, 0, 'L');
        $pdf->Rect(128, $y, 72, 4);
        $pdf->SetFont('Arial', '', 6.5); $pdf->SetXY(129, $y); $pdf->Cell(70, 4, utf8_decode($matricula->turno_nombre ?? ''), 0, 0, 'L');

        // Departamento y Municipio
        $y += 5;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(8, $y); $pdf->Cell(22, 4, utf8_decode("DEPARTAMENTO"), 0, 0, 'L');
        $pdf->Rect(31, $y, 35, 4);
        $pdf->SetFont('Arial', '', 6.5); $pdf->SetXY(32, $y); $pdf->Cell(33, 4, utf8_decode('SANTA ANA'), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(70, $y); $pdf->Cell(18, 4, utf8_decode("MUNICIPIO"), 0, 0, 'L');
        $pdf->Rect(88, $y, 112, 4);
        $pdf->SetFont('Arial', '', 6.5); $pdf->SetXY(89, $y); $pdf->Cell(110, 4, utf8_decode('SANTA ANA'), 0, 0, 'L');

        // B. IDENTIFICACIÓN DEL ESTUDIANTE
        $y += 6;
        $pdf->SetFont('Arial', 'B', 6.5);
        $pdf->SetXY(8, $y);
        $pdf->Cell(192, 3.5, utf8_decode('B. IDENTIFICACIÓN DEL ESTUDIANTE'), 'B', 1, 'C');

        // NIE, DUI, Pasaporte
        $y += 4.5;
        $this->drawInputBox($pdf, 60, $y, 35, 4, '1. NIE', $alumno->nie ?? '');
        $this->drawInputBox($pdf, 105, $y, 35, 4, '2. DUI', $alumno->dui ?? '');
        $this->drawInputBox($pdf, 150, $y, 50, 4, '2.5 Pasaporte/Otro', $alumno->pasaporte ?? '');

        // Nombres separados
        $y += 8;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(40, $y + 2); $pdf->Cell(20, 4, utf8_decode('3. Nombres'), 0, 0, 'L');
        $this->drawInputBox($pdf, 60, $y, 40, 4, '', $alumno->nombre_1 ?? '', 'Primer');
        $this->drawInputBox($pdf, 105, $y, 40, 4, '', $alumno->nombre_2 ?? '', 'Segundo');
        $this->drawInputBox($pdf, 150, $y, 50, 4, '', $alumno->nombre_3 ?? '', 'Tercer');

        // Apellidos separados
        $y += 8;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(40, $y + 2); $pdf->Cell(20, 4, utf8_decode('4. Apellidos'), 0, 0, 'L');
        $this->drawInputBox($pdf, 60, $y, 40, 4, '', $alumno->apellido_1 ?? '', 'Primer');
        $this->drawInputBox($pdf, 105, $y, 40, 4, '', $alumno->apellido_2 ?? '', 'Segundo');
        $this->drawInputBox($pdf, 150, $y, 50, 4, '', $alumno->apellido_3 ?? '', 'Tercer');

        // Fecha de Nacimiento separada (Día, Mes, Año)
        $y += 8;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(25, $y + 1); $pdf->Cell(35, 4, utf8_decode('5. Fecha de nacimiento'), 0, 0, 'L');
        $this->drawInputBox($pdf, 60, $y, 15, 4, '', $diaNac, 'Día');
        $this->drawInputBox($pdf, 80, $y, 25, 4, '', $mesNac, 'Mes');
        $this->drawInputBox($pdf, 110, $y, 20, 4, '', $anioNac, 'Año');

        // Nacionalidad
        $y += 8;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(35, $y); $pdf->Cell(25, 4, utf8_decode('6. Nacionalidad'), 0, 0, 'L');
        
        $this->drawRadio($pdf, 60, $y, $nacionalidad == 'SALVADOREÑA', 'SALVADOREÑA');
        $this->drawRadio($pdf, 100, $y, $nacionalidad == 'GUATEMALTECA', 'GUATEMALTECA');
        $this->drawRadio($pdf, 135, $y, $nacionalidad == 'HONDUREÑA', 'HONDUREÑA');
        $this->drawRadio($pdf, 168, $y, $nacionalidad == 'NICARAGÜENSE', 'NICARAGÜENSE');

        $y += 4;
        $this->drawRadio($pdf, 60, $y, $nacionalidad == 'COSTARRICENSE', 'COSTARRICENSE');
        $this->drawRadio($pdf, 100, $y, $nacionalidad == 'PANAMEÑA', 'PANAMEÑA');
        $this->drawRadio($pdf, 135, $y, $nacionalidad == 'BELICEÑA', 'BELICEÑA');
        $this->drawRadio($pdf, 168, $y, $nacionalidad == 'SURAMERICANA', 'SURAMERICANA');

        $y += 4;
        $this->drawRadio($pdf, 60, $y, $nacionalidad == 'NORTEAMERICANA', 'NORTEAMERICANA');
        $this->drawRadio($pdf, 100, $y, $nacionalidad == 'CARIBEÑA', 'CARIBEÑA');
        $this->drawRadio($pdf, 135, $y, $nacionalidad == 'EUROPEA', 'EUROPEA');
        $this->drawRadio($pdf, 168, $y, $nacionalidad == 'ASIÁTICA', 'ASIÁTICA');
        // Separador
        $y += 5;
        $pdf->Line(8, $y, 200, $y);

        // 7, 8, 9, 10. Preguntas cortas
        $y += 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(40, $y); $pdf->Cell(20, 4, utf8_decode('7. Retornado'), 0, 0, 'L');
        $this->drawRadio($pdf, 60, $y, ($alumno->retornado ?? 'NO') == 'SI', 'SÍ');
        $this->drawRadio($pdf, 72, $y, ($alumno->retornado ?? 'NO') == 'NO', 'NO');

        $y += 5;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(8, $y); $pdf->Cell(50, 4, utf8_decode('8. ¿Posee partida de nacimiento?'), 0, 0, 'L');
        $this->drawRadio($pdf, 60, $y, ($alumno->posee_partida ?? 'SI') == 'SI', 'SÍ');
        $this->drawRadio($pdf, 72, $y, ($alumno->posee_partida ?? 'SI') == 'NO', 'NO');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(88, $y); $pdf->Cell(35, 4, utf8_decode('9. ¿Presenta partida de nacimiento?'), 0, 0, 'L');
        $this->drawRadio($pdf, 122, $y, ($alumno->presenta_partida ?? 'SI') == 'SI', 'SÍ');
        $this->drawRadio($pdf, 132, $y, ($alumno->presenta_partida ?? 'SI') == 'NO', 'NO');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(145, $y); $pdf->Cell(15, 4, utf8_decode('10. Sexo'), 0, 0, 'L');
        $this->drawRadio($pdf, 160, $y, ($alumno->sexo ?? 'F') == 'F', 'MUJER');
        $this->drawRadio($pdf, 180, $y, ($alumno->sexo ?? 'F') == 'M', 'HOMBRE');

        // 11. Etnia
        $y += 5;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(45, $y); $pdf->Cell(15, 4, utf8_decode('11. Etnia'), 0, 0, 'L');
        $etnia = $alumno->etnia ?? 'NO APLICA';
        $this->drawRadio($pdf, 60, $y, $etnia == 'NO APLICA', 'NO APLICA');
        $this->drawRadio($pdf, 85, $y, $etnia == 'NAHUA-PIPIL', 'NAHUA-PIPIL');
        $this->drawRadio($pdf, 118, $y, $etnia == 'LENCA', 'LENCA');
        $this->drawRadio($pdf, 138, $y, $etnia == 'KAKAWIRA', 'KAKAWIRA');
        $this->drawRadio($pdf, 168, $y, $etnia == 'OTRO', 'OTRO');

        $y += 5;
        $pdf->Line(8, $y, 200, $y);

        // 12. Condición de Discapacidad
        $y += 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(8, $y); $pdf->Cell(50, 4, utf8_decode('12. Condición de Discapacidad'), 0, 0, 'L');
        
        $disc = $alumno->discapacidad ?? 'NO APLICA';
        $this->drawCheckbox($pdf, 60, $y, $disc == 'NO APLICA', 'NO APLICA');
        
        $y += 4;
        $this->drawCheckbox($pdf, 60, $y, $disc == 'CEGUERA', 'CEGUERA');
        $this->drawCheckbox($pdf, 105, $y, $disc == 'BAJA VISION', 'BAJA VISIÓN (REMANENTE VISUAL NO FUNCIONAL)');

        $y += 4;
        $this->drawCheckbox($pdf, 60, $y, $disc == 'SORDERA', 'SORDERA');
        $this->drawCheckbox($pdf, 105, $y, $disc == 'MULTIDISCAPACIDAD', 'MULTIDISCAPACIDAD Y RETOS MÚLTIPLES');

        $y += 4;
        $this->drawCheckbox($pdf, 60, $y, $disc == 'SORDO-CEGUERA', 'SORDO-CEGUERA');
        $this->drawCheckbox($pdf, 105, $y, $disc == 'INTELECTUAL', 'DISCAPACIDAD INTELECTUAL');

        $y += 4;
        $this->drawCheckbox($pdf, 60, $y, $disc == 'DOWN', 'SÍNDROME DE DOWN');
        $this->drawCheckbox($pdf, 105, $y, $disc == 'MOTORA', 'DISCAPACIDAD MOTORA');

        $y += 4;
        $this->drawCheckbox($pdf, 60, $y, $disc == 'AUSENCIA DE MIEMBROS', 'AUSENCIA DE MIEMBROS');
        $this->drawCheckbox($pdf, 105, $y, $disc == 'AUTISMO', 'TRASTORNO DEL ESPECTRO AUTISTA (AUTISMO, ASPERGER, REET)');

        $y += 4;
        $this->drawCheckbox($pdf, 60, $y, $disc == 'HIPOACUSIA', 'HIPOACUSIA (AUDICIÓN BAJA)');
        $this->drawCheckbox($pdf, 105, $y, $disc == 'PSICOSOCIAL', 'PSICOSOCIAL (ESQUIZOFRENIA, DEPRESIÓN, BIPOLARIDAD)');

        return response($pdf->Output('I', 'Ficha_Bloque1.pdf'))
            ->header('Content-Type', 'application/pdf');
    }


}