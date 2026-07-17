@extends('layouts.app') 

@section('css')
<!-- Cargamos la hoja de estilos CSS de Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
    /* Estilos para asegurar que la imagen de vista previa en el modal no se desborde */
    .img-container-crop {
        max-height: 450px;
        width: 100%;
        text-align: center;
        background-color: #f7f7f7;
    }
    .img-container-crop img {
        max-width: 100%;
        max-height: 400px;
    }
</style>
@endsection

@section('content')
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
                                @if(!empty($alumno->foto) && trim($alumno->foto) !== 'foto_no_disponible.jpg')
                                    <img id="previewFoto" src="{{ asset('fotos_origen/' . trim($alumno->foto)) }}" 
                                         alt="Foto Alumno" class="img-fluid h-100 w-100" style="object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ asset('img_central/' . (trim($alumno->codigo_genero) == '02' ? 'avatar_femenino.png' : 'avatar_masculino.png')) }}';">
                                @else
                                    @if(trim($alumno->codigo_genero) == '02')
                                        <img id="previewFoto" src="{{ asset('img_central/avatar_femenino.png') }}" alt="Avatar Femenino" class="img-fluid h-100 w-100" style="object-fit: cover;">
                                    @else
                                        <img id="previewFoto" src="{{ asset('img_central/avatar_masculino.png') }}" alt="Avatar Masculino" class="img-fluid h-100 w-100" style="object-fit: cover;">
                                    @endif
                                @endif
                            </div>

                            <div class="ml-md-3 mt-3 mt-md-0">
                                <!-- Modificamos el evento 'onchange' para abrir nuestro modal de recorte -->
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
                        <textarea class="form-control" id="direccion_alumno" name="direccion_alumno" rows="2" required>{{ old('direccion_alumno', $alumno->direccion_alumno ?? '') }}</textarea>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12 border-bottom pb-2 mb-3">
                        <h6 class="text-danger mb-0 fw-bold"><i class="fas fa-pen-nib me-1"></i> Firma de Autorización del Responsable</h6>
                    </div>
                    <div class="col-12">
                        <div class="card bg-light p-3 mx-auto text-center" style="max-width: 500px; border: 2px dashed #ccc;">
                            <span class="text-muted small d-block mb-2">Use el dedo o un lápiz óptico sobre el recuadro blanco para firmar:</span>
                            
                            <div class="position-relative bg-white border rounded shadow-sm w-100 mb-2" style="height: 180px; overflow: hidden;">
                                @if(!empty($alumno->encargadoPrincipal->firma_autorizacion))
                                    <img id="firmaGuardadaPreview" src="{{ $alumno->encargadoPrincipal->firma_autorizacion }}" class="position-absolute" style="max-height: 90%; max-width: 90%; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1; pointer-events: none; opacity: 0.85;">
                                @endif
                                <canvas id="canvasFirma" width="600" height="300" style="width: 100%; height: 250px; background-color: #fff; border: 1px solid #ced4da; border-radius: .25rem; cursor: crosshair;"></canvas>
                            </div>
                            
                            <input type="hidden" name="firma_autorizacion_base64" id="firma_autorizacion_base64" value="{{ old('firma_autorizacion_base64', $alumno->encargadoPrincipal->firma_autorizacion ?? '') }}" autocomplete="off">
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="small text-secondary" id="estadoFirmaTexto">
                                    @if(!empty($alumno->encargadoPrincipal->firma_autorizacion))
                                        <i class="fas fa-check text-success"></i> Ya existe una firma guardada.
                                    @else
                                        <i class="fas fa-info-circle text-muted"></i> Sin firma registrada.
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

