@extends('layouts.app') {{-- O tu layout base --}}

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 m-0"><i class="fas fa-search me-2"></i> Control de Bitácoras por Sección</h5>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Seleccione una Sección de su Carga Académica:</label>
                    <select class="form-select form-select-lg" id="selectCargaDocente">
                        <option value="">-- Seleccione Grado y Sección --</option>
                        @foreach($cargas as $carga)
                            <option value="{{ $carga->id_carga_docente }}">
                                Grado: {{ $carga->codigo_grado }} | Sección: {{ $carga->codigo_seccion }} | Turno: {{ $carga->codigo_turno }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive d-none" id="contenedorTablaAlumnos">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">Código/NIE</th>
                            <th>Nombre Completo del Estudiante</th>
                            <th style="width: 20%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAlumnosBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#selectCargaDocente').on('change', function() {
        const idCarga = $(this).val();
        const contenedor = $('#contenedorTablaAlumnos');
        const tbody = $('#tablaAlumnosBody');
        
        if (!idCarga) {
            contenedor.addClass('d-none');
            return;
        }

        tbody.html('<tr><td colspan="3" class="text-center">Cargando estudiantes...</td></tr>');
        contenedor.removeClass('d-none');

        // Petición AJAX para traer los alumnos de la sección
        $.ajax({
            url: `/bitacora/alumnos/${idCarga}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                tbody.html('');
                if (data.length === 0) {
                    tbody.html('<tr><td colspan="3" class="text-center text-muted">No hay estudiantes matriculados en esta sección.</td></tr>');
                    return;
                }

                data.forEach(alumno => {
                    tbody.append(`
                        <tr>
                            <td><strong>${alumno.codigo_alumno}</strong></td>
                            <td class="text-uppercase">${alumno.nombre_completo}</td>
                            <td class="text-center">
                                <a href="/bitacora/estudiante/${alumno.codigo_alumno}/${idCarga}" class="btn btn-sm btn-dark px-3">
                                    <i class="fas fa-book-open me-1"></i> Bitácora
                                </a>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function() {
                tbody.html('<tr><td colspan="3" class="text-center text-danger">Error al cargar el listado.</td></tr>');
            }
        });
    });
});
</script>
@endsection