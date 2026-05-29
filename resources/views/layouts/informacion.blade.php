@extends('layouts.app') @section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="card-title mb-0 d-flex align-items-center">
                <i class="fas fa-user-check me-2"></i> 
                Actualización de Información General: {{ trim($alumno->nombre_completo) }}
            </h5>
        </div>
        <div class="card-body p-4">

            <form action="{{ route('estudiante.informacion.update', $alumno->id_alumno) }}" method="POST" enctype="multipart/form-data" id="formInformacion">
                @csrf
                @method('PUT')

                <div class="row mb-4 bg-light p-3 rounded border mx-0">
                    <div class="col-12 text-center text-md-start">
                        <label class="form-label fw-bold text-dark"><i class="fas fa-camera me-1"></i> Fotografía del Alumno</label>
                        <div class="d-flex flex-column flex-md-row align-items-center gap-4 mt-2">
                            <div class="border rounded bg-white d-flex align-items-center justify-content-center shadow-sm" style="width: 140px; height: 160px; overflow: hidden;">
                                <img id="previewFoto" src="{{ $alumno->foto ? asset('storage/' . $alumno->foto) : asset('img/default-avatar.png') }}" alt="Foto Alumno" class="img-fluid h-100 w-100" style="object-fit: cover;">
                            </div>
                            <div class="ml-md-3 mt-3 mt-md-0">
                                <input type="file" name="foto" id="inputFoto" accept="image/*" capture="user" class="form-control d-none">
                                <button type="button" class="btn btn-outline-primary btn-sm mb-2 fw-semibold" onclick="document.getElementById('inputFoto').click();">
                                    <i class="fas fa-mobile-alt me-1"></i> Activar Cámara / Subir Foto
                                </button>
                                <small class="text-muted d-block">Nota: Al pulsar desde un teléfono móvil se solicitará abrir la cámara automáticamente.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 border-bottom pb-2 mb-2">
                        <h6 class="text-primary mb-0 fw-bold"><i class="fas fa-user-shield me-1"></i> Datos del Responsable / Encargado</h6>
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label for="nombres_encargado" class="form-label fw-semibold">Nombre Completo del Responsable</label>
                        <input type="text" class="form-control" id="nombres_encargado" name="nombres_encargado" value="{{ old('nombres_encargado', $alumno->encargadoPrincipal->nombres ?? '') }}" required>
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="telefono_encargado" class="form-label fw-semibold">Número de Teléfono</label>
                        <input type="tel" class="form-control" id="telefono_encargado" name="telefono_encargado" value="{{ old('telefono_encargado', $alumno->encargadoPrincipal->telefono ?? '') }}" required>
                    </div>

                    <div class="col-12 form-group mt-2">
                        <label for="direccion_encargado" class="form-label fw-semibold">Dirección del Responsable</label>
                        <textarea class="form-control" id="direccion_encargado" name="direccion_encargado" rows="2" required>{{ old('direccion_encargado', $alumno->encargadoPrincipal->direccion ?? '') }}</textarea>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 border-bottom pb-2 mb-2">
                        <h6 class="text-primary mb-0 fw-bold"><i class="fas fa-map-marker-alt me-1"></i> Residencia del Alumno</h6>
                    </div>
                    <div class="col-12 form-group">
                        <label for="direccion_alumno" class="form-label fw-semibold">Dirección de Habitación del Alumno</label>
                        <textarea class="form-control" id="direccion_alumno" name="direccion_alumno" rows="2" required>{{ old('direccion_alumno', $alumno->direccion_alumno) }}</textarea>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12 border-bottom pb-2 mb-3">
                        <h6 class="text-danger mb-0 fw-bold"><i class="fas fa-pen-nib me-1"></i> Firma de Autorización del Responsable</h6>
                    </div>
                    <div class="col-12">
                        <div class="card bg-light p-3 mx-auto text-center" style="max-width: 500px; border: 2px dashed #ccc;">
                            <span class="text-muted small d-block mb-2">Use el dedo o un lápiz óptico sobre el recuadro blanco para firmar:</span>
                            
                            <canvas id="canvasFirma" class="border bg-white rounded shadow-sm w-100" style="height: 180px; touch-action: none;"></canvas>
                            <input type="hidden" name="firma_autorizacion_base64" id="firma_autorizacion_base64">
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="small text-secondary">
                                    @if(!empty($alumno->encargadoPrincipal->firma_autorizacion))
                                        <i class="fas fa-check text-success"></i> Ya existe una firma guardada.
                                    @endif
                                </span>
                                <button type="button" id="btnLimpiarFirma" class="btn btn-warning btn-sm fw-semibold">
                                    <i class="fas fa-eraser"></i> Limpiar Pizarra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end border-top pt-3">
                    <a href="{{ route('estudiante.informacion.index') }}" class="btn btn-light border px-4 mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4 fw-semibold">
                        <i class="fas fa-save me-1"></i> Guardar Todo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- 1. Previsualización de Foto ---
        const inputFoto = document.getElementById('inputFoto');
        const previewFoto = document.getElementById('previewFoto');

        inputFoto.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { previewFoto.src = e.target.result; }
                reader.readAsDataURL(file);
            }
        });

        // --- 2. Configuración de SignaturePad (Firma) ---
        const canvas = document.getElementById('canvasFirma');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 128)' 
        });

        function redimensionarCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear(); 
        }

        window.onresize = redimensionarCanvas;
        redimensionarCanvas();

        document.getElementById('btnLimpiarFirma').addEventListener('click', function() {
            signaturePad.clear();
        });

        // --- 3. Validación al enviar el Formulario ---
        document.getElementById('formInformacion').addEventListener('submit', function(e) {
            if (!signaturePad.isEmpty()) {
                document.getElementById('firma_autorizacion_base64').value = signaturePad.toDataURL();
            } else {
                @if(empty($alumno->encargadoPrincipal->firma_autorizacion))
                    e.preventDefault();
                    alert('Por favor, solicite al responsable que estampe su firma de autorización antes de guardar.');
                @endif
            }
        });
    });
</script>
@endsection