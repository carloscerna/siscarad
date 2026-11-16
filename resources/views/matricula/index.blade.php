@extends('layouts.app')
Cargando...
@php
use App\Models\User;
    $cant_usuarios = User::count();                                                
use Spatie\Permission\Models\Role;
    $cant_roles = Role::count();                                                
use App\Models\mantenimiento\asignatura\Asignatura;
    $cant_asignaturas = Asignatura::count();        
use Illuminate\Support\Facades;
    $correo_docente = Auth::user()->email;                                        
    $nombre_docente = Auth::user()->name;
    $codigo_personal = Auth::user()->codigo_personal; 
    $codigo_institucion = Auth::user()->codigo_institucion;                                                

@endphp

@section('content')
@role("Docente")
<section class="section">
    <div class="section-header mb-1">
        <h4 class="page__heading">{{$nombre_docente}} - EN CONSTRUCCIÓN</h4>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col col-lg-12 col-xl-12">
                <div class="form-group">    
                    <div class="card bg-secondary">
                        <div class="row">
                            <div class="col col-md-6 col-lg-6 col-xl-6">
                                {!! Form::hidden('codigo_personal', $codigo_personal,['id'=>'codigo_personal', 'class'=>'form-control']) !!}
                                {!! Form::hidden('codigo_institucion', $codigo_institucion,['id'=>'codigo_institucion', 'class'=>'form-control']) !!}
        
                                {{ Form::label('LblAnnLectivo', 'Año Lectivo:') }}
                                {!! Form::select('codigo_annlectivo', ['00'=>'Selecciona...'] + $annlectivo, null, ['id' => 'codigo_annlectivo', 'onchange' => 'BuscarPorAnnLectivo(this.value)','class' => 'form-control']) !!}
                            </div>
        
                            <div class="col col-md-6 col-lg-6 col-xl-6">
                                {{ Form::label('LblGradoSeccionTurno', 'Grado-Sección-Turno:') }}
                                {!! Form::select('codigo_grado_seccion_turno', ['00'=>'Selecciona...'], null, ['id' => 'codigo_grado_seccion_turno','onchange' => 'BuscarPorGradoSeccionMatriculaTodos(this.value)', 'class' => 'form-control']) !!}
                            </div>    
                        </div>       
                    </div>
                </div>
            </div>
        </div>
    </div>

