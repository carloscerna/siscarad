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

<div class="container-fluid py-4">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i> B. IDENTIFICACIÓN DEL ESTUDIANTE (NUMERALES 1 AL 24)</h5>
            <a href="{{ route('ficha.index') }}" class="btn btn-light btn-sm text-primary font-weight-bold">
                <i class="fas fa-arrow-left"></i> Volver a la Nómina
            </a>
        </div>
        <div class="card-body p-4">
            
            <form id="formLiteralB">
                @csrf
                @method('PUT')
                <input type="hidden" id="id_alumno" value="{{ $alumno->id_alumno }}">

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
                            <option value="01" {{ ($alumno->codigo_genero ?? '') == '01' ? 'selected' : '' }}>Masculino</option>
                            <option value="02" {{ ($alumno->codigo_genero ?? '') == '02' ? 'selected' : '' }}>Femenino</option>
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
                    <div class="form-group col-md-8 mb-3">
                        <label class="label-numeral">12. Tipo de Discapacidad:</label>
                        <select name="codigo_discapacidad" class="form-control">
                            @foreach($discapacidades as $disc)
                                <option value="{{ $disc->codigo }}" {{ ($alumno->codigo_discapacidad ?? '') == $disc->codigo ? 'selected' : '' }}>
                                    {{ trim($disc->nombre) }}
                                </option>
                            @endforeach
                        </select>
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

                    <!-- 14. Apoyo Educativo -->
                    <div class="form-group col-md-6 mb-3">
                        <label class="label-numeral">14. Apoyo Educativo Especializado:</label>
                        <select name="codigo_apoyo_educativo" class="form-control">
                            @foreach($apoyosEducativos as $ap)
                                <option value="{{ $ap->codigo }}" {{ ($alumno->codigo_apoyo_educativo ?? '') == $ap->codigo ? 'selected' : '' }}>
                                    {{ trim($ap->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 16. Correo Electrónico con autocompletado institucional -->
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

                    <!-- 19. Actividad Económica -->
                    <div class="form-group col-md-12 mb-3">
                        <label class="label-numeral">19. Actividad Económica / Trabajo:</label>
                        <select name="codigo_actividad_economica" class="form-control">
                            @foreach($actividadesEconomicas as $act)
                                <option value="{{ $act->codigo }}" {{ ($alumno->codigo_actividad_economica ?? '') == $act->codigo ? 'selected' : '' }}>
                                    {{ trim($act->nombre) }}
                                </option>
                            @endforeach
                        </select>
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

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-success btn-lg shadow">
                        <i class="fas fa-save me-1"></i> Guardar Cambios del Literal B
                    </button>
                </div>
            </form>

        </div>
    </div>
    <!-- ========================================== -->
<!-- SECCIÓN C. RESIDENCIA (NUMERALES 25 AL 32) -->
<!-- ========================================== -->
<div class="card shadow-lg border-0 mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-home me-2"></i> C. RESIDENCIA DEL ESTUDIANTE (NUMERALES 25 AL 32)</h5>
    </div>
    <div class="card-body p-4">
        <form id="formLiteralC">
            @csrf
            @method('PUT')

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
                            <option value="{{ trim($cant->codigo) }}" 
                                {{ trim($alumno->codigo_canton ?? '') == trim($cant->codigo) ? 'selected' : '' }}>
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

            <div class="text-right mt-3">
                <button type="submit" class="btn btn-info btn-lg text-white shadow">
                    <i class="fas fa-save me-1"></i> Guardar Cambios del Literal C
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =============================================== -->
<!-- SECCIÓN D. SERVICIOS BÁSICOS (NUMERALES 33 AL 35) -->
<!-- =============================================== -->
<div class="card shadow-lg border-0 mt-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-faucet me-2"></i> D. SERVICIOS BÁSICOS DEL ESTUDIANTE (NUMERALES 33 AL 35)</h5>
    </div>
    <div class="card-body p-4">
        <form id="formLiteralD">
            @csrf
            @method('PUT')

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
                            <option value="{{ trim($abast->codigo) }}" 
                                {{ trim($alumno->codigo_abastecimiento ?? '') == trim($abast->codigo) ? 'selected' : '' }}>
                                {{ trim($abast->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="text-right mt-3">
                <button type="submit" class="btn btn-success btn-lg text-white shadow">
                    <i class="fas fa-save me-1"></i> Guardar Cambios del Literal D
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================== -->
<!-- SECCIÓN E. SERVICIOS DE COMUNICACIÓN (NUMERALES 36 AL 44) -->
<!-- ==================================================== -->
<div class="card shadow-lg border-0 mt-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-wifi me-2"></i> E. SERVICIOS DE COMUNICACIÓN DEL ESTUDIANTE (NUMERALES 36 AL 44)</h5>
    </div>
    <div class="card-body p-4">
        <form id="formLiteralE">
            @csrf
            @method('PUT')

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
                            <option value="{{ trim($comp->codigo) }}" 
                                {{ trim($alumno->codigo_tipo_conexion_internet_company ?? '') == trim($comp->codigo) ? 'selected' : '' }}>
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
                            <option value="{{ trim($mod->codigo) }}" 
                                {{ trim($alumno->codigo_clases_bajo_modalidad ?? '') == trim($mod->codigo) ? 'selected' : '' }}>
                                {{ trim($mod->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 44. Canales de Atención -->
                <div class="form-group col-md-6 mb-3">
                    <label class="label-numeral">44. Canales de atención utilizados:</label>
                    <select name="codigo_clases_canales_atencion" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($canalesAtencion as $canal)
                            <option value="{{ trim($canal->codigo) }}" 
                                {{ trim($alumno->codigo_clases_canales_atencion ?? '') == trim($canal->codigo) ? 'selected' : '' }}>
                                {{ trim($canal->descripcion) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="text-right mt-3">
                <button type="submit" class="btn btn-warning btn-lg text-dark shadow fw-bold">
                    <i class="fas fa-save me-1"></i> Guardar Cambios del Literal E
                </button>
            </div>
        </form>
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


</script>
@endsection