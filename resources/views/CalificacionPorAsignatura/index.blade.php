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
                        <div class="col-md-3">
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
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th width="10%">NIE</th>
                                    <th>Nombre del Estudiante</th>
                                    <th width="12%">Actividad 1</th>
                                    <th width="12%">Actividad 2</th>
                                    <th width="12%">Actividad 3</th>
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
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });



$(document).on('input', '.input-a1, .input-a2, .input-a3, .input-r', function() {
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
                        h += `<option value="${o.codigo}">${o.nombre}</option>`; 
                    });
                    $('#codigo_asignatura').html(h);
                });
            });
    });

    function buscarEstudiantes() {
   let v = $('#codigo_seccion').val(); // Ejemplo: "0902021726" (Noveno, Sec 02, Turno 02, Mod 17, Año 26)

    let params = {
        codigo_asignatura: $('#codigo_asignatura').val(),
        periodo: $('#periodo').val(),
        // Cortes precisos
        codigo_grado:     v.substring(0,2),
        codigo_seccion:   v.substring(2,4),
        codigo_turno:     v.substring(4,6),
        codigo_modalidad: v.substring(6,8),
        codigo_ann_lectivo: v.substring(8,10) // <-- Ya no lo sacamos del otro select, sino de aquí
    };

    $('#contenedorNotas').hide();

    $.get("{{ url('calificaciones/buscar-estudiantes') }}", params, function(res) {
        if(res.status === 'locked') {
            Swal.fire('Periodo Cerrado', res.message, 'error');
            $('#contenedorNotas').hide();
            return;
        }

        let filas = '';
        $.each(res.estudiantes, function(i, e) {
            // Calculamos el promedio visualmente si es necesario o mostramos el de DB
            let promedioActual = parseFloat(e.nota_p) || 0;
            let fullCodigo = $('#codigo_seccion').val();
            let mod = fullCodigo.substring(6,8);
            let minima = (mod >= '17' && mod <= '19') ? 5.0 : 6.0;

            let claseRoja = (promedioActual < minima) ? 'nota-reprobada' : '';
            
            filas += `
                <tr class="fila-estudiante" data-alumno="${e.codigo_alumno}" data-matricula="${e.codigo_matricula}">
                    <td class="text-center">${e.codigo_nie}</td>
                    <td>${e.nombre_completo}</td>
                    <td><input type="number" step="0.1" class="form-control text-center input-a1" value="${e.nota_a1 || ''}"></td>
                    <td><input type="number" step="0.1" class="form-control text-center input-a2" value="${e.nota_a2 || ''}"></td>
                    <td><input type="number" step="0.1" class="form-control text-center input-a3" value="${e.nota_a3 || ''}"></td>
                    <td><input type="number" step="0.1" class="form-control text-center input-r" value="${e.nota_r || ''}"></td>
                    <td class="text-center align-middle font-weight-bold bg-light">
                        <span class="label-promedio ${claseRoja}">${promedioActual.toFixed(1)}</span>
                    </td>
                </tr>`;
        });
        $('#cuerpoTablaEstudiantes').html(filas);
        $('#contenedorNotas').fadeIn();
    });
}

    // Función para guardar todo el listado
function guardarTodasLasNotas() {
    let notasData = [];
    let fullCodigo = $('#codigo_seccion').val();
    
    // Mostramos un loading
    $('#btnGuardarNotas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $('.fila-estudiante').each(function() {
        let fila = $(this);
        notasData.push({
            codigo_matricula: fila.data('matricula'),
            nota_a1: fila.find('.input-a1').val() || 0,
            nota_a2: fila.find('.input-a2').val() || 0,
            nota_a3: fila.find('.input-a3').val() || 0,
            nota_r:  fila.find('.input-r').val() || 0
        });
    });

    let payload = {
        _token: '{{ csrf_token() }}',
        periodo: $('#periodo').val(),
        codigo_asignatura: $('#codigo_asignatura').val(),
        codigo_modalidad: fullCodigo.substring(6,8), // Enviamos la modalidad para el cálculo
        notas: notasData
    };

    $.ajax({
        type: "POST",
        url: "{{ url('calificaciones/guardar-todas') }}",
        data: JSON.stringify(payload),
        contentType: "application/json",
        success: function(res) {
            Swal.fire('¡Éxito!', res.message, 'success');
        },
        error: function(err) {
            Swal.fire('Error', 'No se pudieron guardar las notas.', 'error');
        },
        complete: function() {
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
</script>
@endsection