{{--     <div class="section-body">
        <div class="jumbotron p-3">
            <h3 class="display-5">Matricula</h3>
                <div class="row">
                    <div class="col col-md-4 col-lg-4 col-xl-4">
                    </div>
                </div>  {{-- row
        </div> {{-- JUMBOTRON 
    </div>  --}}

    <div class="bg-info" id="NominaEstudiantes" style="display: none;">
        {{-- {{ csrf_field() }}
        {{ method_field('PATCH') }} --}}
        <div class="card">
            <div class="card-header">
                <h2>Nómina de Estudiantes</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="table-responsive-sm">
                            <table>
                                <thead>
                                    <tr class="bg-secondary">
                                        <th colspan="4">Edad-Escala de Años (Sobreedad)</th>
                                        <th colspan="2" class="bg-info text-dark text-center">Estatus</th>
                                        <th colspan="2" class="text-dark text-center">Indicador</th>
                                        <th colspan="2"class="bg-info text-center text-dark"> Promoción</th>
                                    </tr>
                                </thead>
                                    <tbody id="escalas">
                                        <tr>
                                            <td>
                                                <button type="button" class="btn" style="background-color: rgb(0, 255, 102);">
                                                    <label style="text-color: white"> 0  </label><span class="badge badge-light" id="CantidadCero">#</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn" style="background-color: rgb(5, 210, 87);">
                                                    <label style="text-color: white"> 1  </label><span class="badge badge-light" id="CantidadUno">#</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn" style="background-color: rgb(1, 178, 72);">
                                                    <label style="text-color: white"> 2  </label><span class="badge badge-light" id="CantidadDos">#</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn" style="background-color: rgb(2, 147, 60);">
                                                    <label style="text-color: white"> > 3  </label><span class="badge badge-light" id="CantidadTres">#</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary">
                                                    Presentes <span class="badge badge-light" id="presentes">#</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger">
                                                    Retirados <span class="badge badge-light" id="retirados">#</span>
                                                </button>            
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary">
                                                    Sin Sobredad <span class="badge badge-light" id="sinsobreedad">#</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-warning">
                                                    Sobreedad <span class="badge badge-light" id="sobreedad">#</span>
                                                </button>            
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary">
                                                    Promovidos <span class="badge badge-light" id="promovidos">#</span>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info">
                                                    No Promovidos <span class="badge badge-light" id="nopromovidos">#</span>
                                                </button>            
                                            </td>
                                        </tr>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Estudiantes a Matricular -->
                <div class="table-responsive-sm">
                    <table class="table" id="TablaNominaEstudiantes">
                      <thead>
                          <tr class="bg-secondary">
                            <th>N.°</th>
                            <th>NIE</th>
                            <th>Nombre del Estudiante</th>
                            <th>Edad</th>
                            <th>Estatus</th>
                            <th>Indicador</th>
                            <th>Promoción</th>
                            <th></th>
                          </tr>
                      </thead>
                      <tbody id="contenido">
                          <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                          </tr>
                      </tbody>
                      <tfoot>
                          
                      </tfoot>
                    </table>
                  </div>
            </div>
            <div class="card-footer">
                <tr>
                    <td colspan = "4" style="text-align: right;">
                              {{-- <button type="button" class="btn btn-success" id = "goCalificacionGuardar" onclick="GuardarRegistros()">
                                  Guardar
                              </button> --}}
                    </td>
                </tr>
            </div>
          </div>
    </div>
    <!-- The PARA LOS DATOS DE LA MATRICULA.-->  

    <div class="bg-info" id="NominaEstudiantesMatriculados" style="display: none;">
        {{-- {{ csrf_field() }}
        {{ method_field('PATCH') }} --}}
        <div class="card">
            <div class="card-header">
                <h2>Nómina de Estudiantes matriculados</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <!-- Estudiantes a Matriculados -->
                        <div class="table-responsive-sm">
                            <table class="table" id="TablaNominaEstudiantes">
                                <thead>
                                    <tr class="bg-secondary">
                                        <th>N.°</th>
                                        <th>NIE</th>
                                        <th>Nombre del Estudiante</th>
                                    </tr>
                                </thead>
                                <tbody id="contenidoMatriculados">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    
                                </tfoot>
                            </table>
                        </div>
                        </div>                        
                    </div>
                </div>                
            <div class="card-footer">
                <tr>
                    <td colspan = "4" style="text-align: right;">
                              {{-- <button type="button" class="btn btn-success" id = "goCalificacionGuardar" onclick="GuardarRegistros()">
                                  Guardar
                              </button> --}}
                    </td>
                </tr>
            </div>
          </div>
    </div>
    <!-- The modal PARA LOS DATOS DE LA MATRICULA.-->  
</section>

<!-- Modal -->
<!-- Modal Modernizado de Matrícula -->
<div id="MyMatricula" class="modal fade" role="dialog" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content shadow-lg border-0 rounded-lg">
        
        <!-- Cabecera del Modal -->
        <div class="modal-header bg-primary text-white align-items-center">
          <div class="d-flex align-items-center">
            <h5 class="modal-title mb-0 font-weight-bold"><i class="fas fa-user-graduate mr-2"></i> Proceso de Matrícula</h5>
            <span class="badge badge-light text-primary ml-3 px-2 py-1 font-weight-bold" id="PromocionMatricula">#</span>
          </div>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Cuerpo del Modal -->
        <div class="modal-body bg-light p-4">
            <form id="formMatriculaModal">
                
                <!-- Tarjeta de Información General del Estudiante -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body bg-white rounded">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <span class="text-muted small font-weight-bold">ID Estudiante: <span id="IdMatricula" class="text-dark">#</span></span>
                                <h4 class="text-primary font-weight-bold mt-1 mb-0" id="EstudianteMatricula">Nombre del Estudiante</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Detalles Académicos (Año y Códigos) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body bg-white rounded">
                        <h6 class="text-secondary font-weight-bold mb-3"><i class="fas fa-calendar-alt mr-1"></i> Datos Académicos de Destino</h6>
                        <div class="row text-center">
                            <div class="col">
                                <span class="d-block text-muted small">Año Lectivo</span>
                                <span class="badge badge-secondary px-3 py-2 font-weight-bold" id="AnnLectivoMatricula" style="font-size: 0.9rem;">#</span>
                            </div>
                            <div class="col">
                                <span class="d-block text-muted small">Modalidad</span>
                                <span class="badge badge-info px-3 py-2 font-weight-bold" id="CodigoModalidadMatricula" style="font-size: 0.9rem;">#</span>
                            </div>
                            <div class="col">
                                <span class="d-block text-muted small">Grado</span>
                                <span class="badge badge-success px-3 py-2 font-weight-bold" id="CodigoGradoMatricula" style="font-size: 0.9rem;">#</span>
                            </div>
                            <div class="col">
                                <span class="d-block text-muted small">Sección</span>
                                <span class="badge badge-info px-3 py-2 font-weight-bold" id="CodigoSeccionMatricula" style="font-size: 0.9rem;">#</span>
                            </div>
                            <div class="col">
                                <span class="d-block text-muted small">Turno</span>
                                <span class="badge badge-dark px-3 py-2 font-weight-bold" id="CodigoTurnoMatricula" style="font-size: 0.9rem;">#</span>
                            </div>
                        </div>
                        <!-- Select oculto si se requiere mantener la compatibilidad lógica -->
                        <select class="form-control" name="lstModalidad" id="lstModalidad" style="display: none;">
                            <option value="21">Bachillerato General</option>
                            <option value="15">Bachillerato Técnico Vocacional Administrativo Contable.</option>
                            <option value="11">Bachillerato General - Nocturna</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de Datos de los Responsables -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body bg-white rounded">
                        <h6 class="text-secondary font-weight-bold mb-3"><i class="fas fa-users mr-1"></i> Datos de los Responsables y Contacto</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" id="DatosResponsableTabla">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 25%;">Parentesco / Selección</th>
                                        <th style="width: 50%;">Nombre y Dirección</th>
                                        <th style="width: 25%;">N.º de Teléfono</th>
                                    </tr>
                                </thead>
                                <tbody id="ContenidoResponsable">
                                    <tr>
                                        <td>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="Encargado" id="EncargadoPadre" value="Padre"/>
                                                <label class="form-check-label font-weight-bold text-primary" for="EncargadoPadre">Encargado</label>
                                            </div>
                                            {!! Form::select('codigo_familiar_0', ['00'=>'Selecciona...'] + $codigo_familiar_0, null, ['id' => 'codigo_familiar_00', 'class' => 'form-control form-control-sm']) !!}
                                        </td>
                                        <td>
                                            <input type="hidden" name="id_1" id="id_1" value="">
                                            <label class="small text-muted mb-1">Nombre del Padre:</label>
                                            <input type="text" class="form-control form-control-sm font-weight-bold text-dark bg-light" id="nombrep" name="nombrep" readonly>
                                        </td>
                                        <td>
                                            <label class="small text-muted mb-1">Teléfono:</label>
                                            <input type="text" class="form-control form-control-sm" id="telefono1" name="telefono1" maxlength="9" autocomplete="off">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="Encargado" id="EncargadoMadre" value="Madre"/>
                                                <label class="form-check-label font-weight-bold text-primary" for="EncargadoMadre">Encargado</label>
                                            </div>
                                            {!! Form::select('codigo_familiar_1', ['00'=>'Selecciona...'] + $codigo_familiar_0, null, ['id' => 'codigo_familiar_01', 'class' => 'form-control form-control-sm']) !!}
                                        </td>
                                        <td>
                                            <input type="hidden" name="id_2" id="id_2" value="">
                                            <label class="small text-muted mb-1">Nombre de la Madre:</label>
                                            <input type="text" class="form-control form-control-sm font-weight-bold text-dark bg-light" id="nombrem" name="nombrem" readonly>
                                        </td>
                                        <td>
                                            <label class="small text-muted mb-1">Teléfono:</label>
                                            <input type="text" class="form-control form-control-sm" id="telefono2" name="telefono2" maxlength="9" autocomplete="off">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="Encargado" id="EncargadoOtro" value="Otro"/>
                                                <label class="form-check-label font-weight-bold text-primary" for="EncargadoOtro">Encargado</label>
                                            </div>
                                            {!! Form::select('codigo_familiar_2', ['00'=>'Selecciona...'] + $codigo_familiar_0, null, ['id' => 'codigo_familiar_02', 'class' => 'form-control form-control-sm']) !!}
                                        </td>
                                        <td>
                                            <input type="hidden" name="id_3" id="id_3" value="">
                                            <label class="small text-muted mb-1">Nombre Otro:</label>
                                            <input type="text" class="form-control form-control-sm font-weight-bold text-dark" id="nombreotro" name="nombreotro" autocomplete="off">
                                        </td>
                                        <td>
                                            <label class="small text-muted mb-1">Teléfono:</label>
                                            <input type="text" class="form-control form-control-sm" id="telefono3" name="telefono3" maxlength="9" autocomplete="off">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <label class="small text-muted mb-1">Nombre Estudiante:</label>
                                            <input type="text" class="form-control form-control-sm font-weight-bold text-dark bg-light" id="nombreEstudiante" name="nombreEstudiante" readonly>
                                        </td>
                                        <td>
                                            <label class="small text-muted mb-1">Celular Estudiante:</label>
                                            <input type="text" class="form-control form-control-sm" id="telefono4" name="telefono4" maxlength="9" autocomplete="off">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td colspan="2">
                                            <label class="small text-muted mb-1">Dirección:</label>
                                            <textarea class="form-control form-control-sm" name="direccion1" id="direccion1" rows="2"></textarea>
                                            
                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" value="Si" id="TieneHermanos">
                                                <label class="form-check-label text-dark small font-weight-bold" for="TieneHermanos">
                                                    Tiene hermano(s) en otro grado(s).
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- Pie del Modal -->
        <div class="modal-footer bg-white border-top-0 px-4 pb-4">
          <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal" onclick="CerrarMatricula()"><i class="fas fa-times mr-1"></i> Cerrar</button>
          <button type="button" class="btn btn-primary px-4 font-weight-bold shadow-sm" data-dismiss="modal" onclick="GuardarMatricula()" id="GuardarMatricular"><i class="fas fa-save mr-1"></i> Guardar</button>
        </div>

      </div>
    </div>
</div>
@endrole

@role('Administrador')
<section class="section">
        <div class="section-header">
            <h3 class="page__heading">Tablero</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">                          
                                <div class="row">
                                    <div class="col-md-4 col-xl-4">
                                    
                                    <div class="card bg-primary order-card">
                                            <div class="card-block m-1">
                                            <h5>Usuarios</h5>                                               
                                                <h2 class="text-right"><i class="fa fa-users f-left float-left"></i><span>{{$cant_usuarios}}</span></h2>
                                                <p class="m-b-0 text-right"><a href="/usuarios" class="text-white">Ver más</a></p>
                                            </div>                                            
                                        </div>                                    
                                    </div>
                                    
                                    <div class="col-md-4 col-xl-4">
                                        <div class="card bg-secondary order-card">
                                            <div class="card-block m-1">
                                            <h5>Roles</h5>                                               

                                                <h2 class="text-right"><i class="fa fa-user-lock f-left float-left"></i><span>{{$cant_roles}}</span></h2>
                                                <p class="m-b-0 text-right"><a href="/roles" class="text-white">Ver más</a></p>
                                            </div>
                                        </div>
                                    </div>                                                                
                                    
                                    <div class="col-md-4 col-xl-4">
                                        <div class="card bg-info order-card">
                                            <div class="card-block m-1">
                                                <h5>Asignaturas</h5>                                               

                                                <h2 class="text-right"><i class="fa fa-book f-left float-left"></i><span>{{$cant_asignaturas}}</span></h2>
                                                <p class="m-b-0 text-right"><a href="/asignaturas" class="text-white">Ver más</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endrole
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#codigo_asignatura1').select2();
            $('#codigo_asignatura1').on('change', function(e){
                let valor = $('#codigo_asignatura1').select2('val');
                let text = $('#codigo_asignatura1 option:selected').text();
                //@this.set('seleccionado', text);
            })
        // function onchange
        $('#lstModalidad').on('change', function(e){
                //let valor = $('#lstModalidad').select2('val');
                var codigo_modalidad = $('#lstModalidad option:selected').val();
                $("#CodigoModalidadMatricula").text(codigo_modalidad);    
                //@this.set('seleccionado', text);
            })
  
    function BuscarPorAnnLectivo(AnnLectivo) {
    // Si selecciona la opción por defecto, limpiamos y bloqueamos el select
    if(AnnLectivo == '00') {
        $('#codigo_grado_seccion_turno').prop('disabled', true);
        $('#codigo_grado_seccion_turno').empty().append('<option value="00">Selecciona...</option>');
        $("#NominaEstudiantes").css("display", "none");
        return;
    }

    url_ajax = '{{url("getGradoSeccionMatricula")}}' 
    codigo_personal = $('#codigo_personal').val();
    codigo_annlectivo = AnnLectivo;

    $.ajax({
        type: "post",
        url: url_ajax,
        data: {
            "_token": "{{ csrf_token() }}",
            "id": codigo_personal, 
            codigo_annlectivo: codigo_annlectivo
        },
        dataType: 'json',
        success: function(data) {
            var matriculaHabilitadaPorFecha = true;

            // 1. Validar fechas de matrícula del año lectivo seleccionado
            if (data.ann_lectivo) {
                var fechaInicioMatricula = data.ann_lectivo.fecha_inicio_matricula;
                var fechaFinMatricula = data.ann_lectivo.fecha_fin_matricula;
                var fechaActual = new Date();

                // Normalizar la fecha actual a medianoche para evitar desfases de horario locales
                fechaActual.setHours(0, 0, 0, 0);

                if (!fechaInicioMatricula || !fechaFinMatricula) {
                    toastr.warning("Este año lectivo aún no tiene configuradas las fechas de matrícula.", "Aviso del Sistema");
                    matriculaHabilitadaPorFecha = false;
                } else {
                    var inicio = new Date(fechaInicioMatricula + 'T00:00:00');
                    var fin = new Date(fechaFinMatricula + 'T23:59:59');

                    if (fechaActual < inicio || fechaActual > fin) {
                        matriculaHabilitadaPorFecha = false;
                        toastr.error("El período de matrícula para el año seleccionado está cerrado.", "Atención");
                    } else {
                        toastr.success("Período de matrícula abierto.", "Sistema");
                    }
                }
            } else {
                matriculaHabilitadaPorFecha = false;
            }

            var miselect = $("#codigo_grado_seccion_turno");
            miselect.empty();
            $('#contenido').empty();
            $("#NominaEstudiantes").css("display", "none");

            // 2. Controlar disponibilidad del selector según la validación de fechas
            if (!matriculaHabilitadaPorFecha) {
                // Deshabilitar el select si no está en fecha
                miselect.prop('disabled', true);
                miselect.append('<option value="00">Período de matrícula no disponible...</option>');
            } else {
                // Habilitar el select y cargar los grados si cumple con las fechas
                miselect.prop('disabled', false);
                miselect.append('<option value="00">Selecciona...</option>');
                
                var listaGrados = data.grados || data; 
                $.each(listaGrados, function(key, value) {
                    miselect.append('<option value="' + value.codigo_gradoseccionturno + '">' + value.nombre_gradoseccionturno + '</option>'); 
                });
            }
        } 
    });
}
        //
        // FUNCION PARA PRESENTES O RETIRADOS.
        //
        function BuscarPorGradoSeccionMatriculaTodos() {
            var codigo_annlectivo = $('#codigo_annlectivo').val();
            var codigo_institucion = $('#codigo_institucion').val();
            var codigo_gradoseccionturno = $('#codigo_grado_seccion_turno').val();
                console.log(codigo_annlectivo + ' ' + codigo_gradoseccionturno);
            if(codigo_annlectivo == '00' || codigo_gradoseccionturno == '00'){
                alert('Debe seleccionar Año Lectivo y Grado-Sección-Turno');
                    $('#codigo_annlectivo').focus();
            }else{
                // Botón Otro... visible.
				    $("#NominaEstudiantes").css("display","block");
                // CUANDO SE HA SELECCIONADO UN GRADO...
                url_ajax = '{{url("getGradoSeccionMatriculaTodos")}}' 
                csrf_token = '{{csrf_token()}}' 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            // BUSCAR LA CARGA ACADEMICA DEL DOCENTE.
            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_institucion: codigo_institucion,
                    codigo_gradoseccionturno: codigo_gradoseccionturno
                },
                dataType: 'json',
                success:function(data) {
                    //
                    // variables a utilizar.
                    //
                    var linea = 0; var html= ""; var presentes_ = 0; var retirados_ = 0; var sobreedad_ = 0; var promovidos_ = 0; var no_promovidos_ = 0; var sinsobreedad_ = 0;
                    var matricula_ = 0;  var cantidad_escala_0 = 0; var cantidad_escala_1 = 0; var cantidad_escala_2 = 0; var cantidad_escala_3 = 0; var matricula = true;
                        $('#contenido').empty();

// --- NUEVA VALIDACIÓN DE FECHA DE APERTURA ---
                // Suponiendo que el backend nos devuelve las fechas en el objeto o las leemos de una variable global
                // (Asegúrate de pasar fecha_inicio_matricula y fecha_fin_matricula desde tu controlador en la respuesta JSON)
                var fechaActual = new Date();
                var fechaInicio = data.fecha_inicio_matricula ? new Date(data.fecha_inicio_matricula) : null;
                var fechaFin = data.fecha_fin_matricula ? new Date(data.fecha_fin_matricula) : null;
                
                var matriculaHabilitadaPorFecha = true;
                if (fechaInicio && fechaFin) {
                    if (fechaActual < fechaInicio || fechaActual > fechaFin) {
                        matriculaHabilitadaPorFecha = false;
                        toastr.warning("El período de matrícula se encuentra cerrado.", "Atención");
                    }
                }
                // ---------------------------------------------

                        $('#contenido').append(data);
                    // recorrer la matriz que viene del Controlador.
                    $.each( data, function( key, value ) {
                        linea = linea + 1;
                        // validar para cambiar de color la l{inea}
                        if (linea % 2 === 0) {
                            fila_color = '<tr style=background:#A5FFA5; text-color:black;>';
                        }else{
                            fila_color = '<tr style=background: #FFFFFF; text-color:black;>';
                        }
                        // validar si es cero la calificación
                        if(parseFloat(value.nota_actividad) == 0){
                            style = " style='background: #FFC5C5; color: #FA4646;'";
                        }else{
                            style = " style='background: #FAFAFA; color: black;'";
                        } 
                        // ARMAR VARIABLE QUE CONTENGA LOS DATOS PARA PODER OBTENER LA INFORMACION DE LA BOLETA DE CALIFICACIONES
                        //
                            var codigo_nie = value.codigo_nie;
                            var codigo_alumno = value.codigo_alumno;
                            var nombre_foto = value.foto;
                            var edad = value.edad;
                            var retirado = value.retirado;
                            var sobreedad = value.sobreedad;
                            var codigo_resultado = value.codigo_resultado;
                            var codigo_grado = value.codigo_grado;
                            sobreedad_escala = 0;
                            matricula = true;
                            // validar edad.
                                
                                calcular_sobreedad_escala(edad, codigo_grado);
                               // console.log("retornado " + sobreedad_escala);
                                if(sobreedad_escala == 0){
                                    var td_edad = "<td class='text-black' style='background-color: rgb(0, 255, 102)'>" + edad + "</td>";
                                    cantidad_escala_0++;
                                }else if(sobreedad_escala == 1){
                                    var td_edad = "<td class='text-white' style='background-color: rgb(5, 210, 87)'>" + edad + "</td>";
                                    cantidad_escala_1++;
                                }else if(sobreedad_escala == 2){
                                    var td_edad = "<td class='text-white' style='background-color: rgb(1, 178, 72)'>" + edad + "</td>";
                                    cantidad_escala_2++;
                                }else{
                                    var td_edad = "<td class='text-white' style='background-color: rgb(2, 147, 60)'>" + edad + "</td>";
                                    cantidad_escala_3++;
                                }
                                
                            // Validar retirado.
                                if(retirado == ""){
                                    var td_retirado = "<td class='bg-primary text-white text-small'> Presente </td>";
                                    presentes_++;
                                }else{
                                    var td_retirado = "<td class='bg-danger text-white text-small'> Retirado </td>";
                                    retirados_++;
                                    matricula = false;
                                }
                            // Validar Sobreedad.
                                if(sobreedad == ""){
                                    var td_sobreedad = "<td class='bg-primary text-white text-small'> Sin Sobreedad </td>";
                                    sinsobreedad_++;
                                }else{
                                    var td_sobreedad = "<td class='bg-warning text-white text-small'> Sobreedad </td>";
                                    sobreedad_++;
                                    //matricula = false;
                                }
                                // Validar Promovido no Promovido.
                                if(codigo_resultado == "3"){
                                    var td_codigo_resultado = "<td class='bg-primary text-white text-small'> Promovido </td>";
                                    promovidos_++;
                                    //matricula = true;
                                }else{
                                    var td_codigo_resultado = "<td class='bg-info text-white text-small'> No Promovido </td>";
                                    no_promovidos_++;
                                    //matricula = false;
                                }
                                // VALIDARBOTON DE MATRICULA.
                                    if(matricula == true){
                                        var boton_matricula = '<td><a class="fixedbutton btn btn-primary"  href="#">Matricular</i>';
                                    }else{
                                        var boton_matricula = '<td><a class="btn btn-primary disabled"  href="'+codigo_alumno+"-"+codigo_grado+'">Matricular</i>';
                                    }
                                /*
                                <p class="bg-primary text-white">This text is important.</p>
                                <p class="bg-success text-white">This text indicates success.</p>
                                <p class="bg-info text-white">This text represents some information.</p>
                                <p class="bg-warning text-white">This text represents a warning.</p>
                                <p class="bg-danger text-white">This text represents danger.</p>
                                <p class="bg-secondary text-white">Secondary background color.</p>
                                <p class="bg-dark text-white">Dark grey background color.</p>
                                <p class="bg-light text-dark">Light grey background color.</p>
                                */
                        // colocar los valores de las etiquetas con el dato de cada indicadores, presentes o sobreeedad.
                            $("#presentes").text(presentes_);
                            $("#retirados").text(retirados_);

                            $("#sobreedad").text(sobreedad_);
                            $("#sinsobreedad").text(sinsobreedad_);

                            $("#promovidos").text(promovidos_);
                            $("#nopromovidos").text(no_promovidos_);
                        // colocar la cantidad segundo la escala.
                            $("#CantidadCero").text(cantidad_escala_0);
                            $("#CantidadUno").text(cantidad_escala_1);
                            $("#CantidadDos").text(cantidad_escala_2);
                            $("#CantidadTres").text(cantidad_escala_3);

// VALIDAR BOTÓN DE MATRICULA CONDICIONADO POR FECHA Y ESTADO
                    var boton_matricula = '';
                    if (matriculaHabilitadaPorFecha && matricula == true) {
                        boton_matricula = '<td><a class="fixedbutton btn btn-primary" href="#">Matricular</a></td>';
                    } else if (!matriculaHabilitadaPorFecha) {
                        boton_matricula = '<td><span class="badge badge-secondary">Fuera de período</span></td>';
                    } else {
                        boton_matricula = '<td><a class="btn btn-primary disabled" href="#">Matricular</a></td>';
                    }

                        // armar el thml de la tabla.
                        html += fila_color +
                        '<td>' + linea + 
                        '<input type="hidden" name="codigo_alumno" value="' + codigo_alumno + '"</td>' +
                        '<td>' + value.codigo_nie + '</td>' +
                        '<td>' + value.apellidos_nombres_estudiantes + '</td>' +
                        td_edad +
                        td_retirado +
                        td_sobreedad +
                        td_codigo_resultado + 
                        boton_matricula +
                        '</tr>';
                    }); // fin del for...eacht...
                        $('#contenido').html(html);
                        $('#contenido').focus();
                            // Display an info toast with no title
                            toastr.success("Registros Encontrados... " + linea, "Sistema");
                } 
            });
            } 
        }

        //
        // FUNCION PARA PRESENTAR ESTUDIANTES YA MATRICULADOS.
        //
        function BuscarPorGradoSeccionMatriculadosTodos() {
            var codigo_annlectivo = $('#codigo_annlectivo').val();
            var codigo_institucion = $('#codigo_institucion').val();
            var codigo_gradoseccionturno = $('#codigo_grado_seccion_turno').val();
                console.log(codigo_annlectivo + ' ' + codigo_gradoseccionturno);
            if(codigo_annlectivo == '00' || codigo_gradoseccionturno == '00'){
                alert('Debe seleccionar Año Lectivo y Grado-Sección-Turno');
                    $('#codigo_annlectivo').focus();
            }else{
                // Botón Otro... visible.
				    $("#NominaEstudiantesMatriculados").css("display","block");
                // CUANDO SE HA SELECCIONADO UN GRADO...
                url_ajax = '{{url("getGradoSeccionMatriculadosTodos")}}' 
                csrf_token = '{{csrf_token()}}' 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            // BUSCAR LA CARGA ACADEMICA DEL DOCENTE.
            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_institucion: codigo_institucion,
                    codigo_gradoseccionturno: codigo_gradoseccionturno
                },
                dataType: 'json',
                success:function(data) {
                    //
                    // variables a utilizar.
                    //
                    var linea = 0; var html= ""; var presentes_ = 0; var retirados_ = 0; var sobreedad_ = 0; var promovidos_ = 0; var no_promovidos_ = 0; var sinsobreedad_ = 0;
                    var matricula_ = 0;  var cantidad_escala_0 = 0; var cantidad_escala_1 = 0; var cantidad_escala_2 = 0; var cantidad_escala_3 = 0; var matricula = true;
                        $('#contenidoMatriculados').empty();
                        $('#contenidoMatriculados').append(data);
                    // recorrer la matriz que viene del Controlador.
                    $.each( data, function( key, value ) {
                        linea = linea + 1;
                        // validar para cambiar de color la l{inea}
                        if (linea % 2 === 0) {
                            fila_color = '<tr style=background:#A5FFA5; text-color:black;>';
                        }else{
                            fila_color = '<tr style=background: #FFFFFF; text-color:black;>';
                        }

                        // ARMAR VARIABLE QUE CONTENGA LOS DATOS PARA PODER OBTENER LA INFORMACION DE LA BOLETA DE CALIFICACIONES
                        //
                            var codigo_nie = value.codigo_nie;
                            var codigo_alumno = value.codigo_alumno;
                            var nombre_foto = value.foto;
                            var edad = value.edad;
                            var retirado = value.retirado;
                            var sobreedad = value.sobreedad;
                            var codigo_resultado = value.codigo_resultado;
                            var codigo_grado = value.codigo_grado;
                            
                                /*
                                <p class="bg-primary text-white">This text is important.</p>
                                <p class="bg-success text-white">This text indicates success.</p>
                                <p class="bg-info text-white">This text represents some information.</p>
                                <p class="bg-warning text-white">This text represents a warning.</p>
                                <p class="bg-danger text-white">This text represents danger.</p>
                                <p class="bg-secondary text-white">Secondary background color.</p>
                                <p class="bg-dark text-white">Dark grey background color.</p>
                                <p class="bg-light text-dark">Light grey background color.</p>
                                */

                        // armar el thml de la tabla.
                        html += fila_color +
                        '<td>' + linea + 
                        '<input type="hidden" name="codigo_alumno" value="' + codigo_alumno + '"</td>' +
                        '<td>' + value.codigo_nie + '</td>' +
                        '<td>' + value.apellidos_nombres_estudiantes + '</td>' +
                        '</tr>';
                    }); // fin del for...eacht...
                        $('#contenidoMatriculados').html(html);
                        $('#contenidoMatriculados').focus();
                            // Display an info toast with no title
                            toastr.success("Registros Encontrados... " + linea, "Sistema");
                } 
            });
            } 
        }
    // BUSCAR DATOS DE LA MATRICULA. POR ESTUDIANTE.
    $('#TablaNominaEstudiantes #contenido').on( 'click', '.fixedbutton', function (){
        var currow = $(this).closest('tr');
        var codigo_alumno = currow.find('td').eq(0).find("input[name='codigo_alumno']").val();
        var nie = currow.find('td:eq(1)').html();
        var nombre = currow.find('td:eq(2)').html();
        var promocion = currow.find('td:eq(6)').html();
        // Variables.
        var codigo_gradoseccionturno = $('#codigo_grado_seccion_turno').val();
        var codigo_annlectivo = $('#codigo_annlectivo').val();
        codigo_annlectivo = codigo_annlectivo.trim();

        // 1. Obtener el valor completo concatenado del select de Grado-Sección-Turno
        var codigo_gradoseccionturno = $('#codigo_grado_seccion_turno').val();

        // 2. Extraer los fragmentos según la estructura de tu sistema SISCARAD:
        var codigo_grado    = codigo_gradoseccionturno.substring(0, 2);
        var codigo_seccion  = codigo_gradoseccionturno.substring(2, 4);
        var codigo_turno    = codigo_gradoseccionturno.substring(4, 6);

        // 3. Extraer la modalidad y ahora sí aplicar .trim() de forma segura
        var codigo_modalidad = codigo_gradoseccionturno.substring(6, 8).trim();


        //alert("Nombre: " + nombre + " Código: " + codigo_alumno + " Nie: " + nie);           
        $("#IdMatricula").text(codigo_alumno);
        $("#EstudianteMatricula").text(nombre);

        $("#PromocionMatricula").text(promocion);
        // Validar si es promovido o no promovido.

        // Mapeo basado en los nombres y códigos de la tabla bachillerato_ciclo
            var nombresBachilleratoCiclo = {
                '04': 'Segundo Ciclo de Educación Básica',
                '05': 'Tercer Ciclo de Educación Básica',
                '06': 'Bachillerato General',
                '09': 'Bachillerato Técnico Vocacional',
                '10': 'Modalidades Flexibles',
                '14': 'Primer Ciclo de Educación Básica',
                '15': 'Bachillerato Técnico Vocacional Administrativo Contable',
                '16': 'Educación Básica Focalizada'
            };

            // Al procesar la selección del estudiante para la matrícula:
            var codigoModalidadTrim = codigo_modalidad.trim();
            var textoModalidadReal = nombresBachilleratoCiclo[codigoModalidadTrim] || 'Modalidad No Especificada';

            var textoAnnLectivoReal = "Año Lectivo " + codigo_annlectivo;

            // Pintar los nombres limpios en los elementos del modal
            $("#AnnLectivoMatricula").text(textoAnnLectivoReal);
            $("#CodigoModalidadMatricula").text(textoModalidadReal);

    // 1. DICCIONARIOS DE REFERENCIA (Nombres reales basados en tus tablas de PostgreSQL)
        var nombresGrados = {
            '4P': 'Parvularia 4 años',
            '5P': 'Parvularia 5 años',
            '6P': 'Parvularia 6 años',
            '01': 'Primer Grado',
            '02': 'Segundo Grado',
            '03': 'Tercer Grado',
            '04': 'Cuarto Grado',
            '05': 'Quinto Grado',
            '06': 'Sexto Grado',
            '07': 'Séptimo Grado',
            '08': 'Octavo Grado',
            '09': 'Noveno Grado',
            '10': 'Primer Año de Bachillerato',
            '11': 'Segundo Año de Bachillerato',
            '12': 'Tercer Año de Bachillerato',
            '17': 'Segundo Grado Focalizado',
            '18': 'Tercer Grado Focalizado'
        };

        var nombresModalidades = {
            '04': 'Segundo Ciclo',
            '05': 'Tercer Ciclo',
            '06': 'Bachillerato General',
            '09': 'Bachillerato Técnico Vocacional',
            '10': 'Modalidades Flexibles',
            '14': 'Primer Ciclo',
            '15': 'Bach. Técnico Administrativo Contable',
            '16': 'Educación Básica Focalizada'
        };

        var nombresTurnos = {
            '01': 'Matutino',
            '02': 'Vespertino',
            '03': 'Nocturno',
            '04': 'Jornada Completa'
        };

        var nombresSecciones = {
            '01': 'Sección A',
            '02': 'Sección B',
            '03': 'Sección C',
            '04': 'Sección D'
        };

        // 2. TU BLOQUE SWITCH ACTUALIZADO PARA ASIGNAR CÓDIGOS Y NOMBRES
        if(promocion.trim() == "Promovido" || promocion.trim() == "No Promovido"){
            codigo_grado = codigo_gradoseccionturno.substring(0,2);
            codigo_seccion = codigo_gradoseccionturno.substring(2,4);
            codigo_turno = codigo_gradoseccionturno.substring(4,6);
            codigo_modalidad = codigo_gradoseccionturno.substring(6,8);

            if(promocion.trim() == "Promovido") {
                codigo_annlectivo = parseInt(codigo_annlectivo) + 1;
                
                // Switch de reglas de promoción de grado
                switch (codigo_grado) {
                    case '4P': codigo_grado = '5P'; break;
                    case '5P': codigo_grado = '6P'; break;
                    case '6P': 
                        codigo_grado = '01';
                        codigo_modalidad = '20';
                        codigo_turno = '01';
                        if(codigo_seccion == '02'){ codigo_seccion = '03'; codigo_turno = '02'; }
                        break;
                    case '01': 
                        codigo_grado = '02'; 
                        codigo_modalidad = '19';
                         break;
                    case '02': codigo_grado = '03'; codigo_modalidad = '19';break;
                    case '03': codigo_grado = '04'; codigo_modalidad = '18'; break;
                    case '04': codigo_grado = '05'; codigo_modalidad = '18';break;
                    case '05': codigo_grado = '06'; codigo_modalidad = '18'; break;
                    case '06': codigo_grado = '07'; codigo_modalidad = '17'; break;
                    case '07': codigo_grado = '08'; codigo_modalidad = '17';break;
                    case '08': codigo_grado = '09'; codigo_modalidad = '17'; break;
                    case '09': 
                        codigo_seccion = '01';
                        codigo_grado = '10';
                        codigo_modalidad = '21';
                        codigo_turno = '04';
                        $("#lstModalidad").css("display", "block");
                        //$("#lstModalidad").show();
                        break;
                    case '10': codigo_grado = '11'; codigo_seccion = '01'; break;
                    case '11': 
                        if(codigo_modalidad == '21' && codigo_grado == '11'){
                            codigo_grado = '12'; 
                            codigo_modalidad = '21';
                        }
                        if(codigo_modalidad == '15' && codigo_grado == '11'){
                            codigo_grado = '12'; 
                            codigo_modalidad = '15';
                        }
                        break;
                    default: break;
                }
            } else {
                // Si es No Promovido, el año aumenta en 1 pero repite el mismo grado
                codigo_annlectivo = parseInt(codigo_annlectivo) + 1;
            }

            // 3. OBTENEMOS LOS NOMBRES DESCRIPTIVOS USANDO LOS DICCIONARIOS
            var textoGrado = nombresGrados[codigo_grado] || codigo_grado;
            var textoModalidad = nombresModalidades[codigo_modalidad] || codigo_modalidad;
            var textoTurno = nombresTurnos[codigo_turno] || codigo_turno;
            var textoSeccion = nombresSecciones[codigo_seccion] || codigo_seccion;

            // 4. PLASMAMOS LOS NOMBRES EN LOS BADGES DEL MODAL
            $("#AnnLectivoMatricula").text(codigo_annlectivo);
            $("#CodigoModalidadMatricula").text(textoModalidad);    
            $("#CodigoGradoMatricula").text(textoGrado);    
            $("#CodigoSeccionMatricula").text(textoSeccion);    
            $("#CodigoTurnoMatricula").text(textoTurno);   
        }
        //
        // traer datos del responsable.
        //

        url_ajax = '{{url("getDatosResponsables")}}' 
            csrf_token = '{{csrf_token()}}' 

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_alumno,
                    codigo_modalidad: codigo_modalidad,
                    codigo_grado: codigo_grado,
                    codigo_annlectivo: codigo_annlectivo
                },
                dataType: 'json',
                success:function(response) {

                    // 1. Validar si existe carga académica para este año/grado de destino
                            var tieneCarga = response.tiene_carga_academica;
                            var data = response.responsables;

                            if (!tieneCarga) {
                                toastr.error("Acción detenida: No existe carga académica creada para este grado en el año lectivo " + codigo_annlectivo, "Sistema");
                                $("#GuardarMatricular").attr('disabled', true);
                                $("#PromocionMatricula").text("SIN CARGA ACADÉMICA");
                            } else {
                                $("#GuardarMatricular").prop('disabled', false);
                            }
                            
                    $.each( data, function( key, value ) {
                        // VALIDAR SI LA MATRICULA YA FUE REALIZADA.
                            if(value.estado_matricular == 'si'){
                                $("#GuardarMatricular").prop('disabled', true);
                                $("#PromocionMatricula").text("MATRICULADO");
                            }else{
                                $("#GuardarMatricular").prop('disabled', false);
                            }
                        switch (value.fila) {
                            case 0:
                                $("#codigo_familiar_00").val(value.codigo_familiar);
                                $("#id_1").val(value.id_);
                                $("#nombrep").val(value.nombres);
                                $("#telefono1").val(value.telefono);
                                if(value.encargado == true){
                                   // alert(value.encargado+" Padre.");
                                    $("#EncargadoPadre").prop("checked", true);
                                }
                                // información del estudiante.
                                $("#direccion1").val(value.direccion);
                                $("#nombreEstudiante").val(value.nombreEstudiante);
                                $("#telefono4").val(value.numeroCelular);
                                if(value.poseeHermano == true){
                                   // alert(value.encargado+" Padre.");
                                    $("#TieneHermanos").prop("checked", true);
                                }
                                break;
                            case 1:
                                $("#codigo_familiar_01").val(value.codigo_familiar);
                                $("#id_2").val(value.id_);
                                $("#nombrem").val(value.nombres);
                                $("#telefono2").val(value.telefono);
                                if(value.encargado == true){
                                   // alert(value.encargado +" Madre.");
                                    $("#EncargadoMadre").prop("checked", true);
                                }
                            break;
                            case 2:
                                $("#codigo_familiar_02").val(value.codigo_familiar);
                                $("#id_3").val(value.id_);
                                $("#nombreotro").val(value.nombres);
                                $("#telefono3").val(value.telefono);
                                if(value.encargado == true){
                                   // alert(value.encargado+" Otro.");
                                    $("#EncargadoOtro").prop("checked", true);
                                }
                                break;
                            default:
                                break;
                        }
                    }); // fin del for...eacht...
                } 
            });
        // form modal.
        $("#MyMatricula").modal("show");
    });
