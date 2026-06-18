@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 m-0"><i class="fas fa-search me-2"></i> Control de Bitácoras por Sección</h5>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Seleccione una Sección de su Carga Académica:</label>
                    <select class="form-select form-control" id="selectCargaDocente">
                        <option value="">-- Seleccione una opción --</option>
                        @foreach($cargas as $carga)
                            <option value="{{ $carga->id_carga_docente }}">
                                {{ $carga->nombre_bachillerato }} - 
                                {{ $carga->nombre_grado }} - 
                                Sección "{{ $carga->nombre_seccion }}" - 
                                Turno {{ $carga->nombre_turno }} - 
                                20{{ trim($carga->codigo_ann_lectivo) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive d-none" id="contenedorTablaAlumnos">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 8%">ID</th>
                            <th style="width: 12%">NIE</th>
                            <th>Nombre Completo del Estudiante (Apellidos, Nombres)</th>
                            <th style="width: 15%" class="text-center">Acciones</th>
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
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Actualizar la consulta AJAX en el controlador para que jale también el NIE
    $('#selectCargaDocente').on('change', function() {
        const idCarga = $(this).val();
        const contenedor = $('#contenedorTablaAlumnos');
        const tbody = $('#tablaAlumnosBody');
        
        if (!idCarga) {
            contenedor.addClass('d-none');
            return;
        }

        tbody.html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i> Buscando estudiantes matriculados...</td></tr>');
        contenedor.removeClass('d-none');

        $.ajax({
            url: "{{ url('bitacora/alumnos') }}/" + idCarga,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                tbody.html('');
                if (data.length === 0) {
                    tbody.html('<tr><td colspan="4" class="text-center text-muted">No se encontraron estudiantes matriculados.</td></tr>');
                    return;
                }

                data.forEach(alumno => {
                    // Si el NIE viene vacío o nulo ponemos un guión
                    const nieValue = alumno.codigo_nie ? alumno.codigo_nie.trim() : '-';
                    
                    tbody.append(`
                        <tr>
                            <td><span class="text-secondary small">${alumno.id_alumno}</span></td>
                            <td><strong>${nieValue}</strong></td>
                            <td class="text-uppercase">${alumno.nombre_completo_formateado}</td>
                            <td class="text-center">
                                <a href="{{ url('bitacora/estudiante') }}/${alumno.id_alumno}/${idCarga}" class="btn btn-sm btn-dark btn-block shadow-sm">
                                    <i class="fas fa-book-open me-1"></i> Abrir Bitácora
                                </a>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Hubo un problema al traer la nómina de estudiantes.',
                    confirmButtonColor: '#3085d6'
                });
                tbody.html('<tr><td colspan="4" class="text-center text-danger">Fallo al cargar datos.</td></tr>');
            }
        });
    });
});
</script>
@endsection