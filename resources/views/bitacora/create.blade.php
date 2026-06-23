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
                                    <textarea class="form-control" name="descripcion" rows="8" required placeholder="Escriba aquí los pormenores del caso o acuerdos alcanzados..."></textarea>
                                </div>
                        </div>

                        <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                            <button type="button" class="btn btn-success btn-lg px-5 shadow" id="btnGuardarBitacora" onclick="guardarBitacora()">
                                <i class="fas fa-save mr-2"></i> Registrar en Bitácora
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
                                    <div>
                                        <span class="badge bg-dark text-white mr-2">Código Incidencia: {{ $nota->codigo_tipo_incidencia ?? '01' }}</span>
                                        <button type="button" class="btn btn-xs btn-info p-1 text-white" onclick="abrirModalEditar({{ json_encode($nota) }})">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                </div>
                                <p class="mb-0 text-dark" style="white-space: pre-line;" id="texto-nota-{{ $nota->id_alumno_bitacora ?? $loop->index }}">{{ $nota->descripcion }}</p>
                            </div>
                        </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarBitacora" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEditarLabel"><i class="fas fa-edit me-2"></i> Modificar Registro de Bitácora</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formEditarBitacora">
                    @csrf
                    <input type="hidden" name="id_bitacora" id="edit_id_bitacora">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="edit_fecha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Hora</label>
                            <input type="time" class="form-control" name="hora" id="edit_hora" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Tipo de Incidencia</label>
                            <select class="form-control" name="codigo_tipo_incidencia" id="edit_codigo_tipo_incidencia" required>
                                <option value="01">Académico / Rendimiento</option>
                                <option value="02">Conducta / Disciplina</option>
                                <option value="03">Asistencia / Puntualidad</option>
                                <option value="04">Aspecto Positivo / Destacado</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Descripción / Acuerdos</label>
                                <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="8" required></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info px-4 shadow text-white" id="btnActualizarBitacora" onclick="actualizarBitacora()">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function guardarBitacora() {
    let formulario = document.getElementById('formBitacoraEstudiante');
    let formData = new FormData(formulario);
    let btn = $('#btnGuardarBitacora');

    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando Registro...');

    $.ajax({
        url: '{{ route("bitacora.store") }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Registro Exitoso!',
                text: response.message,
                confirmButtonColor: '#28a745'
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Registrar en Bitácora');
            console.error("====== ERROR DETALLADO DEL SERVIDOR ======");
            console.error("Código de estado:", xhr.status);
            console.error("Respuesta de error:", xhr.responseText);
            console.error("==========================================");
            
            let errorText = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo procesar la solicitud en el servidor.';
            Swal.fire({
                icon: 'error',
                title: 'Error de Almacenamiento',
                text: errorText,
                confirmButtonColor: '#dc3545'
            });
        }
    });
}
function abrirModalEditar(nota) {
    // Imprimimos el objeto en la consola F12 para que veas los nombres reales de tus columnas
    console.log("DATOS RECIBIDOS DE LA BITÁCORA:", nota);

    // Buscamos todas las variantes posibles de nombres que pueda tener tu llave primaria en PostgreSQL
    const idRegistro = nota.id_alumno_bitacora || nota.id_bitacora || nota.id || nota.id_registro;
    
    if (!idRegistro || idRegistro === "undefined") {
        console.error("ALERTA: No se pudo capturar un ID numérico válido para este registro.", nota);
    }

    // Inyectamos los valores de forma segura al formulario del Modal
    document.getElementById('edit_id_bitacora').value = idRegistro;
    document.getElementById('edit_fecha').value = nota.fecha || '';
    document.getElementById('edit_hora').value = nota.hora || '';
    
    // Validamos el tipo de incidencia limpiando espacios en blanco que deja Postgres (char/varchar)
    let tipoIncidencia = '01';
    if (nota.codigo_tipo_incidencia) {
        tipoIncidencia = nota.codigo_tipo_incidencia.toString().trim();
    }
    document.getElementById('edit_codigo_tipo_incidencia').value = tipoIncidencia;
    document.getElementById('edit_descripcion').value = nota.descripcion || '';
    
    // Abrimos el modal nativamente con Bootstrap 5
    var modalElement = document.getElementById('modalEditarBitacora');
    var miModal = new bootstrap.Modal(modalElement);
    miModal.show();
}

// Aseguramos que la función secundaria haga exactamente el mismo mapeo seguro
function cargarDatosEnFormulario(nota) {
    const idRegistro = nota.id_alumno_bitacora || nota.id_bitacora || nota.id || nota.id_registro;
    document.getElementById('edit_id_bitacora').value = idRegistro;
    document.getElementById('edit_fecha').value = nota.fecha || '';
    document.getElementById('edit_hora').value = nota.hora || '';
    document.getElementById('edit_codigo_tipo_incidencia').value = nota.codigo_tipo_incidencia ? nota.codigo_tipo_incidencia.toString().trim() : '01';
    document.getElementById('edit_descripcion').value = nota.descripcion || '';
}
function actualizarBitacora() {
    let formulario = document.getElementById('formEditarBitacora');
    let formData = new FormData(formulario);
    let btn = $('#btnActualizarBitacora');

    if (!formulario.checkValidity()) {
        formulario.reportValidity();
        return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Actualizando...');

    $.ajax({
        url: '{{ url("bitacora/actualizar") }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            // Buscamos la instancia activa del modal para cerrarla fluidamente en Bootstrap 5
            var modalElement = document.getElementById('modalEditarBitacora');
            var instance = bootstrap.Modal.getInstance(modalElement);
            if (instance) {
                instance.hide();
            }

            Swal.fire({
                icon: 'success',
                title: '¡Modificación Exitosa!',
                text: response.message,
                confirmButtonColor: '#17a2b8'
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar Cambios');
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar',
                text: 'No se pudieron salvar las modificaciones.',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}
</script>
@endsection