// funcionar para guardar las calificaciones.
    function GuardarMatricula() {
        var codigo_annlectivo = $("#AnnLectivoMatricula").html();
        var codigo_grado = $("#CodigoGradoMatricula").html();
        var codigo_modalidad = $("#CodigoModalidadMatricula").html();
        var codigo_alumno = $("#IdMatricula").html();
        var codigo_seccion = $("#CodigoSeccionMatricula").html();
        var codigo_turno = $("#CodigoTurnoMatricula").html();
        // DATOS DE LOS RESPONSABLES.
        var codigo_familiar_padre = $("#codigo_familiar_00").val();
        var codigo_familiar_madre = $("#codigo_familiar_01").val();
        var codigo_familiar_otro = $("#codigo_familiar_02").val();
            const codigo_familiar_ = [codigo_familiar_padre, codigo_familiar_madre, codigo_familiar_otro];
        // DATOS DEL ID DE CADA ENCARGADO.
        var codigo_id_padre = $("#id_1").val();
        var codigo_id_madre = $("#id_2").val();
        var codigo_id_otro = $("#id_3").val();
            const codigo_id_ = [codigo_id_padre, codigo_id_madre, codigo_id_otro];
        // NOMBRE DEL OTRO ENCARGADO.
        var nombre_encargado_otro = $("#nombreotro").val();
        // NUMERO DE TELEFONO PADRE, MADRE U OTRO.
        var telefono_padre = $("#telefono1").val();
        var telefono_madre = $("#telefono2").val();
        var telefono_otro = $("#telefono3").val();
            const telefono_ = [telefono_padre, telefono_madre, telefono_otro];
        // actualizar la dirección.
        var direccion = $("#direccion1").val();
        // actualizar la dirección.
        var numeroCelular = $("#telefono4").val();
        // variable hermano en otros grados.
        if ($('#TieneHermanos').prop('checked')) {
           // alert("Checkbox is checked");
            var OtroHermano = "true";
        } else {
         //   alert("Checkbox is not checked");
            var OtroHermano = "false";
        }
        // variable para el responsable del estudiante.
            var Encargado = $('input[name=Encargado]:checked').val();
            // definir el padre es el encargado
            $En = "true"; $En2 = "false"; $En3 = "false"
            if(Encargado == 'Otro'){
                $En = "false"; $En2 = "false"; $En3 = "true"
            }else if(Encargado == 'Madre'){
                $En = "false"; $En2 = "true"; $En3 = "false"
            }
            const encargado_ = [$En,$En2,$En3];
        //
            console.log("Código estudiante: " + codigo_alumno + " Codigo modalidad: " + codigo_modalidad + " Codigo grado: " + codigo_grado + " Codigo año lectivo: " + codigo_annlectivo);
        // CUANDO SE HA SELECCIONADO UN GRADO...
            url_ajax = '{{url("getDatosMatriculaGuardar")}}' 
            csrf_token = '{{csrf_token()}}' 
            // BUSCAR LA CARGA ACADEMICA DEL DOCENTE.
            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "codigo_alumno": codigo_alumno, 
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_modalidad: codigo_modalidad,
                    codigo_grado: codigo_grado,
                    codigo_seccion: codigo_seccion,
                    codigo_turno: codigo_turno,
                    codigo_familiar: codigo_familiar_,
                    codigo_id: codigo_id_,
                    telefono: telefono_,
                    nombreotro: nombre_encargado_otro,
                    direccion: direccion,
                    telefonoEstudiante: numeroCelular,
                    otroHermano: OtroHermano,
                    encargado: encargado_,
                },
                dataType: 'json',
                success:function(data) {
                    linea = 0;
                    // colocar mensaje si existe alguno
                    // recorrer la matriz que viene del Controlador.
                    $.each(data, function( key, value) {
                        // Mostrar mensaje de la matriz.
                            console.log(value.matricula_mensaje + " Id_alumno_matricula: " + value.id_alumno_matricula + "Calificacion Mensaje: " + value.calificacion_mensaje);
                    }); // fin del for...eacht...
                                    // mensaje
                        // Display an info toast with no title
                        toastr.sucess("Matricula creada.", "Sistema");
                        //
                        // variable hermano en otros grados.
                            if ($('#TieneHermanos').prop('checked')) {
                                $('#TieneHermanos').prop('checked', false);
                            }
                }
            }); 
            // FIN DE LA CONSULTA CON AJAX
    }
