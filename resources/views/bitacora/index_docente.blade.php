@extends('layouts.app')

@php
    // Captura segura de la sesión activa para el contexto de la vista
    $nombre_docente = Auth::user()->name;
    $codigo_personal = Auth::user()->codigo_personal; 
    $codigo_institucion = Auth::user()->codigo_institucion;                                                
@endphp

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid py-4">
    
    <div class="mb-3">
        <h4 class="fw-bold text-dark"><i class="fas fa-user-chalkboard text-info me-2"></i>{{ $nombre_docente }}</h4>
        <span class="badge bg-secondary">Código Personal: {{ $codigo_personal }}</span>
    </div>

    <input type="hidden" id="codigo_personal" value="{{ $codigo_personal }}">
    <input type="hidden" id="codigo_institucion" value="{{ $codigo_institucion }}">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 m-0"><i class="fas fa-search me-2"></i> Control de Bitácoras por Sección</h5>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Seleccione una Sección de su Carga Académica:</label>
                    <select class="form-select form-control text-uppercase fw-bold text-dark" id="selectCargaDocente">
                        <option value="">-- Seleccione una opción --</option>
                        @foreach($cargas as $carga)
                            <option value="{{ $carga->id_carga_docente }}">
                                {{ $carga->nombre_bachillerato }} - {{ $carga->nombre_grado }} - SECCIÓN "{{ $carga->nombre_seccion }}" - {{ $carga->nombre_turno }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-4 d-none" id="contenedorEstadisticas">
                <div class="col-6 col-md-3">
                    <div class="card bg-light border-start border-primary border-4 shadow-sm mb-2">
                        <div class="card-body p-3 text-center">
                            <span class="text-muted small fw-bold text-uppercase d-block">Matrícula Total</span>
                            <h3 class="fw-bold text-primary mb-0" id="stat_total">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-light border-start border-info border-4 shadow-sm mb-2">
                        <div class="card-body p-3 text-center">
                            <span class="text-muted small fw-bold text-uppercase d-block"><i class="fas fa-mars text-info me-1"></i> Masculinos</span>
                            <h3 class="fw-bold text-info mb-0" id="stat_masculino">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-light border-start border-purple border-4 shadow-sm mb-2" style="border-left-color: #e83e8c !important;">
                        <div class="card-body p-3 text-center">
                            <span class="text-muted small fw-bold text-uppercase d-block"><i class="fas fa-venus me-1" style="color: #e83e8c;"></i> Femeninos</span>
                            <h3 class="fw-bold mb-0" style="color: #e83e8c;" id="stat_femenino">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-light border-start border-danger border-4 shadow-sm mb-2">
                        <div class="card-body p-3 text-center">
                            <span class="text-muted small fw-bold text-uppercase d-block"><i class="fas fa-user-slash text-danger me-1"></i> Retirados</span>
                            <h3 class="fw-bold text-danger mb-0" id="stat_retirado">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive d-none" id="contenedorTablaAlumnos">
                <table class="table table-hover table-bordered align-middle shadow-sm">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 8%">N°</th>
                            <th style="width: 15%">NIE</th>
                            <th>Nombre Completo del Estudiante (Apellidos, Nombres)</th>
                            <th style="width: 15%" class="text-center">Estado</th>
                            <th style="width: 18%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAlumnosBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>document.addEventListener("DOMContentLoaded", function() {
    $('#selectCargaDocente').on('change', function() {
        const idCarga = $(this).val();
        const contenedorTabla = $('#contenedorTablaAlumnos');
        const contenedorStats = $('#contenedorEstadisticas');
        const tbody = $('#tablaAlumnosBody');
        
        if (!idCarga) {
            contenedorTabla.addClass('d-none');
            contenedorStats.addClass('d-none');
            return;
        }

        tbody.html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-2 d-block"></i> Filtrando matrícula del año electivo actual...</td></tr>');
        contenedorTabla.removeClass('d-none');
        contenedorStats.removeClass('d-none');

        $.ajax({
            url: "{{ url('bitacora/alumnos') }}/" + idCarga,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                tbody.html('');
                
                // 1. Seteamos los contadores dinámicos arriba de la tabla
                $('#stat_total').text(response.totales.t);
                $('#stat_masculino').text(response.totales.m);
                $('#stat_femenino').text(response.totales.f);
                $('#stat_retirado').text(response.totales.r);

                const listaAlumnos = response.alumnos;

                if (listaAlumnos.length === 0) {
                    tbody.html('<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-folder-open fa-2x mb-2 d-block"></i> No se encontraron estudiantes matriculados para este año lectivo.</td></tr>');
                    return;
                }

                // 2. Renderizamos la nómina oficial limpia
                let correlativo = 1;
                listaAlumnos.forEach(alumno => {
                    const nieValue = alumno.codigo_nie ? alumno.codigo_nie.trim() : '-';
                    
                    // Validamos si el alumno tiene estatus retirado para cambiar el badge visual
                    let esRetirado = alumno.retirado === true || alumno.retirado == 1 || alumno.retirado == 't';
                    let badgeEstado = esRetirado 
                        ? `<span class="badge bg-danger px-2 py-1"><i class="fas fa-user-times me-1"></i> RETIRADO</span>`
                        : `<span class="badge bg-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> ACTIVO</span>`;
                    
                    // Fila opaca si el alumno está retirado para guiar la vista
                    let filaEstilo = esRetirado ? 'style="background-color: #f8f9fa; opacity: 0.65;"' : '';

                    tbody.append(`
                        <tr ${filaEstilo} class="animated fadeIn">
                            <td class="text-center text-secondary small fw-bold">${correlativo++}</td>
                            <td><span class="badge bg-light text-dark border border-secondary px-2 py-1 fs-6"><strong>${nieValue}</strong></span></td>
                            <td class="text-uppercase fw-bold text-dark">${alumno.nombre_completo_formateado}</td>
                            <td class="text-center">${badgeEstado}</td>
                            <td class="text-center">
                                <a href="{{ url('bitacora/estudiante') }}/${alumno.id_alumno}/${idCarga}" class="btn btn-sm btn-dark px-3 shadow-sm">
                                    <i class="fas fa-book-open me-1 text-warning"></i> Abrir Bitácora
                                </a>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de consistencia',
                    text: 'Hubo un inconveniente al procesar la nómina escolar.',
                    confirmButtonColor: '#0d6efd'
                });
                tbody.html('<tr><td colspan="5" class="text-center text-danger fw-bold py-3">Fallo al cargar datos del servidor.</td></tr>');
            }
        });
    });
});
</script>
@endsection