@extends('layouts.app')

@php
use Illuminate\Support\Facades\Auth;
    $correo_docente = Auth::user()->email;                                        
    $nombre_docente = Auth::user()->name;
    $codigo_personal = Auth::user()->codigo_personal;   
    $codigo_institucion = Auth::user()->codigo_institucion;                                                
@endphp

@section('content')
<section class="section">
    <div class="section-header">
        <h5 class="page__heading">{{$nombre_docente}} - Gestión de Calificaciones</h5>
    </div>
</section>

<div class="section-body">
    <div class="row">
        <div class="col-lg-12">
            
            {{-- PANEL DE FILTROS --}}
            <div class="card shadow-lg">
                <div class="card-header border-secondary font-weight-bold">
                    <i class="fas fa-filter"></i> Panel de Selección
                </div>
                <div class="card-body">
                    <form id="formFiltros" class="row">
            {{-- CAMPOS OCULTOS --}}
            <input type="hidden" id="codigo_personal" value="{{ $codigo_personal }}">
            <input type="hidden" id="codigo_institucion" value="{{ $codigo_institucion }}">

                        {{-- Filtro: Año Lectivo --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="codigo_ann_lectivo">Año Lectivo</label>
                                <select id="codigo_ann_lectivo" name="codigo_ann_lectivo" class="form-control select2" required>
                                    <option value="">Seleccione año...</option>
                                    @if(isset($ann_lectivo))
                                        @foreach($ann_lectivo as $ann)
                                            <option value="{{ $ann->codigo }}">{{ $ann->nombre }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        {{-- Filtro: Sección --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="codigo_seccion">Sección / Grado</label>
                                <select id="codigo_seccion" name="codigo_seccion" class="form-control select2" required>
                                    <option value="">Seleccione sección...</option>
                                </select>
                            </div>
                        </div>

                        {{-- Filtro: Asignatura --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="codigo_asignatura">Asignatura</label>
                                <select id="codigo_asignatura" name="codigo_asignatura" class="form-control select2" required>
                                    <option value="">Seleccione asignatura...</option>
                                </select>
                            </div>
                        </div>

                        {{-- Filtro: Periodo --}}
                        <div class="col-md-3" id="contenedor-filtro-periodo">
                            <div class="form-group">
                                <label for="periodo">Periodo</label>
                                <select id="periodo" name="periodo" class="form-control" required>
                                    <option value="1">Periodo 1</option>
                                    <option value="2">Periodo 2</option>
                                    <option value="3">Periodo 3</option>
                                    <option value="4">Periodo 4</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12 text-right">
                            <button type="button" class="btn btn-primary" onclick="buscarEstudiantes()">
                                <i class="fas fa-search"></i> Cargar Listado
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLA DE NOTAS --}}
            <div class="card shadow-lg mt-4" id="contenedorNotas" style="display: none;">
                <div class="card-header bg-primary text-white font-weight-bold">
                    <i class="fas fa-edit"></i> Ingreso de Notas: <span id="infoPeriodo"></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                      <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="button" id="btnVerSeleccionados" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> Ver Boletas
                                </button>
                                <button type="button" id="btnDescargarSeleccionados" class="btn btn-outline-secondary">
                                    <i class="fas fa-download"></i> Descargar Boletas
                                </button>
                                <button type="button" id="btnEnviarCorreos" class="btn btn-outline-success">
                                    <i class="fas fa-envelope"></i> Enviar por Correo
                                </button>
                                <button type="button" id="btnReporteAsignatura" class="btn btn-info">
                                    <i class="fas fa-book"></i> Informe por Asignatura
                                </button>
                            </div>
                            <span class="text-muted ml-2" id="textoSeleccionados">(0 seleccionados)</span>
                        </div>
                    </div>

                        <table id="tabla_estudiantes" class="table table-bordered table-hover">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="checkTodos" title="Seleccionar/Deseleccionar Todos">
                                    <th width="10%">NIE</th>
                                    <th>Nombre del Estudiante</th>
                                    <th width="12%">Actividad 1</th>
                                    <th width="12%">Actividad 2</th>
                                    <th width="12%">PO</th>
                                    <th width="12%">Recuperación</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaEstudiantes"></tbody>
                        </table>
                    </div>
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-success btn-lg" onclick="guardarTodasLasNotas()" id="btnGuardarNotas">
                            <i class="fas fa-save"></i> Guardar Todo el Periodo
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="pantalla-bloqueo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.55); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; color: white;">
    <div class="text-center p-4" style="background: #ffffff; color: #333333; border-radius: 8px; box-shadow: 0px 4px 15px rgba(0,0,0,0.3); min-width: 280px;">
        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-info"></i>
        <h5 class="font-weight-bold mb-1">Guardando Calificaciones</h5>
        <p class="text-muted mb-0 font-size-sm">Procesando registros en el servidor, por favor no cierre la página...</p>
    </div>
</div>
@endsection


@section('css')
<style>
    .nota-reprobada {
        color: #e74c3c !important; /* Rojo intenso */
        font-weight: bold;
    }
    .nota-input.reprobada {
        border-color: #e74c3c;
        background-color: #fdf2f2;
    }
</style>
@endsection

@section('scripts')
<script>
// 1. Definimos la ruta base para las boletas (sin el ID y la ACCION al final)
    const urlBaseBoleta = "{{ url('boleta/pdf') }}"; 
    const urlBaseAsignatura = "{{ url('/pdfRPA') }}";
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

$(document).on('input', '.input-a1, .input-a2, .input-a3, .input-r', function() {
let input = $(this);
    let valor = parseFloat(input.val());

    // --- BLOQUEO DE RANGO (0 a 10) ---
    if (valor > 10) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'La nota máxima es 10',
            showConfirmButton: false,
            timer: 2000
        });
        input.val(10); // Le forzamos el valor máximo
        valor = 10;
    } else if (valor < 0) {
        input.val(0); // No permitimos notas negativas
        valor = 0;
    }
    // ---------------------------------

    let fila = $(this).closest('tr');
    let fullCodigo = $('#codigo_seccion').val();
    let modalidad = fullCodigo.substring(6,8);
    
    // Determinamos la nota mínima según tu lógica de grados/modalidades
    // Grados 02-09 (Básica) -> 5.0 | Media y otros -> 6.0
    let notaMinima = (modalidad >= '17' && modalidad <= '19') ? 5.0 : 6.0;

    let a1 = parseFloat(fila.find('.input-a1').val()) || 0;
    let a2 = parseFloat(fila.find('.input-a2').val()) || 0;
    let a3 = parseFloat(fila.find('.input-a3').val()) || 0;
    let r  = parseFloat(fila.find('.input-r').val()) || 0;

    // Lógica de recuperación (reemplaza la menor entre A1 y A2)
    let a1_calc = a1, a2_calc = a2;
    if (r > 0) {
        if (a1 < a2) { a1_calc = (r > a1) ? r : a1; } 
        else { a2_calc = (r > a2) ? r : a2; }
    }

    let promedio = (a1_calc * 0.35) + (a2_calc * 0.35) + (a3 * 0.30);
    let spanPromedio = fila.find('.label-promedio');
    
    spanPromedio.text(promedio.toFixed(1));

    // APLICAR COLOR ROJO SI REPROBÓ
    if (promedio < notaMinima) {
        spanPromedio.addClass('nota-reprobada');
    } else {
        spanPromedio.removeClass('nota-reprobada');
    }

    // Opcional: Pintar el input individual si es menor a la mínima
    $(this).toggleClass('reprobada', parseFloat($(this).val()) < notaMinima);
});

    $(document).ready(function() {
// Función para marcar/desmarcar todos
$(document).on('change', '#checkTodos', function() {
    let checked = $(this).prop('checked');
    $('.check-estudiante').prop('checked', checked);
    actualizarContador();
});

// Función para actualizar el contador al marcar uno individual
$(document).on('change', '.check-estudiante', function() {
    actualizarContador();
    
    // Si desmarcas uno, desmarcamos el "Todos" por coherencia
    if(!$(this).prop('checked')) {
        $('#checkTodos').prop('checked', false);
    }
});

function actualizarContador() {
    let seleccionados = $('.check-estudiante:checked').length;
    $('#textoSeleccionados').text(`(${seleccionados} seleccionados)`);
}

    // Escuchamos el cambio en cualquiera de los selects de filtro
    $('#codigo_ann_lectivo, #codigo_seccion, #codigo_asignatura, #periodo').on('change', function() {
    // 1. Ocultamos el contenedor de la tabla con un efecto suave
    $('#contenedorNotas').hide();
    
    // 2. Vaciamos el cuerpo de la tabla
    $('#cuerpoTablaEstudiantes').html('');
    
    // 3. Opcional: Puedes poner un mensaje sutil si lo prefieres
    // $('#cuerpoTablaEstudiantes').html('<tr><td colspan="7" class="text-center text-muted">Realice una nueva búsqueda</td></tr>');
});
        
//
// CAMBIOS EN LOS DIFERENTES CONTROLES.
//
// Función reutilizable para evaluar si es Modular
    function evaluarSiEsModular() {
        let codigoAsignaturaCompleto = $('#codigo_asignatura').val() || '';
        let fullCodigoSeccion = $('#codigo_seccion').val() || '';

        // 1. Extraemos el codigo_cc (Últimos 2 caracteres de la asignatura)
        // Si el largo es 8, saca las posiciones 6 y 7. Si es otro largo, siempre los últimos 2.
        let codigo_cc = codigoAsignaturaCompleto.slice(-2); 

        // 2. Extraemos la modalidad (Posiciones 6 y 7 de la sección completa)
        let codigo_modalidad = fullCodigoSeccion.substring(6, 8); 

        // Condición: Si el concepto es '04' y la modalidad es '15'
        if (codigo_cc === '04' && codigo_modalidad === '15') {
            // Ocultamos el contenedor del periodo y forzamos valor '1' para evitar campos vacíos
            $('#contenedor-filtro-periodo').fadeOut(250);
            $('#periodo').val('1');
            return true;
        } else {
            // Si no se cumple, se muestra el periodo normalmente
            $('#contenedor-filtro-periodo').fadeIn(250);
            $('#periodo').prop('disabled', false); // <--- ESTO CORRIGE EL BLOQUEO
            return false;
        }
    }

    // Escuchamos el cambio en ambos selectores para reaccionar de inmediato
    $('#codigo_asignatura, #codigo_seccion').on('change', function() {
        evaluarSiEsModular();
    });


            // Al cambiar AÑO -> Cargar SECCIONES
            $('#codigo_ann_lectivo').on('change', function() {
                let ann = $(this).val();
                if(!ann) return;
                
                $.get("{{ url('get-secciones') }}", { codigo_ann: ann }, function(data) {
                    let h = '<option value="">Seleccione sección...</option>';
                    $.each(data, function(i, o) { 
                        h += `<option value="${o.codigo}">${o.nombre}</option>`; 
                    });
                    $('#codigo_seccion').html(h);
                    $('#codigo_asignatura').html('<option value="">Seleccione asignatura...</option>');
                });
            });

            // Al cambiar SECCIÓN -> Cargar ASIGNATURAS (enviando también el año)
            $('#codigo_seccion').on('change', function() {
                let sec = $(this).val();
                let ann = $('#codigo_ann_lectivo').val();
                if(!sec) return;

                $.get("{{ url('get-asignaturas') }}", { codigo_seccion: sec, codigo_ann: ann }, function(data) {
                    let h = '<option value="">Seleccione asignatura...</option>';
                    $.each(data, function(i, o) { 
                        // Usamos .trim() para eliminar cualquier espacio invisible
                        let codAsig = String(o.codigo).trim();
                        let codArea = String(o.codigo_area || "00").trim();
                        let codCC = String(o.codigo_cc || "00").trim();

                        h += `<option value="${codAsig}${codArea}${codCC}">${o.nombre}</option>`;
                    });
                    $('#codigo_asignatura').html(h);
                });
            });
    });

// BUSCAR ESTUDIANTES, SI ES MODULAR O NO.
function buscarEstudiantes() {
    let fullCodigo = $('#codigo_seccion').val(); 
    
    if(!fullCodigo || !$('#codigo_asignatura').val()){
        Swal.fire('Atención', 'Seleccione todos los filtros antes de buscar.', 'warning');
        return;
    }

    // --- 1. EFECTO VISUAL DE CARGA ---
    let btnBuscar = $('#btnCargarListado'); 
    $('#tabla_estudiantes tbody').empty();

    btnBuscar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
    
    $('#cuerpoTablaEstudiantes').html('<tr><td colspan="9" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Obteniendo listado de estudiantes, por favor espere...</td></tr>');
    $('#contenedorNotas').fadeIn();

    let params = {
        codigo_asignatura: $('#codigo_asignatura').val(),
        periodo: $('#periodo').val(),
        codigo_grado:      fullCodigo.substring(0,2),
        codigo_seccion:    fullCodigo.substring(2,4),
        codigo_turno:      fullCodigo.substring(4,6),
        codigo_modalidad:  fullCodigo.substring(6,8),
        codigo_ann_lectivo: fullCodigo.substring(8,10)
    };

    $.get("{{ url('calificaciones/buscar-estudiantes') }}", params, function(res) {
        if(res.status === 'locked') {
            Swal.fire('Periodo Cerrado', res.message, 'error');
            $('#contenedorNotas').hide();
            return;
        }

        // =========================================================================
        // NUEVO: CONTROL VISUAL DE ENCABEZADOS SEGÚN EL TIPO DE ASIGNATURA
        // =========================================================================
        let thHeaders = $('#tabla_estudiantes thead th'); // Capturamos las cabeceras de tu tabla
        
        if (res.es_modular) {
            // Deshabilitamos el periodo porque es único anual
            $('#periodo').prop('disabled', true);

            // Mutamos los encabezados de las columnas (th)
            thHeaders.eq(3).text('Calificación Módulo (1.0 - 5.0)').css({'background-color': '#d1ecf1', 'color': '#0c5460'});
            thHeaders.eq(4).text('N/A').css({'background-color': '#f8f9fa', 'color': '#6c757d'});
            thHeaders.eq(5).text('N/A').css({'background-color': '#f8f9fa', 'color': '#6c757d'});
            thHeaders.eq(6).text('N/A').css({'background-color': '#f8f9fa', 'color': '#6c757d'});
            thHeaders.eq(7).text('Nota Única Anual');
        } else {
            // Restauramos a modo tradicional si cambia a otra materia básica
            $('#periodo').prop('disabled', false);

            thHeaders.eq(3).text('Actividad 1 (35%)').css({'background-color': '', 'color': ''});
            thHeaders.eq(4).text('Actividad 2 (35%)').css({'background-color': '', 'color': ''});
            thHeaders.eq(5).text('Prueba Obj. (30%)').css({'background-color': '', 'color': ''});
            thHeaders.eq(6).text('Nota Recuperación').css({'background-color': '', 'color': ''});
            thHeaders.eq(7).text('Nota Período');
        }
        // =========================================================================

        let filas = '';
        $.each(res.estudiantes, function(i, e) {
            let promedioActual = parseFloat(e.nota_p) || 0;
            let minima = (params.codigo_modalidad >= '17' && params.codigo_modalidad <= '19') ? 5.0 : 6.0;
            let claseRoja = (promedioActual < minima) ? 'nota-reprobada' : '';

            // El formato final será: /boleta/pdf/ID/ver
            let urlVer = `${urlBaseBoleta}/${e.codigo_matricula}/ver`;
            let urlDescargar = `${urlBaseBoleta}/${e.codigo_matricula}/descargar`;

            const urlBaseAsignatura = "{{ url('/pdfRPA') }}";
            const codigo_institucion = "{{ Auth::user()->codigo_institucion }}"; 

            // =========================================================================
            // NUEVO: RENDERIZADO CONDICIONAL DE FILAS (MODULAR VS NORMAL)
            // =========================================================================
            if (res.es_modular) {
                // Modo Modular: Un único campo activo (Mapeado con el valor de nota_final que viene en e.nota_p)
                let notaModular = e.nota_p ? parseFloat(e.nota_p).toFixed(1) : '';

                filas += `
                    <tr class="fila-estudiante" data-alumno="${e.codigo_alumno}" data-matricula="${e.codigo_matricula}">
                        <td class="text-center align-middle">
                            <input type="checkbox" class="check-estudiante" value="${e.codigo_matricula}">
                        </td>
                        <td class="text-center align-middle">${e.codigo_nie}</td>
                        <td class="align-middle">${e.nombre_completo}</td>
                        

                        <td>
                            <input type="number" step="0.1" min="1.0" max="5.0" 
                                class="form-control text-center nota-input input-modular" 
                                style="font-weight: bold; border: 2px solid #17a2b8; background-color: #f7feff;"
                                value="${notaModular}"
                                onkeypress="return sliderSoloNumeros(event)"
                                oninput="validarRangoTeclado(this)">  
                                onblur="validarVacioAlSalir(this)">  </td>
                        </td>
                        
                        <td class="text-center align-middle text-muted bg-light"><i class="fas fa-ban"></i></td>
                        <td class="text-center align-middle text-muted bg-light"><i class="fas fa-ban"></i></td>
                        <td class="text-center align-middle text-muted bg-light"><i class="fas fa-ban"></i></td>
                        
                        <td class="text-center align-middle font-weight-bold bg-light">
                            <span class="label-promedio">${notaModular || '0.0'}</span>
                        </td>
                    </tr>`;
            } else {
                // Modo Tradicional (Básicas y Técnicas normales que ya tenías)
                filas += `
                    <tr class="fila-estudiante" data-alumno="${e.codigo_alumno}" data-matricula="${e.codigo_matricula}">
                        <td class="text-center align-middle">
                            <input type="checkbox" class="check-estudiante" value="${e.codigo_matricula}">
                        </td>
                        <td class="text-center align-middle">${e.codigo_nie}</td>
                        <td class="align-middle">${e.nombre_completo}</td>
                        <td><input type="number" step="0.1" min="0" max="10" class="form-control text-center nota-input input-a1" value="${e.nota_a1 || ''}"></td>
                        <td><input type="number" step="0.1" min="0" max="10" class="form-control text-center nota-input input-a2" value="${e.nota_a2 || ''}"></td>
                        <td><input type="number" step="0.1" min="0" max="10" class="form-control text-center nota-input input-a3" value="${e.nota_a3 || ''}"></td>
                        <td><input type="number" step="0.1" min="0" max="10" class="form-control text-center nota-input input-r" value="${e.nota_r || ''}"></td>
                        <td class="text-center align-middle font-weight-bold bg-light">
                            <span class="label-promedio ${claseRoja}">${promedioActual.toFixed(1)}</span>
                        </td>
                    </tr>`;
            }
            // =========================================================================
        });

        $('#cuerpoTablaEstudiantes').html(filas || '<tr><td colspan="8" class="text-center">No hay registros</td></tr>');
        
    }).fail(function() {
        Swal.fire('Error', 'No se pudo realizar la búsqueda.', 'error');
    }).always(function() {
        btnBuscar.prop('disabled', false).html('<i class="fas fa-users"></i> Cargar Listado');
    });
}
 

// GUARDAR TODAS LAS NOTAS.

   function guardarTodasLasNotas() {
    let notasData = [];
    let fullCodigo = $('#codigo_seccion').val();
    
    // 1. EFECTO DEL BOTÓN (Se mantiene intacto tu efecto actual)
    $('#btnGuardarNotas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    // 2. NUEVO: ATENUAR LA PANTALLA COMPLETA
    // Usamos flex porque el HTML de arriba usa flexbox para centrar el cuadro blanco
    $('#pantalla-bloqueo').css('display', 'flex').hide().fadeIn(200);

    $('.fila-estudiante').each(function() {
        let fila = $(this);
        
        // =========================================================================
        // CAPTURA INTELIGENTE SEGÚN EL MODO (MODULAR O NORMAL)
        // =========================================================================
        let inputMod = fila.find('.input-modular');
        
        if (inputMod.length > 0) {
            let valorNota = inputMod.val();
                
                // Si la casilla está vacía (length == 0), le asignamos 1.0 de forma automática
                if (!valorNota || valorNota.trim() === '') {
                    valorNota = "1.0";
                    inputMod.val("1.0"); // Lo pintamos en la tabla para que el docente lo vea
                }

                notasData.push({
                    codigo_matricula: fila.data('matricula'),
                    nota_modular: valorNota  // Ahora viaja seguro como 1.0 mínimo
                });
        } else {
            // Modo tradicional: Se envían las actividades normales
            notasData.push({
                codigo_matricula: fila.data('matricula'),
                nota_a1: fila.find('.input-a1').val() || 0,
                nota_a2: fila.find('.input-a2').val() || 0,
                nota_a3: fila.find('.input-a3').val() || 0,
                nota_r:  fila.find('.input-r').val() || 0
            });
        }
        // =========================================================================
    });

    let payload = {
        _token: '{{ csrf_token() }}',
        periodo: $('#periodo').val(),
        codigo_asignatura: $('#codigo_asignatura').val(),
        codigo_modalidad: fullCodigo.substring(6,8),
        notas: notasData
    };

    $.ajax({
        type: "POST",
        url: "{{ url('calificaciones/guardar-todas') }}",
        data: payload, 
        dataType: 'json',
        success: function(res) {
            Swal.fire('¡Éxito!', res.message, 'success');
            // Opcional: Recargar el listado para reflejar los promedios en la etiqueta gris
            buscarEstudiantes();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error del servidor:", jqXHR.responseText);
            Swal.fire('Error', 'No se pudieron guardar las notas. Revisa la consola (F12) para más detalles.', 'error');
        },
        complete: function() {
           // 3. NUEVO: QUITAR EL ATENUADOR DE PANTALLA SUAVEMENTE
            $('#pantalla-bloqueo').fadeOut(200);

            // RESTAURAR EL BOTÓN (Se mantiene tu flujo original en el complete)
            $('#btnGuardarNotas').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Todo el Periodo');
        }
    });
}

// Escuchador para validación de colores en tiempo real
$(document).on('input', '.form-control', function() {
    let fila = $(this).closest('tr');
    let mod = $('#codigo_seccion').val().substring(6,8);
    
    // Definir mínima según tus reglas: 17, 18, 19 (Básica) -> 5.0 | Resto -> 6.0
    let minima = (mod >= '17' && mod <= '19') ? 5.0 : 6.0;
    
    // Obtenemos el promedio que calculamos en el paso anterior
    let promedio = parseFloat(fila.find('.label-promedio').text()) || 0;

    if (promedio < minima) {
        fila.find('.label-promedio').css('color', 'red');
    } else {
        fila.find('.label-promedio').css('color', 'black');
    }
});

        // Listeners para los nuevos botones
        $(document).ready(function() {
            $('#btnVerSeleccionados').on('click', function() {
                AccionMasivaBoletas('ver');
            });

            $('#btnDescargarSeleccionados').on('click', function() {
                AccionMasivaBoletas('descargar');
            });
            // ===== AÑADIR ESTE LISTENER =====
            $('#btnEnviarCorreos').on('click', function() {
                ConfirmarEnvioCorreos();
            });
        });

/*
function AccionMasivaBoletas(accion) {
    var urls = [];
    var dataAttr = (accion === 'ver') ? 'data-url-ver' : 'data-url-descargar';

    $('.check-estudiante:checked').each(function() {
        urls.push($(this).attr(dataAttr));
    });

    if (urls.length === 0) {
        Swal.fire('Atención', 'Seleccione al menos un estudiante.', 'info');
        return;
    }

    Swal.fire({
        title: '¿Procesar ' + urls.length + ' boletas?',
        text: "Su navegador podría solicitar permisos para múltiples descargas.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Usamos un intervalo para que no se abran todas al mismo milisegundo
            urls.forEach(function(url, index) {
                setTimeout(function() {
                    window.open(url, '_blank');
                }, index * 800); // 800ms de retraso entre cada una
            });
            
            toastr.success('Iniciando descargas procesadas...');
        }
    });
}
*/

function AccionMasivaBoletas(accion) {
    let matriculas = [];
    let tipoAccion = (accion === 'ver') ? 'Ver' : 'Descargar';
    
    // 1. Recolectar solo los códigos de matrícula marcados
    $('.check-estudiante:checked').each(function() {
        matriculas.push($(this).val());
    });

    // 2. Validar si seleccionó alguno
    if (matriculas.length === 0) {
        Swal.fire('Ningún Estudiante', 'Por favor, seleccione al menos un estudiante de la lista.', 'info');
        return;
    }

    // 3. ¡Mostrar el SweetAlert!
    Swal.fire({
        title: '¿' + tipoAccion + ' ' + matriculas.length + ' Boleta(s)?',
        text: "Se procesarán las boletas de los estudiantes seleccionados en un solo archivo. ¿Desea continuar?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            toastr.info('Generando archivo... Por favor espere.', 'Sistema');

            // 4. Creamos una URL limpia enviando el array de matrículas como parámetro
            // Convertimos el array [12, 15, 18] en una cadena "12,15,18"
            let matriculasStr = matriculas.join(',');
            
            // Reemplaza 'reportes/boleta-masiva' por la ruta real que uses para el PDF
            let urlFinal = `{{ url('reportes/boleta-masiva') }}?matriculas=${matriculasStr}&accion=${accion}&periodo=${$('#periodo').val()}`;

            // Abrimos UNA SOLA pestaña con el PDF completo
            window.open(urlFinal, '_blank');
        }; // 800ms de retraso entre cada una
    });
}

$(document).ready(function() {
    $('#btnReporteAsignatura').on('click', function() {
        ReportePorAsignatura();
    });
});

function ReportePorAsignatura() {
    // 1. Obtener valores de los selects actuales
    var codigo_gradoseccionturno = $("#codigo_seccion").val();
    var codigo_annlectivo = $('#codigo_ann_lectivo').val();
    var codigo_asignatura_area = $("#codigo_asignatura").val();
    var codigo_personal = $('#codigo_personal').val();
    var codigo_institucion = $('#codigo_institucion').val();

    // Validar que se haya seleccionado una asignatura
    if (!codigo_asignatura_area) {
        Swal.fire('Atención', 'Por favor seleccione una asignatura primero.', 'warning');
        return;
    }

    // 2. Lógica de substrings (tu lógica original)
    var codigo_asignatura = "";
    var codigo_area = "";
    var conteo = codigo_asignatura_area.length;

    if(conteo == 4){
        codigo_asignatura = codigo_asignatura_area.substring(0,2);
        codigo_area = codigo_asignatura_area.substring(2,4);
    } else if(conteo == 6){
        codigo_asignatura = codigo_asignatura_area.substring(0,4);
        codigo_area = codigo_asignatura_area.substring(4,6);
    } else {
        codigo_asignatura = codigo_asignatura_area.substring(0,3);
        codigo_area = codigo_asignatura_area.substring(3,5);
    }

    // Convertimos a String y luego aplicamos trim para evitar errores
    var ann_trim = String(codigo_annlectivo).trim();
    var inst_trim = String(codigo_institucion).trim();

    // 3. Armar el ID compuesto que espera el controlador
    // Formato: GGSSTT-ANN-INST-ASIG-AREA-PERS
    var datos_estudiantes = codigo_gradoseccionturno + "-" + 
                            ann_trim + "-" + 
                            inst_trim + "-" + 
                            codigo_asignatura + "-" + 
                            codigo_area + "-" + 
                            codigo_personal;

    // 4. Construir URL y abrir
    var url = urlBaseAsignatura + "/" + datos_estudiantes;
    window.open(url, '_blank');
}


function validarRangoTeclado(input) {
    // Si está vacío porque el docente está borrando para corregir, lo dejamos pasar provisionalmente
    if (input.value === '') return;

    let valor = parseFloat(input.value);

    // 1. Si supera el límite máximo (5.0), lo corregimos automáticamente a 5.0
    if (valor > 5.0) {
        input.value = "5.0";
        alertarBordeRojo(input);
        return;
    }

    // 2. CORREGIDO: Si es menor a 1.0 (ya sea 0, un decimal como 0.5 o cualquier número NEGATIVO)
    if (valor < 1.0) {
        input.value = "1.0";
        alertarBordeRojo(input);
        return;
    }
}

// Función auxiliar para el efecto visual del parpadeo
function alertarBordeRojo(input) {
    $(input).css('border-color', '#dc3545');
    setTimeout(() => { 
        $(input).css('border-color', '#17a2b8'); 
    }, 500);
}


function sliderSoloNumeros(e) {
    var key = e.keyCode || e.which;
    var teclado = String.fromCharCode(key);
    
    // Permitir números del 0 al 9 y el punto decimal (.)
    var permitidos = "0123456789.";
    
    // Permitir teclas de control especiales (Retroceso, Tabulador, Flechas si es necesario)
    var especiales = [8, 9, 37, 39, 46]; // 8: Backspace, 9: Tab
    
    var tecla_especial = false;
    for (var i in especiales) {
        if (key == especiales[i]) {
            tecla_especial = true;
            break;
        }
    }
    
    // Si no está en los permitidos ni es especial, bloquea la pulsación (evita letras y el signo menos)
    if (permitidos.indexOf(teclado) == -1 && !tecla_especial) {
        return false;
    }
}

function validarVacioAlSalir(input) {
    // Si el docente cambió de celda y dejó el espacio totalmente en blanco
    if (input.value === '' || parseFloat(input.value) < 1.0) {
        input.value = "1.0"; // Forzamos el mínimo requerido por el script de PostgreSQL
        alertarBordeRojo(input);
    }
}


</script>
@endsection