//  Cerrar Ventana matricula.
    function CerrarMatricula() {
        $('#TieneHermanos').prop('checked', false);
    }
// VERIFICAR LA PROMOCION PARA EL CAMBIO DE GRADO SECCION TURNO
function promocion_verificar(codigo_grado) {
}
// CALCULAR LA ESCALA DE LA SOBREEDAD
function calcular_sobreedad_escala(edad,grado) {
    console.log(edad + " " +  grado);
        sobreedad_escala = 0;
        if(edad >= 4 && grado == "4P" ){ // 4 y cuatro años.
			if(edad == 4){
                sobreedad_escala = 0;
			}else if(edad == 5){
				sobreedad_escala = 1;
			}else if(edad == 6){
				sobreedad_escala = 2;
			}else if(edad > 6){
                sobreedad_escala = 3;
            }
		}

        if(edad >= 5 && grado == "5P" ){ // 5 y cinco años.
			if(edad == 5){
                sobreedad_escala = 0;
			}else if(edad == 6){
				sobreedad_escala = 1;
			}else if(edad == 7){
				sobreedad_escala = 2;
			}else if(edad > 8){
                sobreedad_escala = 3;
            }
		}

        if(edad >= 6 && grado == "6P" ){ // 6 y seis años.
			if(edad == 6){
                sobreedad_escala = 0;
			}else if(edad == 7){
				sobreedad_escala = 1;
			}else if(edad == 8){
				sobreedad_escala = 2;
			}else if(edad > 8){
                sobreedad_escala = 3;
            }
		}

		if(edad >= 7 && grado == "01" ){ // 7 y Primer grado.
			if(edad == 7){
                sobreedad_escala = 0;
			}else if(edad == 8){
				sobreedad_escala = 1;
			}else if(edad == 9){
				sobreedad_escala = 2;
			}else if(edad > 9){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 8 && grado == "02" ){ // 8 y segundo grado.
			if(edad == 8){
                sobreedad_escala = 0;
			}else if(edad == 9){
				sobreedad_escala = 1;
			}else if(edad == 10){
				sobreedad_escala = 2;
			}else if(edad > 10){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 9 && grado == "03" ){ // 9 y tercer grado.
			if(edad == 9){
                sobreedad_escala = 0;
			}else if(edad == 10){
				sobreedad_escala = 1;
			}else if(edad == 11){
				sobreedad_escala = 2;
			}else if(edad > 11){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 10 && grado == "04" ){ // 10 y cuarto grado.
			if(edad == 10){
                sobreedad_escala = 0;
			}else if(edad == 11){
				sobreedad_escala = 1;
			}else if(edad == 12){
				sobreedad_escala = 2;
			}else if(edad > 12){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 11 && grado == "05" ){ // 11 y quinto grado.
			if(edad == 11){
                sobreedad_escala = 0;
			}else if(edad == 12){
				sobreedad_escala = 1;
			}else if(edad == 13){
				sobreedad_escala = 2;
			}else if(edad > 13){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 12 && grado == "06" ){ // 12 y sexto grado.
			if(edad == 12){
                sobreedad_escala = 0;
			}else if(edad == 13){
				sobreedad_escala = 1;
			}else if(edad == 14){
				sobreedad_escala = 2;
			}else if(edad > 14){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 13 && grado == "07" ){ // 13 y septimo grado.
			if(edad == 13){
                sobreedad_escala = 0;
			}else if(edad == 14){
				sobreedad_escala = 1;
			}else if(edad == 15){
				sobreedad_escala = 2;
			}else if(edad > 15){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 14 && grado == "08" ){ // 14 y octavo grado.
			if(edad == 14){
                sobreedad_escala = 0;
			}else if(edad == 15){
				sobreedad_escala = 1;
			}else if(edad == 16){
				sobreedad_escala = 2;
			}else if(edad > 16){
                sobreedad_escala = 3;
            }
		}
		
		if(edad >= 15 && grado == "09" ){ // 15 y noveno grado.
			if(edad == 15){
                sobreedad_escala = 0;
			}else if(edad == 16){
				sobreedad_escala = 1;
			}else if(edad == 17){
				sobreedad_escala = 2;
			}else if(edad > 17){
                sobreedad_escala = 3;
            }
		}

        if(edad >= 16 && grado == "10" ){ // 16 y primer año.
			if(edad == 16){
                sobreedad_escala = 0;
			}else if(edad == 17){
				sobreedad_escala = 1;
			}else if(edad == 18){
				sobreedad_escala = 2;
			}else if(edad > 18){
                sobreedad_escala = 3;
            }
		}	
		
		if(edad >= 17 && grado == "11" ){ // 17 y segundo año.
			if(edad == 17){
                sobreedad_escala = 0;
			}else if(edad == 18){
				sobreedad_escala = 1;
			}else if(edad == 19){
				sobreedad_escala = 2;
			}else if(edad > 19){
                sobreedad_escala = 3;
            }
		}	

		if(edad >= 18 && grado == "12" ){ // 18 y tercer año.
			if(edad == 18){
                sobreedad_escala = 0;
			}else if(edad == 19){
				sobreedad_escala = 1;
			}else if(edad == 20){
				sobreedad_escala = 2;
			}else if(edad > 20){
                sobreedad_escala = 3;
            }
		}
		
		return sobreedad_escala;
    }
    </script>
@endsection
