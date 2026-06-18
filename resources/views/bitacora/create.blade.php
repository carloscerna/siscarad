@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="{{ route('bitacora.index_docente') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver al Listado de Alumnos
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 m-0"><i class="fas fa-pen-square text-warning me-2"></i>Nueva Anotación</h5>
                    <small class="text-info fw-bold text-uppercase d-block mt-1">{{ $alumno->nombre_completo_formateado }}</small>
                    <span class="badge bg-secondary mt-1">NIE: {{ $alumno->codigo_nie ?? 'Sin Registro' }}</span>
                </div>
                <div class="card-body p-4">
                    <form id="formBitacoraEstudiante">
                        @csrf
                        <input type="hidden" name="id_alumno" value="{{ $alumno->id_alumno }}">
                        <input type="hidden" name="id_carga_docente" value="{{ $id_carga_docente }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Fecha</label>
                                <input type="date" class="form-control" name="fecha" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary">Hora</label>
                                <input type="time" class="form-control" name="hora" value="{{ date('H:i') }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary">Tipo de Incidencia</label>
                                <select class="form-select form-control" name="codigo_tipo_incidencia">
                                    <option value="01">Académico / Rendimiento</option>
                                    <option value="02">Conducta / Disciplina</option>
                                    <option value="03">Asistencia / Puntualidad</option>
                                    <option value="04">Aspecto Positivo / Destacado</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary">Asunto / Título Corto</label>
                                <input type="text" class="form-control" name="asunto" required placeholder="Ej: Incumplimiento de tareas académicas" autocomplete="off">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary">Descripción Detallada del Hecho</label>
                                <textarea class="form-control" name="descripcion" rows="5" required placeholder="Escriba aquí los pormenores del caso o acuerdos alcanzados..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 shadow" id="btnGuardarBitacora">
                                <i class="fas fa-save me-1"></i> Registrar en Bitácora
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="mb-0 m-0"><i class="fas fa-history me-2"></i> Historial de Bitácoras del Año</h5>
                </div>
                <div class="card-body p-4" style="max-height: 520px; overflow-y: auto;">
                    @if($historial->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p>El estudiante no posee registros previos en este año lectivo.</p>
                        </div>
                    @else
                        @foreach($historial as $nota)
                            <div class="card mb-3 border-start border-primary border-3 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between text-muted small mb-2">
                                        <span><i class="fas fa-calendar-alt me-1"></i> {{ date('d/m/Y', strtotime($nota->fecha)) }} - {{ $nota->hora }}</span>
                                        <span class="badge bg-dark text-white">Código Incidencia: {{ $nota->codigo_tipo_incidencia ?? '01' }}</span>
                                    </div>
                                    <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $nota->descripcion }}</p>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#formBitacoraEstudiante').on('submit', function(e) {
    e.preventDefault();
    
    let formData = new FormData(this);
    let btn = $('#btnGuardarBitacora');

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');

    $.ajax({
        url: '{{ route("bitacora.store") }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            // PUNTO 4: SweetAlert elegante para éxito
            Swal.fire({
                icon: 'success',
                title: '¡Buen trabajo!',
                text: response.message,
                confirmButtonColor: '#28a745'
            }).then(() => {
                location.reload(); // Recarga limpia para ver la nota en el historial
            });
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Registrar en Bitácora');
            
            // PUNTO 4: SweetAlert elegante para errores del servidor
            let errorText = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Ocurrió un error inesperado al procesar la solicitud.';
            Swal.fire({
                icon: 'error',
                title: 'Error al registrar',
                text: errorText,
                confirmButtonColor: '#dc3545'
            });
        }
    });
});
</script>
@endsection