<!-- ================= MODAL PARA RECORTE DE FOTO ================= -->
<div class="modal fade" id="modalRecortarFoto" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalRecortarFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalRecortarFotoLabel"><i class="fas fa-crop-alt me-2"></i> Recortar Fotografía del Estudiante</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="btnCerrarModalX">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="img-container-crop">
                    <img id="imagenPreviaCrop" src="" alt="Cargando imagen...">
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnCancelarRecorte">Cancelar</button>
                <button type="button" class="btn btn-primary fw-semibold" id="btnAplicarRecorte">
                    <i class="fas fa-check me-1"></i> Aplicar Recorte
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Cargamos la biblioteca Cropper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<script>
    (function() {
        document.addEventListener("DOMContentLoaded", function() {
            
            // --- ELEMENTOS DE FIRMA DIGITAL ---
            const canvasElement = document.getElementById('canvasFirma');
            const hiddenInputFirma = document.getElementById('firma_autorizacion_base64');
            const imgViejaPreview = document.getElementById('firmaGuardadaPreview');
            
            if (canvasElement) {
                const ctxCanvas = canvasElement.getContext('2d');
                let isDrawing = false;
                let isCanvasEmpty = true; 

                if (hiddenInputFirma && hiddenInputFirma.value.trim() !== '') {
                    isCanvasEmpty = false;
                }

                ctxCanvas.strokeStyle = "#000080"; 
                ctxCanvas.lineWidth = 3; 
                ctxCanvas.lineJoin = "round";
                ctxCanvas.lineCap = "round";

                function getCoordinates(e) {
                    const rect = canvasElement.getBoundingClientRect();
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    return {
                        x: (clientX - rect.left) * (canvasElement.width / rect.width),
                        y: (clientY - rect.top) * (canvasElement.height / rect.height)
                    };
                }

                function startDrawing(e) {
                    isDrawing = true;
                    isCanvasEmpty = false;
                    if (imgViejaPreview) {
                        imgViejaPreview.style.display = 'none';
                    }
                    const pos = getCoordinates(e);
                    ctxCanvas.beginPath();
                    ctxCanvas.moveTo(pos.x, pos.y);
                    if (e.touches) e.preventDefault();
                }

                function draw(e) {
                    if (!isDrawing) return;
                    const pos = getCoordinates(e);
                    ctxCanvas.lineTo(pos.x, pos.y);
                    ctxCanvas.stroke();
                    if (e.touches) e.preventDefault();
                }

                function stopDrawing() {
                    isDrawing = false;
                    ctxCanvas.closePath();
                }

                canvasElement.addEventListener('mousedown', startDrawing);
                canvasElement.addEventListener('mousemove', draw);
                window.addEventListener('mouseup', stopDrawing);

                canvasElement.addEventListener('touchstart', startDrawing, { passive: false });
                canvasElement.addEventListener('touchmove', draw, { passive: false });
                canvasElement.addEventListener('touchend', stopDrawing);

                const btnClear = document.getElementById('btnLimpiarFirma');
                if (btnClear) {
                    btnClear.addEventListener('click', function() {
                        ctxCanvas.clearRect(0, 0, canvasElement.width, canvasElement.height);
                        if (hiddenInputFirma) hiddenInputFirma.value = '';
                        if (imgViejaPreview) imgViejaPreview.style.display = 'none';
                        isCanvasEmpty = true; 
                        const txtStatus = document.getElementById('estadoFirmaTexto');
                        if (txtStatus) {
                            txtStatus.innerHTML = '<i class="fas fa-info-circle text-warning"></i> Pizarra limpia. Ingrese la nueva firma.';
                        }
                    });
                }

                const formContainer = document.getElementById('formInformacion');
                if (formContainer) {
                    formContainer.addEventListener('submit', function(e) {
                        if (!isCanvasEmpty) {
                            hiddenInputFirma.value = canvasElement.toDataURL('image/png');
                        } else {
                            @if(empty($alumno->encargadoPrincipal->firma_autorizacion))
                                e.preventDefault();
                                alert('Por favor, solicite al responsable que estampe su firma de autorización antes de guardar.');
                            @endif
                        }
                    });
                }
            }

            // ================= LÓGICA DE RECORTE (CROPPER.JS) =================
            const inputFoto = document.getElementById('inputFoto');
            const previewFoto = document.getElementById('previewFoto');
            const imagenPreviaCrop = document.getElementById('imagenPreviaCrop');
            const btnAplicarRecorte = document.getElementById('btnAplicarRecorte');
            const btnCancelarRecorte = document.getElementById('btnCancelarRecorte');
            const btnCerrarModalX = document.getElementById('btnCerrarModalX');
            
            let cropper = null;
            let originalFileName = "estudiante.jpg";

            // Escuchar la selección de una nueva foto (o disparo de la cámara)
            if (inputFoto) {
                inputFoto.addEventListener('change', function(e) {
                    const archivos = e.target.files;
                    if (archivos && archivos.length > 0) {
                        const file = archivos[0];
                        originalFileName = file.name;
                        const fileReader = new FileReader();
                        
                        fileReader.onload = function(evt) {
                            // Asignamos la imagen al contenedor dentro del modal
                            imagenPreviaCrop.src = evt.target.result;
                            
                            // Abrimos el modal de Bootstrap manualmente
                            $('#modalRecortarFoto').modal('show');
                        };
                        fileReader.readAsDataURL(file);
                    }
                });
            }

            // Inicializar Cropper cuando el modal se termine de mostrar en pantalla
            $('#modalRecortarFoto').on('shown.bs.modal', function() {
                cropper = new Cropper(imagenPreviaCrop, {
                    aspectRatio: 140 / 160, // Sincronizado con las dimensiones de tu visualizador (140px x 160px)
                    viewMode: 1,            // Evita que el contenedor de recorte se salga de la imagen
                    dragMode: 'move',       // Permite arrastrar la imagen de fondo
                    autoCropArea: 0.85,     // Cubre el 85% de la imagen al inicio
                    restore: false,
                    guides: true,           // Muestra líneas de guía internas
                    center: true,           // Muestra el indicador central
                    highlight: false,
                    cropBoxMovable: true,   // El cuadro se puede mover
                    cropBoxResizable: true, // El cuadro se puede redimensionar
                    toggleDragModeOnDblclick: false,
                });
            }).on('hidden.bs.modal', function() {
                // Destruimos el objeto cropper para evitar fugas de memoria al cerrar el modal
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                imagenPreviaCrop.src = "";
            });

            // Acción: Presionar "Aplicar Recorte"
            if (btnAplicarRecorte) {
                btnAplicarRecorte.addEventListener('click', function() {
                    if (!cropper) return;

                    // Obtenemos el Canvas recortado con la resolución recomendada
                    const canvasRecortado = cropper.getCycledCanvas ? cropper.getCycledCanvas() : cropper.getCroppedCanvas({
                        width: 420,  // Escalado a 3 veces el ancho del preview para mantener excelente nitidez
                        height: 480, // Escalado a 3 veces el alto del preview
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high'
                    });

                    if (canvasRecortado) {
                        // 1. Mostrar de inmediato la previsualización en el formulario principal
                        previewFoto.src = canvasRecortado.toDataURL('image/jpeg', 0.90);

                        // 2. Convertir el canvas a un Blob virtual tipo archivo
                        canvasRecortado.toBlob(function(blob) {
                            if (blob) {
                                // Creamos un objeto File real a partir del Blob
                                const croppedFile = new File([blob], originalFileName, { 
                                    type: 'image/jpeg', 
                                    lastModified: Date.now() 
                                });

                                // Inyectamos el nuevo archivo recortado al input real del formulario
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(croppedFile);
                                inputFoto.files = dataTransfer.files;

                                console.log("Fotografía recortada con éxito e inyectada al input del formulario.");
                            }
                        }, 'image/jpeg', 0.90); // Guardamos como JPEG con 90% de calidad
                    }

                    // Cerramos el modal
                    $('#modalRecortarFoto').modal('hide');
                });
            }

            // Si cancela, limpiamos el input para que pueda volver a elegir el mismo archivo si lo desea
            function limpiarInputFoto() {
                if (inputFoto) inputFoto.value = "";
            }
            if (btnCancelarRecorte) btnCancelarRecorte.addEventListener('click', limpiarInputFoto);
            if (btnCerrarModalX) btnCerrarModalX.addEventListener('click', limpiarInputFoto);
        });
    })();
</script>
@endsection