@extends('layouts.app')

@section('content')
<!-- Inclusión de SweetAlert2 via CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Estilo diferenciado para los labels numéricos */
    .label-numeral {
        color: #1a5276;
        font-weight: 700;
        font-size: 0.95rem;
        display: block;
        margin-bottom: 0.4rem;
    }
</style>

<!-- ================================================================= -->
<!-- SECCIÓN A. INFORMACIÓN DE LA INSTITUCIÓN Y ACADÉMICA (SÓLO LECTURA) -->
<!-- ================================================================= -->
<div class="card shadow-lg border-0 mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-school me-2"></i> A. INFORMACIÓN DE LA INSTITUCIÓN Y ACADÉMICA
        </h5>
        <!-- Botón para Imprimir PDF -->
        <a href="{{ route('ficha.pdf', $alumno->id_alumno) }}" target="_blank" class="btn btn-danger btn-sm fw-bold me-2">
            <i class="fas fa-file-pdf me-1"></i> Imprimir Ficha (PDF)
        </a>

        <!-- Botón Volver a la Nómina reubicado -->
        <a href="{{ route('ficha.index') }}" class="btn btn-outline-light btn-sm fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Volver a la Nómina
        </a>
    </div>
    
    <div class="card-body bg-light p-4">
        <div class="row">
            <!-- Código de Infraestructura -->
            <div class="form-group col-md-3 mb-3">
                <label class="fw-bold text-secondary">Código Infraestructura:</label>
                <input type="text" class="form-control bg-white" value="{{ trim($institucion->codigo_institucion ?? '') }}" readonly>
            </div>

            <!-- Nombre de la Infraestructura / Institución -->
            <div class="form-group col-md-9 mb-3">
                <label class="fw-bold text-secondary">Nombre de la Infraestructura:</label>
                <input type="text" class="form-control bg-white fw-bold" value="{{ trim($institucion->nombre_institucion ?? '') }}" readonly>
            </div>

            <!-- Grado -->
            <div class="form-group col-md-4 mb-3">
                <label class="fw-bold text-secondary">Grado Académico:</label>
                <input type="text" class="form-control bg-white text-primary fw-bold" value="{{ trim($matricula->grado_nombre ?? 'N/A') }}" readonly>
            </div>

            <!-- Sección -->
            <div class="form-group col-md-4 mb-3">
                <label class="fw-bold text-secondary">Sección:</label>
                <input type="text" class="form-control bg-white text-primary fw-bold" value="{{ trim($matricula->seccion_nombre ?? 'N/A') }}" readonly>
            </div>

            <!-- Jornada / Turno -->
            <div class="form-group col-md-4 mb-3">
                <label class="fw-bold text-secondary">Jornada / Turno:</label>
                <input type="text" class="form-control bg-white text-primary fw-bold" value="{{ trim($matricula->turno_nombre ?? 'N/A') }}" readonly>
            </div>

            <!-- Departamento de la Institución -->
            <div class="form-group col-md-4 mb-2">
                <label class="fw-bold text-secondary">Departamento:</label>
                <input type="text" class="form-control bg-white" value="{{ trim($institucion->departamento_nombre ?? '') }}" readonly>
            </div>

            <!-- Municipio de la Institución -->
            <div class="form-group col-md-4 mb-2">
                <label class="fw-bold text-secondary">Municipio:</label>
                <input type="text" class="form-control bg-white" value="{{ trim($institucion->municipio_nombre ?? '') }}" readonly>
            </div>

            <!-- Distrito de la Institución -->
            <div class="form-group col-md-4 mb-2">
                <label class="fw-bold text-secondary">Distrito:</label>
                <input type="text" class="form-control bg-white" value="{{ trim($institucion->distrito_nombre ?? '') }}" readonly>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
  <form id="formFichaCompleta">
    @csrf
    @method('PUT')
    <input type="hidden" id="id_alumno" value="{{ $alumno->id_alumno }}">

    <!-- BARRA FLOTANTE CON EL BOTÓN PRINCIPAL DE GUARDADO -->
    <div class="card shadow-sm border-0 sticky-top mb-4 bg-white">
        <div class="card-body d-flex justify-content-between align-items-center py-2 px-4">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-edit me-2"></i> Edición de Ficha del Estudiante
            </h5>
            <button type="submit" class="btn btn-success btn-lg shadow fw-bold">
                <i class="fas fa-save me-2"></i> Guardar Toda la Ficha
            </button>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- B. IDENTIFICACIÓN DEL ESTUDIANTE -->
    <!-- ================================================================= -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i> B. IDENTIFICACIÓN DEL ESTUDIANTE (NUMERALES 1 AL 24)</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- 1. NIE -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">1. NIE:</label>
                    <input type="text" id="codigo_nie" name="codigo_nie" class="form-control" value="{{ trim($alumno->codigo_nie ?? '') }}" readonly>
                </div>

                <!-- 2. DUI -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">2. DUI:</label>
                    <input type="text" name="dui" class="form-control" value="{{ trim($alumno->dui ?? '') }}">
                </div>

                <!-- Pasaporte / Carné Residencia -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">2.5 Pasaporte / Carné Residencia:</label>
                    <input type="text" name="pasaporte" class="form-control" value="{{ trim($alumno->pasaporte ?? '') }}">
                </div>

                <!-- 3. Nombres -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">3. Nombres (según partida):</label>
                    <input type="text" name="nombre_completo" class="form-control" value="{{ trim($alumno->nombre_completo ?? '') }}" required>
                </div>

                <!-- 4. Apellidos -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">4.1 Primer Apellido:</label>
                    <input type="text" name="apellido_paterno" class="form-control" value="{{ trim($alumno->apellido_paterno ?? '') }}" required>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">4.2 Segundo Apellido:</label>
                    <input type="text" name="apellido_materno" class="form-control" value="{{ trim($alumno->apellido_materno ?? '') }}">
                </div>

                <!-- 5. Fecha Nacimiento -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">5. Fecha de Nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="{{ $alumno->fecha_nacimiento ?? '' }}">
                </div>

                <!-- 6. Nacionalidad -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">6. Nacionalidad:</label>
                    <select name="codigo_nacionalidad" class="form-control">
                        @foreach($nacionalidades as $nac)
                            <option value="{{ $nac->codigo }}" {{ ($alumno->codigo_nacionalidad ?? '') == $nac->codigo ? 'selected' : '' }}>
                                {{ trim($nac->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 7. Retornado -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">7. ¿Es estudiante retornado?</label>
                    <select name="retornado" class="form-control">
                        <option value="No" {{ ($alumno->retornado ?? '') == 'No' ? 'selected' : '' }}>NO</option>
                        <option value="Si" {{ ($alumno->retornado ?? '') == 'Si' ? 'selected' : '' }}>SÍ</option>
                    </select>
                </div>

                <!-- 8. Posee PN -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">8. ¿Posee Partida de Nac.?</label>
                    <select name="posee_pn" class="form-control">
                        <option value="Si" {{ ($alumno->posee_pn ?? '') == 'Si' ? 'selected' : '' }}>SÍ</option>
                        <option value="No" {{ ($alumno->posee_pn ?? '') == 'No' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 9. Presenta PN -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">9. ¿Presenta Partida de Nac.?</label>
                    <select name="presenta_pn" class="form-control">
                        <option value="Si" {{ ($alumno->presenta_pn ?? '') == 'Si' ? 'selected' : '' }}>SÍ</option>
                        <option value="No" {{ ($alumno->presenta_pn ?? '') == 'No' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 10. Género -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">10. Género / Sexo:</label>
                    <select name="codigo_genero" class="form-control">
                        <option value="01" {{ ($alumno->codigo_genero ?? '') == '01' ? 'selected' : '' }}>Hombre</option>
                        <option value="02" {{ ($alumno->codigo_genero ?? '') == '02' ? 'selected' : '' }}>Mujer</option>
                    </select>
                </div>

                <!-- 11. Etnia -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">11. Autoidentificación Étnica:</label>
                    <select name="codigo_etnia" class="form-control">
                        @foreach($etnias as $et)
                            <option value="{{ $et->codigo }}" {{ ($alumno->codigo_etnia ?? '') == $et->codigo ? 'selected' : '' }}>
                                {{ trim($et->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 12. Discapacidad -->
                <!-- 12. Condición de Discapacidad (Checkboxes dinámicos) -->
                <div class="form-group col-md-12 mb-3">
                    <label class="label-numeral mb-2">12. Condición de Discapacidad (Puede seleccionar más de una opción):</label>
                    
                    @php
                        // Convertimos el valor guardado en 'codigo_discapacidad' (ej: "01,03") en un array
                        $discapacidadesGuardadas = !empty($alumno->codigo_discapacidad) 
                            ? array_map('trim', explode(',', $alumno->codigo_discapacidad)) 
                            : [];
                    @endphp

                    <div class="row bg-light p-3 rounded border">
                        @foreach($discapacidades as $disc)
                            @php
                                $codigoLimpio = trim($disc->codigo);
                                $isChecked = in_array($codigoLimpio, $discapacidadesGuardadas);
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input chk-discapacidad" 
                                        type="checkbox" 
                                        name="codigo_discapacidad[]" 
                                        value="{{ $codigoLimpio }}" 
                                        id="disc_{{ $codigoLimpio }}"
                                        {{ $isChecked ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark" for="disc_{{ $codigoLimpio }}">
                                        <strong>[{{ $codigoLimpio }}]</strong> {{ trim($disc->nombre) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 13. Diagnóstico Clínico -->
                <div class="form-group col-md-6 mb-3">
                    <label class="label-numeral">13. Diagnóstico Clínico:</label>
                    <select name="codigo_diagnostico" class="form-control">
                        @foreach($diagnosticos as $diag)
                            <option value="{{ $diag->codigo }}" {{ ($alumno->codigo_diagnostico ?? '') == $diag->codigo ? 'selected' : '' }}>
                                {{ trim($diag->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

<!-- 14. Apoyo Educativo Especializado (Numeral 14 - Campo: codigo_apoyo_educativo) -->
<div class="form-group col-md-6 mb-3">
    <label class="label-numeral">14. Apoyo Educativo Especializado:</label>
    <select name="codigo_apoyo_educativo" class="form-control">
        <option value="">-- Seleccione --</option>
        @foreach($apoyosEducativos as $ap)
            <option value="{{ $ap->codigo }}" {{ ($alumno->codigo_apoyo_educativo ?? '') == $ap->codigo ? 'selected' : '' }}>
                {{ trim($ap->nombre ?? $ap->descripcion) }}
            </option>
        @endforeach
    </select>
</div>

<!-- 15. El estudiante recibe (Numeral 15 - Checkboxes dinámicos - Campo: codigo_recibe) -->
<div class="form-group col-md-12 mb-3">
    <label class="label-numeral mb-2">15. El estudiante recibe (Puede seleccionar más de una opción):</label>
    
    @php
        // Extraemos únicamente el campo 'codigo_recibe'
        $recibeGuardados = !empty($alumno->codigo_recibe) 
            ? array_map('trim', explode(',', $alumno->codigo_recibe)) 
            : [];
    @endphp

    <div class="row bg-light p-3 rounded border">
        @foreach($catalogoRecibe as $item)
            @php
                $codigoLimpio = trim($item->codigo);
                $isChecked = in_array($codigoLimpio, $recibeGuardados);
            @endphp
            <div class="col-md-6 col-lg-4 mb-2">
                <div class="form-check">
                    <input class="form-check-input chk-recibe" 
                           type="checkbox" 
                           name="codigo_recibe[]" 
                           value="{{ $codigoLimpio }}" 
                           id="recibe_{{ $codigoLimpio }}"
                           {{ $isChecked ? 'checked' : '' }}>
                    <label class="form-check-label text-dark" for="recibe_{{ $codigoLimpio }}">
                        <strong>[{{ $codigoLimpio }}]</strong> {{ trim($item->descripcion) }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>

                <!-- 16. Correo Electrónico -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">16. Correo Electrónico:</label>
                    <div class="input-group">
                        <input type="email" id="direccion_email" name="direccion_email" class="form-control" value="{{ trim($alumno->direccion_email ?? '') }}" placeholder="ejemplo@clases.edu.sv">
                        <button class="btn btn-outline-secondary" type="button" id="btnGenerarCorreo" title="Generar correo institucional">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>
                </div>

                <!-- 17. Teléfono -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">17. Teléfono Celular:</label>
                    <input type="text" name="telefono_celular" class="form-control" value="{{ trim($alumno->telefono_celular ?? $alumno->telefono_matricula ?? '') }}">
                </div>

                <!-- 18. WhatsApp -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">18. ¿Posee WhatsApp?</label>
                    <select name="whatsapp" class="form-control">
                        <option value="Si" {{ ($alumno->whatsapp ?? '') == 'Si' ? 'selected' : '' }}>SÍ</option>
                        <option value="No" {{ ($alumno->whatsapp ?? '') == 'No' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 19. Actividad Económica / Tipo de Trabajo (Checkboxes dinámicos) -->
                <div class="form-group col-md-12 mb-3">
                    <label class="label-numeral mb-2">19. ¿Tipo de trabajo / Actividad Económica? (Puede seleccionar más de una opción):</label>

                    @php
                        $actividadesGuardadas = !empty($alumno->codigo_actividad_economica) 
                            ? array_map('trim', explode(',', $alumno->codigo_actividad_economica)) 
                            : [];
                    @endphp

                    <div class="row bg-light p-3 rounded border">
                        @foreach($actividadesEconomicas as $act)
                            @php
                                $codigoLimpio = trim($act->codigo);
                                $isChecked = in_array($codigoLimpio, $actividadesGuardadas);
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input chk-actividad" 
                                        type="checkbox" 
                                        name="codigo_actividad_economica[]" 
                                        value="{{ $codigoLimpio }}" 
                                        id="act_{{ $codigoLimpio }}"
                                        {{ $isChecked ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark" for="act_{{ $codigoLimpio }}">
                                        <strong>[{{ $codigoLimpio }}]</strong> {{ trim($act->nombre) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 20. Estado Civil -->
                <div class="form-group col-md-6 mb-3">
                    <label class="label-numeral">20. Estado Civil:</label>
                    <select name="codigo_estado_civil" class="form-control">
                        @foreach($estadosCiviles as $ec)
                            <option value="{{ $ec->codigo }}" {{ ($alumno->codigo_estado_civil ?? '') == $ec->codigo ? 'selected' : '' }}>
                                {{ trim($ec->nombre) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 21. Convivencia / Estado Familiar -->
                <div class="form-group col-md-6 mb-3">
                    <label class="label-numeral">21. Convivencia / Estado Familiar:</label>
                    <select name="codigo_estado_familiar" class="form-control">
                        @foreach($estadosFamiliares as $ef)
                            <option value="{{ $ef->codigo }}" {{ ($alumno->codigo_estado_familiar ?? '') == $ef->codigo ? 'selected' : '' }}>
                                {{ trim($ef->nombre) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 22. Embarazo -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">22. ¿Está Embarazada?</label>
                    <select name="embarazada" class="form-control">
                        <option value="No" {{ ($alumno->embarazada ?? '') == 'No' ? 'selected' : '' }}>NO</option>
                        <option value="Si" {{ ($alumno->embarazada ?? '') == 'Si' ? 'selected' : '' }}>SÍ</option>
                        <option value="No Aplica" {{ ($alumno->embarazada ?? '') == 'No Aplica' ? 'selected' : '' }}>NO APLICA</option>
                    </select>
                </div>

                <!-- 23. Tiene Hijos -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">23. ¿Tiene Hijos/Hijas?</label>
                    <select name="tiene_hijos" class="form-control">
                        <option value="0" {{ !($alumno->tiene_hijos ?? false) ? 'selected' : '' }}>NO</option>
                        <option value="1" {{ ($alumno->tiene_hijos ?? false) ? 'selected' : '' }}>SÍ</option>
                    </select>
                </div>

                <!-- 24. Cantidad de Hijos -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">24. Cantidad de Hijos:</label>
                    <input type="number" name="cantidad_hijos" class="form-control" value="{{ $alumno->cantidad_hijos ?? 0 }}" min="0" max="10">
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- C. RESIDENCIA -->
    <!-- ================================================================= -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-home me-2"></i> C. RESIDENCIA DEL ESTUDIANTE (NUMERALES 25 AL 32)</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- 25. Zona de Residencia -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">25. Zona de Residencia:</label>
                    <select name="codigo_zona_residencia" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($zonasResidencia as $zona)
                            <option value="{{ $zona->codigo }}" {{ ($alumno->codigo_zona_residencia ?? '') == $zona->codigo ? 'selected' : '' }}>
                                {{ trim($zona->nombre) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 26. Tipo de Vivienda -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">26. Tipo de Vivienda:</label>
                    <select name="codigo_tipo_vivienda" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($tiposVivienda as $tv)
                            <option value="{{ $tv->codigo }}" {{ ($alumno->codigo_tipo_vivienda ?? '') == $tv->codigo ? 'selected' : '' }}>
                                {{ trim($tv->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 27. Departamento -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">27. Departamento:</label>
                    <select id="select_departamento" name="codigo_departamento" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep->codigo }}" {{ ($alumno->codigo_departamento ?? '') == $dep->codigo ? 'selected' : '' }}>
                                {{ trim($dep->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 28. Municipio -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">28. Municipio:</label>
                    <select id="select_municipio" name="codigo_municipio" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($municipios as $mun)
                            <option value="{{ $mun->codigo }}" {{ ($alumno->codigo_municipio ?? '') == $mun->codigo ? 'selected' : '' }}>
                                {{ trim($mun->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 29. Distrito -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">29. Distrito:</label>
                    <select id="select_distrito" name="codigo_distrito" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($distritos as $dist)
                            <option value="{{ $dist->codigo }}" {{ ($alumno->codigo_distrito ?? '') == $dist->codigo ? 'selected' : '' }}>
                                {{ trim($dist->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 30. Cantón -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">30. Cantón:</label>
                    <select id="select_canton" name="codigo_canton" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($cantones as $cant)
                            <option value="{{ trim($cant->codigo) }}" {{ trim($alumno->codigo_canton ?? '') == trim($cant->codigo) ? 'selected' : '' }}>
                                {{ trim($cant->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 31. Caserío -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">31. Caserío:</label>
                    <input type="text" name="caserio" class="form-control" value="{{ trim($alumno->caserio ?? '') }}" maxlength="100" placeholder="Nombre del caserío">
                </div>

                <!-- 32. Dirección Completa -->
                <div class="form-group col-md-12 mb-3">
                    <label class="label-numeral">32. Dirección de Residencia:</label>
                    <textarea name="direccion_alumno" class="form-control" rows="2" placeholder="Calle, pasaje, número de casa, punto de referencia...">{{ trim($alumno->direccion_alumno ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- D. SERVICIOS BÁSICOS -->
    <!-- ================================================================= -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-faucet me-2"></i> D. SERVICIOS BÁSICOS DEL ESTUDIANTE (NUMERALES 33 AL 35)</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- 33. Energía Eléctrica -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">33. ¿Cuenta con servicio de energía eléctrica en su casa?:</label>
                    <select name="servicio_energia" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="Si" {{ trim($alumno->servicio_energia ?? '') == 'Si' ? 'selected' : '' }}>SI</option>
                        <option value="No" {{ trim($alumno->servicio_energia ?? '') == 'No' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 34. Recolección de Basura -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">34. ¿Cuenta con servicio de recolección de basura?:</label>
                    <select name="recoleccion_basura" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="Si" {{ trim($alumno->recoleccion_basura ?? '') == 'Si' ? 'selected' : '' }}>SI</option>
                        <option value="No" {{ trim($alumno->recoleccion_basura ?? '') == 'No' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 35. Fuente de Abastecimiento de Agua -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">35. Fuente principal de abastecimiento de agua:</label>
                    <select name="codigo_abastecimiento" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($abastecimientosAgua as $abast)
                            <option value="{{ trim($abast->codigo) }}" {{ trim($alumno->codigo_abastecimiento ?? '') == trim($abast->codigo) ? 'selected' : '' }}>
                                {{ trim($abast->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- E. SERVICIOS DE COMUNICACIÓN -->
    <!-- ================================================================= -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-wifi me-2"></i> E. SERVICIOS DE COMUNICACIÓN DEL ESTUDIANTE (NUMERALES 36 AL 44)</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- 36. Acceso a Internet -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">36. Acceso a Internet:</label>
                    <select name="acceso_internet" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="SI" {{ trim($alumno->acceso_internet ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ trim($alumno->acceso_internet ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 37. Conexión a Internet Residencial -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">37. ¿Tiene conexión a internet residencial?:</label>
                    <select id="select_conexion_residencial" name="tipo_conexion_internet" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="SI" {{ trim($alumno->tipo_conexion_internet ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ trim($alumno->tipo_conexion_internet ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 38. Compañía de Internet -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">38. ¿Con cuál compañía?:</label>
                    <select id="select_company_internet" name="codigo_tipo_conexion_internet_company" class="form-control" {{ trim($alumno->tipo_conexion_internet ?? '') != 'SI' ? 'disabled' : '' }}>
                        <option value="">-- Seleccione --</option>
                        @foreach($companiasInternet as $comp)
                            <option value="{{ trim($comp->codigo) }}" {{ trim($alumno->codigo_tipo_conexion_internet_company ?? '') == trim($comp->codigo) ? 'selected' : '' }}>
                                {{ trim($comp->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 39. Posee Radio -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">39. ¿Posee Radio?:</label>
                    <select name="posee_radio" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="SI" {{ trim($alumno->posee_radio ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ trim($alumno->posee_radio ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 40. Posee TV -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">40. ¿Posee TV?:</label>
                    <select name="posee_tv" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="SI" {{ trim($alumno->posee_tv ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ trim($alumno->posee_tv ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 41. Sintoniza Canal 10 -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">41. ¿Sintoniza Canal 10?:</label>
                    <select name="sintoniza_canal_10" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="SI" {{ trim($alumno->sintoniza_canal_10 ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ trim($alumno->sintoniza_canal_10 ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                        <option value="NO APLICA" {{ trim($alumno->sintoniza_canal_10 ?? '') == 'NO APLICA' ? 'selected' : '' }}>NO APLICA</option>
                    </select>
                </div>

                <!-- 42. Posee Computadora -->
                <div class="form-group col-md-3 mb-3">
                    <label class="label-numeral">42. ¿Posee Computadora?:</label>
                    <select name="posee_computadora" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="SI" {{ trim($alumno->posee_computadora ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ trim($alumno->posee_computadora ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 43. Modalidad de Clases -->
                <div class="form-group col-md-6 mb-3">
                    <label class="label-numeral">43. Modalidad en que recibe clases:</label>
                    <select name="codigo_clases_bajo_modalidad" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($modalidadesClase as $mod)
                            <option value="{{ trim($mod->codigo) }}" {{ trim($alumno->codigo_clases_bajo_modalidad ?? '') == trim($mod->codigo) ? 'selected' : '' }}>
                                {{ trim($mod->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 44. Canales de Atención -->
                    <!-- 42. Canales de Atención (Checkboxes dinámicos) -->
                    <div class="form-group col-md-12 mb-3">
                        <label class="label-numeral mb-2">44. Canales de atención utilizados para recibir clases (Puede seleccionar más de una opción):</label>

                        @php
                            $canalesGuardados = !empty($alumno->codigo_clases_canales_atencion) 
                                ? array_map('trim', explode(',', $alumno->codigo_clases_canales_atencion)) 
                                : [];
                        @endphp

                        <div class="row bg-light p-3 rounded border">
                            @foreach($canalesAtencion as $canal)
                                @php
                                    $codigoLimpio = trim($canal->codigo);
                                    $isChecked = in_array($codigoLimpio, $canalesGuardados);
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input chk-canal" 
                                            type="checkbox" 
                                            name="codigo_clases_canales_atencion[]" 
                                            value="{{ $codigoLimpio }}" 
                                            id="canal_{{ $codigoLimpio }}"
                                            {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark" for="canal_{{ $codigoLimpio }}">
                                            <strong>[{{ $codigoLimpio }}]</strong> {{ trim($canal->descripcion) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- F. SERVICIO SOCIAL -->
    <!-- ================================================================= -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> F. SERVICIO SOCIAL - SOLO EDUCACIÓN MEDIA (NUMERALES 45 AL 48)</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- 45. Realización de Servicio Social -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">45. ¿Ha realizado las horas de servicio social?:</label>
                    <select id="select_servicio_social" name="servicio_social_realizado" class="form-control">
                        <option value="">-- Seleccione --</option>
                        <option value="SI" {{ trim($alumno->servicio_social_realizado ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ trim($alumno->servicio_social_realizado ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                </div>

                <!-- 46. Fecha Finalización -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">46. Fecha finalización del servicio social:</label>
                    <input type="date" id="input_fecha_social" name="servicio_social_fecha_finalizado" 
                           class="form-control" 
                           value="{{ $alumno->servicio_social_fecha_finalizado ?? '' }}" 
                           {{ trim($alumno->servicio_social_realizado ?? '') != 'SI' ? 'disabled' : '' }}>
                </div>

                <!-- 47. Cantidad de Horas -->
                <div class="form-group col-md-4 mb-3">
                    <label class="label-numeral">47. Cantidad de horas:</label>
                    <input type="number" id="input_horas_social" name="servicio_social_horas" 
                           class="form-control" min="0" placeholder="Ej. 150" 
                           value="{{ trim($alumno->servicio_social_horas ?? '') }}" 
                           {{ trim($alumno->servicio_social_realizado ?? '') != 'SI' ? 'disabled' : '' }}>
                </div>

                <!-- 48. Descripción -->
                <div class="form-group col-md-12 mb-3">
                    <label class="label-numeral">48. Descripción del servicio social:</label>
                    <textarea id="input_desc_social" name="servicio_social_descripcion" 
                              class="form-control" rows="3" 
                              placeholder="Detalle la institución o proyecto donde realizó el servicio social..." 
                              {{ trim($alumno->servicio_social_realizado ?? '') != 'SI' ? 'disabled' : '' }}>{{ trim($alumno->servicio_social_descripcion ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTÓN INFERIOR DE GUARDADO -->
    <div class="text-right mb-5">
        <button type="submit" class="btn btn-success btn-lg px-5 shadow fw-bold">
            <i class="fas fa-save me-2"></i> Guardar Toda la Ficha del Estudiante
        </button>
    </div>
</form>

<!-- ==================================================== -->
<!-- SECCIÓN G. DATOS DEL RESPONSABLE (NUMERALES 49 AL 56) -->
<!-- ==================================================== -->
<div class="card shadow-lg border-0 mt-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i> G. DATOS DEL RESPONSABLE (NUMERALES 49 AL 56)</h5>
        
        <!-- Indicador de estado de registros -->
        <span class="badge bg-light text-dark fw-bold">
            Registrados: {{ count($encargados) }} / 3
        </span>
    </div>
    
    <div class="card-body p-4">

        {{-- Alerta si faltan registros por completar --}}
        @if(count($encargados) < 3)
            <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Atención:</strong> Ha registrado {{ count($encargados) }} responsable(s). Puede registrar hasta 3 familiares/encargados.
                </div>
                <button type="button" class="btn btn-sm btn-primary fw-bold" data-toggle="modal" data-target="#modalNuevoResponsable">
                    <i class="fas fa-plus-circle me-1"></i> Agregar Familiar
                </button>
            </div>
        @endif

        @if(count($encargados) > 0)
            <!-- Navegación por pestañas (*Tabs*) -->
            <ul class="nav nav-tabs mb-3" id="tabResponsables" role="tablist">
                @foreach($encargados as $index => $enc)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index == 0 ? 'active' : '' }}" 
                                id="tab-encargado-{{ $enc->id_alumno_encargado }}" 
                                data-bs-toggle="tab" 
                                data-bs-target="#encargado-{{ $enc->id_alumno_encargado }}" 
                                type="button" role="tab">
                            
                            @if($enc->encargado)
                                <i class="fas fa-star text-warning me-1" title="Responsable Principal"></i>
                            @else
                                <i class="fas fa-user me-1"></i>
                            @endif

                            {{ $enc->nombres ? trim($enc->nombres) : 'Familiar #'.($index+1) }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- Contenido de las pestañas -->
            <div class="tab-content" id="tabResponsablesContent">
                @foreach($encargados as $index => $enc)
                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" 
                         id="encargado-{{ $enc->id_alumno_encargado }}" 
                         role="tabpanel">
                        
                        <form class="formGuardarResponsable">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id_alumno_encargado" value="{{ $enc->id_alumno_encargado }}">

                            <div class="row">
                                <!-- Marcar como Responsable Principal -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch bg-light p-2 rounded border">
                                        <input class="form-check-input ms-2" type="checkbox" name="encargado" id="chk_encargado_{{ $enc->id_alumno_encargado }}" {{ $enc->encargado ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold ms-2" for="chk_encargado_{{ $enc->id_alumno_encargado }}">
                                            Designar como Responsable Principal del Estudiante
                                        </label>
                                    </div>
                                </div>

                                <!-- 49. Nº DUI -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">49. Nº DUI:</label>
                                    <input type="text" name="dui" class="form-control" placeholder="00000000-0" value="{{ trim($enc->dui ?? '') }}">
                                </div>

                                <!-- 50. Nº Pasaporte/Otro -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">50. Nº Pasaporte / Otro:</label>
                                    <input type="text" name="pasaporte_otro" class="form-control" value="{{ trim($enc->pasaporte_otro ?? '') }}">
                                </div>

                                <!-- 51. Tipo Parentesco -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">51. Parentesco:</label>
                                    <select name="codigo_familiar" class="form-control">
                                        <option value="">-- Seleccione --</option>
                                        @foreach($parentescos as $par)
                                            <option value="{{ trim($par->codigo) }}" {{ trim($enc->codigo_familiar ?? '') == trim($par->codigo) ? 'selected' : '' }}>
                                                {{ trim($par->descripcion) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 52. Nombre Completo -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">52. Nombre Completo:</label>
                                    <input type="text" name="nombres" class="form-control" value="{{ trim($enc->nombres ?? '') }}">
                                </div>

                                <!-- 53. Teléfono -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">53. Teléfono:</label>
                                    <input type="text" name="telefono" class="form-control" value="{{ trim($enc->telefono ?? '') }}">
                                </div>

                                <!-- 54. Teléfono Alternativo -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">54. Teléfono Alternativo:</label>
                                    <input type="text" name="telefono_alternativo" class="form-control" value="{{ trim($enc->telefono_alternativo ?? '') }}">
                                </div>

                                <!-- 55. Correo Electrónico -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">55. Correo Electrónico:</label>
                                    <input type="email" name="correo_electronico" class="form-control" value="{{ trim($enc->correo_electronico ?? '') }}">
                                </div>

                                <!-- 56. ÚLTIMO GRADO DE ESCOLARIDAD -->
                                <div class="form-group col-md-3 mb-3">
                                    <label class="label-numeral">56. Grado Escolaridad:</label>
                                    <select name="codigo_ultimo_grado_aprobado" class="form-control">
                                        <option value="">-- Seleccione --</option>
                                        @foreach($gradosEscolaridad as $gra)
                                            <option value="{{ trim($gra->codigo) }}" {{ trim($enc->codigo_ultimo_grado_aprobado ?? '') == trim($gra->codigo) ? 'selected' : '' }}>
                                                {{ trim($gra->descripcion) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="text-right mt-3">
                                <button type="submit" class="btn btn-primary shadow fw-bold">
                                    <i class="fas fa-save me-1"></i> Guardar Cambios de este Responsable
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <p class="text-muted">No hay información de responsables registrada para este estudiante.</p>
                <button type="button" class="btn btn-primary fw-bold" data-toggle="modal" data-target="#modalNuevoResponsable">
                    <i class="fas fa-plus-circle me-1"></i> Registrar Primer Responsable
                </button>
            </div>
        @endif
    </div>
</div>

<!-- ==================================================== -->
<!-- MODAL PARA AGREGAR NUEVO RESPONSABLE FALTANTE        -->
<!-- ==================================================== -->
<div class="modal fade" id="modalNuevoResponsable" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Registrar Nuevo Familiar/Responsable</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNuevoResponsable">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="form-group col-md-4 mb-3">
                            <label>49. Nº DUI:</label>
                            <input type="text" name="dui" class="form-control">
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <label>50. Nº Pasaporte / Otro:</label>
                            <input type="text" name="pasaporte_otro" class="form-control">
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <label>51. Parentesco:</label>
                            <select name="codigo_familiar" class="form-control">
                                <option value="">-- Seleccione --</option>
                                @foreach($parentescos as $par)
                                    <option value="{{ trim($par->codigo) }}">{{ trim($par->descripcion) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label>52. Nombre Completo:</label>
                            <input type="text" name="nombres" class="form-control" required>
                        </div>
                        <div class="form-group col-md-3 mb-3">
                            <label>53. Teléfono:</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="form-group col-md-3 mb-3">
                            <label>54. Teléfono Alt.:</label>
                            <input type="text" name="telefono_alternativo" class="form-control">
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label>55. Correo Electrónico:</label>
                            <input type="email" name="correo_electronico" class="form-control">
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label>56. Grado Escolaridad:</label>
                            <select name="codigo_ultimo_grado_aprobado" class="form-control">
                                <option value="">-- Seleccione --</option>
                                @foreach($gradosEscolaridad as $gra)
                                    <option value="{{ trim($gra->codigo) }}">{{ trim($gra->descripcion) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Guardar Nuevo Responsable</button>
                </div>
            </form>
        </div>
    </div>
</div>


</div> <!-- CONTENEDOR PRINCIPAL -->




@endsection

@section('scripts')
<script>
$(document).ready(function() {

    // Lógica para autocompletar el correo institucional
    function verificarCorreoInstitucional() {
        let correoInput = $('#direccion_email');
        let nie = $('#codigo_nie').val().trim();

        if (correoInput.val().trim() === '' && nie !== '') {
            correoInput.val(nie + '@clases.edu.sv');
        }
    }

    // Evento al presionar el botón de sugerencia de correo
    $('#btnGenerarCorreo').click(function() {
        verificarCorreoInstitucional();
    });

    // Envío del formulario mediante AJAX
    $('#formLiteralB').submit(function(e) {
        e.preventDefault();
        
        verificarCorreoInstitucional(); 
        let id_alumno = $('#id_alumno').val();

        // Generación de la URL exacta de Laravel reemplazando el comodín :id
        let urlGuardar = "{{ route('ficha.guardar-literal-b', ':id') }}";
        urlGuardar = urlGuardar.replace(':id', id_alumno);

        // Indicador de carga con SweetAlert2
        Swal.fire({
            title: 'Guardando datos...',
            text: 'Por favor espere mientras se procesa la información.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: urlGuardar,
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado Correctamente!',
                    text: response.message,
                    confirmButtonColor: '#3085d6'
                });
            },
            error: function(xhr) {
                let mensajeError = 'Ocurrió un error inesperado en el servidor.';

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let listaErrores = '<ul style="text-align: left;">';
                    $.each(xhr.responseJSON.errors, function(index, error) {
                        listaErrores += '<li>' + error + '</li>';
                    });
                    listaErrores += '</ul>';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos requeridos o con error:',
                        html: listaErrores,
                        confirmButtonColor: '#d33'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al guardar (' + xhr.status + ')',
                        text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : mensajeError,
                        confirmButtonColor: '#d33'
                    });
                }
            }
        });
    });
});

// ==========================================
// LÓGICA SELECTS DEPENDIENTES DE RESIDENCIA
// ==========================================

// 1. Cambio de Departamento -> Carga Municipios
$('#select_departamento').change(function() {
    let dep = $(this).val();
    
    // Limpieza de selects inferiores
    $('#select_municipio').html('<option value="">-- Seleccione --</option>');
    $('#select_distrito').html('<option value="">-- Seleccione --</option>');
    $('#select_canton').html('<option value="">-- Seleccione --</option>');

    if (dep) {
        // Generación de URL absoluta por Blade reemplazando el comodín :dep
        let urlMunicipios = "{{ route('ficha.municipios', ':dep') }}";
        urlMunicipios = urlMunicipios.replace(':dep', dep);

        $.get(urlMunicipios, function(data) {
            $.each(data, function(index, item) {
                $('#select_municipio').append(
                    '<option value="' + item.codigo + '">' + item.descripcion.trim() + '</option>'
                );
            });
        });
    }
});

// 2. Cambio de Municipio -> Carga Distritos
$('#select_municipio').change(function() {
    let dep = $('#select_departamento').val();
    let mun = $(this).val();

    $('#select_distrito').html('<option value="">-- Seleccione --</option>');
    $('#select_canton').html('<option value="">-- Seleccione --</option>');

    if (dep && mun) {
        // Generación de URL con dos parámetros reemplazando :dep y :mun
        let urlDistritos = "{{ route('ficha.distritos', [':dep', ':mun']) }}";
        urlDistritos = urlDistritos.replace(':dep', dep).replace(':mun', mun);

        $.get(urlDistritos, function(data) {
            $.each(data, function(index, item) {
                $('#select_distrito').append(
                    '<option value="' + item.codigo + '">' + item.descripcion.trim() + '</option>'
                );
            });
        });
    }
});

// 3. Cambio de Distrito -> Carga Cantones
$('#select_distrito').change(function() {
    let dep  = $('#select_departamento').val();
    let mun  = $('#select_municipio').val();
    let dist = $(this).val();

    // Resetear el selector de cantones
    $('#select_canton').html('<option value="">-- Seleccione --</option>');

    if (dep && mun && dist) {
        // Generar la URL base reemplazando cada comodín explícitamente
        let urlCantones = "{{ route('ficha.cantones', ['DEP', 'MUN', 'DIST']) }}";
        urlCantones = urlCantones.replace('DEP', dep)
                                 .replace('MUN', mun)
                                 .replace('DIST', dist);

        console.log("Petición AJAX Cantones enviada a:", urlCantones);

        $.ajax({
            url: urlCantones,
            type: "GET",
            dataType: "json",
            success: function(data) {
                console.log("Respuesta recibida del servidor (Cantones):", data);

                if (data.length === 0) {
                    $('#select_canton').append('<option value="">-- No hay cantones disponibles --</option>');
                } else {
                    $.each(data, function(index, item) {
                        $('#select_canton').append(
                            '<option value="' + item.codigo.trim() + '">' + item.descripcion.trim() + '</option>'
                        );
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar cantones:", status, error);
            }
        });
    }
});

// 4. Guardado del Literal C
$('#formLiteralC').submit(function(e) {
    e.preventDefault();
    let id_alumno = $('#id_alumno').val();

    let urlGuardarC = "{{ route('ficha.guardar-literal-c', ':id') }}".replace(':id', id_alumno);

    Swal.fire({
        title: 'Guardando datos de Residencia...',
        text: 'Por favor espere...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: urlGuardarC,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado Correctamente!',
                text: response.message,
                confirmButtonColor: '#3085d6'
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: 'No se pudo guardar la información de residencia.',
                confirmButtonColor: '#d33'
            });
        }
    });
});

// ==========================================
// GUARDADO AJAX DEL LITERAL D (SERVICIOS BÁSICOS)
// ==========================================
$('#formLiteralD').submit(function(e) {
    e.preventDefault();
    let id_alumno = $('#id_alumno').val();

    let urlGuardarD = "{{ route('ficha.guardar-literal-d', ':id') }}";
    urlGuardarD = urlGuardarD.replace(':id', id_alumno);

    Swal.fire({
        title: 'Guardando Servicios Básicos...',
        text: 'Por favor espere mientras se procesa la información.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: urlGuardarD,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado Correctamente!',
                text: response.message,
                confirmButtonColor: '#3085d6'
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: 'Ocurrió un inconveniente al actualizar los servicios básicos.',
                confirmButtonColor: '#d33'
            });
        }
    });
});

// =======================================================
// LÓGICA DE INTERACCIÓN Y GUARDADO DEL LITERAL E
// =======================================================

// Habilitar / Deshabilitar pregunta 38 según la pregunta 37
$('#select_conexion_residencial').change(function() {
    let valor = $(this).val();
    let selectCompany = $('#select_company_internet');

    if (valor === 'SI') {
        selectCompany.prop('disabled', false);
    } else {
        selectCompany.prop('disabled', true);
        selectCompany.val(''); // Limpiar selección si cambia a NO o Vacío
    }
});

// Guardado por AJAX
$('#formLiteralE').submit(function(e) {
    e.preventDefault();
    let id_alumno = $('#id_alumno').val();

    let urlGuardarE = "{{ route('ficha.guardar-literal-e', ':id') }}";
    urlGuardarE = urlGuardarE.replace(':id', id_alumno);

    Swal.fire({
        title: 'Guardando Servicios de Comunicación...',
        text: 'Por favor espere...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: urlGuardarE,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado Correctamente!',
                text: response.message,
                confirmButtonColor: '#3085d6'
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: 'Ocurrió un inconveniente al actualizar los servicios de comunicación.',
                confirmButtonColor: '#d33'
            });
        }
    });
});

// =======================================================
// LÓGICA DE INTERACCIÓN Y GUARDADO DEL LITERAL F
// =======================================================

// Habilitar / Deshabilitar campos según la respuesta a la pregunta 45
$('#select_servicio_social').change(function() {
    let realizado = $(this).val();
    let fechaInput = $('#input_fecha_social');
    let horasInput = $('#input_horas_social');
    let descInput  = $('#input_desc_social');

    if (realizado === 'SI') {
        fechaInput.prop('disabled', false);
        horasInput.prop('disabled', false);
        descInput.prop('disabled', false);
    } else {
        fechaInput.prop('disabled', true).val('');
        horasInput.prop('disabled', true).val('');
        descInput.prop('disabled', true).val('');
    }
});

// Envío del formulario Literal F vía AJAX
$('#formLiteralF').submit(function(e) {
    e.preventDefault();
    let id_alumno = $('#id_alumno').val();

    let urlGuardarF = "{{ route('ficha.guardar-literal-f', ':id') }}";
    urlGuardarF = urlGuardarF.replace(':id', id_alumno);

    Swal.fire({
        title: 'Guardando Servicio Social...',
        text: 'Por favor espere mientras se procesan los datos.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: urlGuardarF,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado Correctamente!',
                text: response.message,
                confirmButtonColor: '#3085d6'
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: 'Ocurrió un inconveniente al actualizar el servicio social.',
                confirmButtonColor: '#d33'
            });
        }
    });
});

// =======================================================
// LÓGICA DE ACTUALIZACIÓN Y AGREGADO DEL LITERAL G
// =======================================================

// Actualizar información del responsable seleccionado
$('.formGuardarResponsable').submit(function(e) {
    e.preventDefault();
    let id_alumno = $('#id_alumno').val();

    let urlGuardar = "{{ route('ficha.guardar-responsable', ':id') }}";
    urlGuardar = urlGuardar.replace(':id', id_alumno);

    Swal.fire({
        title: 'Guardando datos del responsable...',
        text: 'Por favor espere...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: urlGuardar,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: response.message,
                confirmButtonColor: '#3085d6'
            }).then(() => {
                location.reload(); // Recarga para refrescar estados de las pestañas
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar',
                text: 'No se pudo guardar la información del responsable.',
                confirmButtonColor: '#d33'
            });
        }
    });
});

// Guardar nuevo responsable desde el Modal
$('#formNuevoResponsable').submit(function(e) {
    e.preventDefault();
    let id_alumno = $('#id_alumno').val();

    let urlCrear = "{{ route('ficha.crear-responsable', ':id') }}";
    urlCrear = urlCrear.replace(':id', id_alumno);

    Swal.fire({
        title: 'Creando nuevo registro...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: urlCrear,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Registrado!',
                text: response.message,
                confirmButtonColor: '#3085d6'
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            let msg = 'Error al registrar la información.';
            if(xhr.responseJSON && xhr.responseJSON.errors) {
                msg = xhr.responseJSON.errors.join('<br>');
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: msg,
                confirmButtonColor: '#d33'
            });
        }
    });
});

</script>

<script>
$(document).ready(function() {

    // 1. DEFINICIÓN OBLIGATORIA DE LA FUNCIÓN
    function verificarCorreoInstitucional() {
        let correoInput = $('#direccion_email');
        let nieInput = $('#codigo_nie');
        
        if (correoInput.length && nieInput.length) {
            let nie = nieInput.val().trim();
            if (correoInput.val().trim() === '' && nie !== '') {
                correoInput.val(nie + '@clases.edu.sv');
            }
        }
    }

    // Evento al presionar el botón de la varita mágica
    $('#btnGenerarCorreo').click(function() {
        verificarCorreoInstitucional();
    });

    // 2. ENVÍO DEL FORMULARIO UNIFICADO
    $('#formFichaCompleta').submit(function(e) {
        e.preventDefault();

        // Ejecutar la verificación antes de enviar los datos por AJAX
        verificarCorreoInstitucional(); 
        
        let id_alumno = $('#id_alumno').val();
        let urlGuardarTodo = "{{ route('ficha.guardar-todo', ':id') }}".replace(':id', id_alumno);

        Swal.fire({
            title: 'Guardando Ficha Completa...',
            text: 'Por favor espere mientras se procesa la información de todas las secciones.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: urlGuardarTodo,
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado Correctamente!',
                    text: response.message,
                    confirmButtonColor: '#3085d6'
                });
            },
            error: function(xhr) {
                let mensajeError = 'Ocurrió un error inesperado al procesar la solicitud.';

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let listaErrores = '<ul style="text-align: left;">';
                    $.each(xhr.responseJSON.errors, function(index, error) {
                        listaErrores += '<li>' + error + '</li>';
                    });
                    listaErrores += '</ul>';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos requeridos o con error:',
                        html: listaErrores,
                        confirmButtonColor: '#d33'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al guardar (' + xhr.status + ')',
                        text: xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : mensajeError,
                        confirmButtonColor: '#d33'
                    });
                }
            }
        });
    });

    // ... Resto de eventos AJAX (Selects dependientes, Modal de Responsables, etc.) ...

});
</script>
@endsection
