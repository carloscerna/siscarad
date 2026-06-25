@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="container-fluid py-4">
    <div class="mb-3">
        <h4 class="fw-bold text-dark"><i class="fas fa-gavel text-danger mb-2 me-2"></i>Registro Consolidado Mensual de Convivencia Escolar</h4>
        <p class="text-muted">Gestión integrada de deméritos, redenciones y reconocimientos por sección a cargo.</p>
    </div>

    <!-- FORMULARIO DE FILTROS SUPERIORES -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light p-4">
            <form id="formFiltrosConsolidado">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-3 mb-md-0">
                        <label class="form-label fw-bold text-secondary">Sección a Cargo:</label>
                        <select class="form-select form-control text-uppercase fw-bold text-dark" id="filtroCarga" required>
                            <option value="">-- Seleccione una sección --</option>
                            @foreach($secciones as $sec)
                                <option value="{{ $sec->id_encargado_grado }}">
                                    {{ trim($sec->bachi) }} - {{ trim($sec->grado) }} "{{ trim($sec->seccion) }}" - {{ trim($sec->turno) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label fw-bold text-secondary">Mes de Evaluación:</label>
                        <select class="form-select form-control fw-bold text-dark" id="filtroMes">
                            <option value="">-- Seleccione un mes para captura directa --</option>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-dark w-100 fw-bold shadow-sm" id="btnCargarModulo">
                            <i class="fas fa-edit me-1 text-warning"></i> Captura Directa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PANTALLA A: TABLA DE CONTROL DE MESES (NUEVA) -->
    <div class="card shadow-sm border-0 mb-4 d-none" id="panelTablaMeses">
        <div class="card-header bg-secondary text-white py-3">
            <h5 class="mb-0 m-0 fw-bold"><i class="fas fa-calendar-check me-2 text-warning"></i>Estado de Envío del Año Lectivo 2026</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 30%">Mes Académico</th>
                            <th style="width: 40%">Estado del Registro</th>
                            <th class="text-center" style="width: 30%">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyControlMeses">
                        <!-- Generado dinámicamente mediante JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PANTALLA B: MATRIZ DE CAPTURA DE COMPORTAMIENTO -->
    <div class="card shadow-sm border-0 d-none" id="panelCapturaMasiva">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <button type="button" class="btn btn-sm btn-outline-light fw-bold" id="btnVolverAlMenu">
                <i class="fas fa-arrow-left me-1"></i> Regresar a Meses
            </button>
            <span class="badge bg-danger fs-6" id="badgeEstadoRegistro">NUEVO REGISTRO</span>
        </div>
        
        <form id="formMatrizDemeritos">
            @csrf
            <input type="hidden" name="id_encargado_grado" id="hiddenCarga">
            <input type="hidden" name="mes_evaluacion" id="hiddenMes">

            <div class="card-body p-4">
                <!-- MATRÍCULA DEL MES -->
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                    <i class="fas fa-users fa-2x me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Matrícula Escolar Base para el Mes Evaluado:</h6>
                        <span class="me-4"><strong>Hombres Inscritos:</strong> <span id="txtMatriculaH">0</span></span>
                        <input type="hidden" name="matricula_hombres" id="inputMatriculaH">
                        <span><strong>Mujeres Inscritas:</strong> <span id="txtMatriculaM">0</span></span>
                        <input type="hidden" name="matricula_mujeres" id="inputMatriculaM">
                    </div>
                </div>

                <div class="row">
                    <!-- COLUMNA IZQUIERDA: DEMÉRITOS GENERALES Y POR CAUSALES -->
                    <div class="col-lg-6 border-end">
                        <h5 class="fw-bold text-danger border-bottom pb-2 mb-3"><i class="fas fa-minus-circle me-2"></i>1. Registro de Deméritos</h5>
                        
                        <div class="row g-3 mb-4 bg-light p-3 rounded">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Total Demeritos (Hombres):</label>
                                <input type="number" class="form-control form-control-sm border-danger fw-bold" name="total_demeritos_hombres" id="total_demeritos_hombres" min="0" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Total Demeritos (Mujeres):</label>
                                <input type="number" class="form-control form-control-sm border-danger fw-bold" name="total_demeritos_mujeres" id="total_demeritos_mujeres" min="0" value="0">
                            </div>
                        </div>

                        <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-list-ol me-2"></i>Número de Deméritos por Causales (Art. 3)</h6>
                        <div class="mb-3">
                            <label class="form-label small"><strong>Causal A:</strong> No saludar al entrar o al salir del aula.</label>
                            <input type="number" class="form-control form-control-sm" name="dem_causal_a" id="dem_causal_a" min="0" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small"><strong>Causal B:</strong> Omitir “Por favor” al hacer una petición.</label>
                            <input type="number" class="form-control form-control-sm" name="dem_causal_b" id="dem_causal_b" min="0" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small"><strong>Causal C:</strong> Omitir “Gracias” al recibir favores o materiales.</label>
                            <input type="number" class="form-control form-control-sm" name="dem_causal_c" id="dem_causal_c" min="0" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small"><strong>Causal D:</strong> Usar un tono grosero o irrespetuoso.</label>
                            <input type="number" class="form-control form-control-sm" name="dem_causal_d" id="dem_causal_d" min="0" value="0">
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA: REDENCIONES Y RECONOCIMIENTOS -->
                    <div class="col-lg-6">
                        <!-- REDENCIONES -->
                        <h5 class="fw-bold text-success border-bottom pb-2 mb-3"><i class="fas fa-heart text-success me-2"></i>2. Redenciones (Art. 6)</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Redenciones (Hombres):</label>
                                <input type="number" class="form-control form-control-sm border-success fw-bold" name="redenciones_hombres" id="redenciones_hombres" min="0" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Redenciones (Mujeres):</label>
                                <input type="number" class="form-control form-control-sm border-success fw-bold" name="redenciones_mujeres" id="redenciones_mujeres" min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-2 bg-light p-2 rounded">
                            <div class="mb-2">
                                <label class="form-label small">Opcion A (Semana de cortesía ejemplar):</label>
                                <input type="number" class="form-control form-control-sm" name="redencion_opcion_a" id="redencion_opcion_a" min="0" value="0">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Opcion B (Apoyo en orden y limpieza):</label>
                                <input type="number" class="form-control form-control-sm" name="redencion_opcion_b" id="redencion_opcion_b" min="0" value="0">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Opcion C (Campañas de valores):</label>
                                <input type="number" class="form-control form-control-sm" name="redencion_opcion_c" id="redencion_opcion_c" min="0" value="0">
                            </div>
                        </div>

                        <!-- RECONOCIMIENTOS -->
                        <h5 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-3"><i class="fas fa-trophy text-warning me-2"></i>3. Reconocimientos (Art. 7)</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Reconocidos (Hombres):</label>
                                <input type="number" class="form-control form-control-sm border-primary fw-bold" name="reconocimientos_hombres" id="reconocimientos_hombres" min="0" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Reconocidos (Mujeres):</label>
                                <input type="number" class="form-control form-control-sm border-primary fw-bold" name="reconocimientos_mujeres" id="reconocimientos_mujeres" min="0" value="0">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small">Mención por Diplomas Otorgados:</label>
                                <input type="number" class="form-control form-control-sm" name="reconocimiento_diploma" id="reconocimiento_diploma" min="0" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Mención en Murales Escolares:</label>
                                <input type="number" class="form-control form-control-sm" name="reconocimiento_mural" id="reconocimiento_mural" min="0" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTONES DE ACCIÓN -->
            <div class="card-footer bg-light p-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger fw-bold d-none" id="btnEliminarConsolidado">
                    <i class="fas fa-trash-alt me-1"></i> Eliminar Registro Mensual
                </button>
                <div class="ms-auto">
                    <button type="submit" class="btn btn-success px-5 fw-bold shadow-sm" id="btnGuardarConsolidado">
                        <i class="fas fa-save me-1"></i> Guardar Cambios del Mes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    const nombresMeses = {
        1: "Enero", 2: "Febrero", 3: "Marzo", 4: "Abril", 5: "Mayo",
        6: "Junio", 7: "Julio", 8: "Agosto", 9: "Septiembre", 10: "Octubre"
    };

    // AL CAMBIAR DE SECCIÓN: Cargar bitácora de meses de forma prioritaria
    $('#filtroCarga').on('change', function() {
        renderizarBitacoraMeses();
    });

    function renderizarBitacoraMeses() {
        const idCarga = $('#filtroCarga').val();
        if (!idCarga) {
            $('#panelTablaMeses').addClass('d-none');
            $('#panelCapturaMasiva').addClass('d-none');
            return;
        }

        $('#panelCapturaMasiva').addClass('d-none'); // Aseguramos ocultar matriz de datos
        const tbody = $('#tbodyControlMeses');
        tbody.html('<tr><td colspan="3" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Consultando estatus de entrega mensual...</td></tr>');
        $('#panelTablaMeses').removeClass('d-none');

        $.ajax({
            url: `{{ url('/consolidado-conducta/verificar-meses') }}/${idCarga}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                tbody.html('');
                const mesesGuardados = response.meses_registrados;

                // Construimos la fila de Enero a Octubre de forma estructurada
                for (let m = 1; m <= 10; m++) {
                    let yaExiste = mesesGuardados.includes(m);
                    let badgeEstatus = yaExiste 
                        ? `<span class="badge bg-success px-3 py-1 fs-6"><i class="fas fa-check-circle me-1"></i> REGISTRADO</span>`
                        : `<span class="badge bg-light text-muted border px-3 py-1 fs-6"><i class="far fa-clock me-1"></i> PENDIENTE</span>`;
                    
                    let btnAccion = yaExiste
                        ? `<button type="button" class="btn btn-sm btn-primary px-3 fw-bold" onclick="abrirCapturaMes(${m})"><i class="fas fa-folder-open me-1"></i> Ver / Editar</button>`
                        : `<button type="button" class="btn btn-sm btn-outline-success px-3 fw-bold" onclick="abrirCapturaMes(${m})"><i class="fas fa-plus me-1"></i> Rellenar</button>`;

                    tbody.append(`
                        <tr>
                            <td class="ps-4 fw-bold text-dark text-uppercase">${nombresMeses[m]}</td>
                            <td>${badgeEstatus}</td>
                            <td class="text-center">${btnAccion}</td>
                        </tr>
                    `);
                }
            }
        });
    }

    // DISPARADOR DE CAPTURA DESDE LA TABLA O POR FILTRO DIRECTO
    window.abrirCapturaMes = function(mes) {
        const idCarga = $('#filtroCarga').val();
        
        $('#hiddenCarga').val(idCarga);
        $('#hiddenMes').val(mes);

        $.ajax({
            url: `{{ url('/consolidado-conducta/cargar') }}/${idCarga}/${mes}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#panelTablaMeses').addClass('d-none'); // Ocultamos la tabla de meses
                $('#panelCapturaMasiva').removeClass('d-none'); // Mostramos el panel de captura
                
                $('#txtMatriculaH').text(response.datos.matricula_hombres);
                $('#inputMatriculaH').val(response.datos.matricula_hombres);
                $('#txtMatriculaM').text(response.datos.matricula_mujeres);
                $('#inputMatriculaM').val(response.datos.matricula_mujeres);

                for (const key in response.datos) {
                    if ($(`#${key}`).length) {
                        $(`#${key}`).val(response.datos[key]);
                    }
                }

                if (response.existe) {
                    $('#badgeEstadoRegistro').removeClass('bg-danger').addClass('bg-success').text(`MES DE ${nombresMeses[mes].toUpperCase()} - REGISTRADO`);
                    $('#btnEliminarConsolidado').removeClass('d-none');
                } else {
                    $('#badgeEstadoRegistro').removeClass('bg-success').addClass('bg-danger').text(`MES DE ${nombresMeses[mes].toUpperCase()} - NUEVO`);
                    $('#btnEliminarConsolidado').addClass('d-none');
                }
            }
        });
    };

    // BOTÓN: CAPTURA DIRECTA SUPERIOR
    $('#btnCargarModulo').on('click', function() {
        const mes = $('#filtroMes').val();
        if (!mes) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione un mes en el menú desplegable para usar la captura rápida.' });
            return;
        }
        abrirCapturaMes(mes);
    });

    // BOTÓN: REGRESAR A LA TABLA DE MESES
    $('#btnVolverAlMenu').on('click', function() {
        $('#panelCapturaMasiva').addClass('d-none');
        renderizarBitacoraMeses();
    });

    // GUARDAR O ACTUALIZAR DATOS
    $('#formMatrizDemeritos').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnGuardarConsolidado');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            url: "{{ url('/consolidado-conducta/guardar') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar Cambios del Mes');
                if (response.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: response.message, confirmButtonColor: '#198754' });
                    $('#btnVolverAlMenu').click(); // Regresa de forma automática al tablero de control
                }
            }
        });
    });

    // ELIMINAR REGISTRO MENSUAL
    $('#btnEliminarConsolidado').on('click', function() {
        const idCarga = $('#hiddenCarga').val();
        const mes = $('#hiddenMes').val();

        Swal.fire({
            title: '¿Confirmar eliminación?',
            text: "Se borrarán de forma permanente los conteos de este mes.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('/consolidado-conducta/eliminar') }}/${idCarga}/${mes}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        Swal.fire('Eliminado', 'El registro mensual ha sido borrado.', 'success');
                        $('#btnVolverAlMenu').click();
                    }
                });
            }
        });
    });
});
</script>
@endsection