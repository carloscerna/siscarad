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
        <h4 class="page__heading">{{$nombre_docente}}</h4>
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
                                {!! Form::select('codigo_grado_seccion_turno', ['00'=>'Selecciona...'], null, ['id' => 'codigo_grado_seccion_turno','onchange' => 'BuscarPorGradoSeccionIndicadores(this.value)', 'class' => 'form-control']) !!}
                            </div>    
                        </div>       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-body">
        <div class="jumbotron p-1">
            <h3 class="display-5">Indicadores Educativos</h3>
                <div class="row">
                    <div class="col col-md-4 col-lg-4 col-xl-4">
                        {{-- card  TOTAL DE ESTUDIANTES--}}
                        <div class="card text-white bg-primary mb-3 p-1" style="max-width: 13rem;">
                            <div class="card-header p-1"><h5>Total de Estudiantes</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                        <h2 class="text-right"><i class="fa fa-user-friends f-left float-left"></i><label for="totalEstudiantes">#</label></h2>
                                    </p>
                                        <div class="float-md-left">
                                            <i class="fas fa-male"></i>  <label for="totalEstudiantesMasculino"></label>
                                        </div>

                                        <div class="float-md-right">
                                            <i class="fas fa-female"></i>  <label for="totalEstudiantesFemenino"></label>
                                        </div>
                                        <div class="">
                                            <button type="button" class="btn btn-info btn-sm" style="display: none;" id="VerEstudiantes">Ver más...</button>
                                        </div>
                                </div>
                        </div> 
                    </div>  {{-- --}}
                    <div class="col col-md-4 col-lg-4 col-xl-4">
                        {{-- card  TOTAL DE ESTUDIANTES PRESENTES--}}
                        <div class="card text-white bg-info mb-3 p-1" style="max-width: 13rem;">
                            <div class="card-header p-1"><h5>Prensentes</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                        <h2 class="text-right"><i class="fa fa-user-check f-left float-left"></i><label for="totalEstudiantesPresentes">#</label></h2>
                                    </p>
                                        <div class="float-md-left">
                                            <i class="fas fa-male"></i>  <label for="totalEstudiantesMasculinoPresentes"></label>
                                        </div>
        
                                        <div class="float-md-right">
                                            <i class="fas fa-female"></i>  <label for="totalEstudiantesFemeninoPresentes"></label>
                                        </div>
                                </div>
                                {{-- BOTON DE INFORMACION --}}
                                <button type="button" class="btn btn-dark" id="BuscarEstudiantesPresentes">Más detalles...</button>
                            </div>
                    </div>  {{-- card  TOTAL DE ESTUDIANTES PRESENTES--}}
                    <div class="col col-md-4 col-lg-4 col-xl-4">
                        {{-- card  TOTAL DE ESTUDIANTES RETIRADOS--}}
                        <div class="card text-white bg-warning mb-3 p-1" style="max-width: 13rem;">
                            <div class="card-header p-1"><h5>Retirados</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                        <h2 class="text-right"><i class="fa fa-user-times f-left float-left"></i><label for="totalEstudiantesRetirados">#</label></h2>
                                    </p>
                                        <div class="float-md-left">
                                            <i class="fas fa-male"></i>  <label for="totalEstudiantesMasculinoRetirados"></label>
                                        </div>
        
                                        <div class="float-md-right">
                                            <i class="fas fa-female"></i>  <label for="totalEstudiantesFemeninoRetirados"></label>
                                        </div>
                                </div>
                                {{-- BOTON DE INFORMACION --}}
                                <button type="button" class="btn btn-dark btn-block" id="BuscarEstudiantesRetirados">Más detalles...</button>
                            </div>
                    </div>                             {{-- card  TOTAL DE ESTUDIANTES RETIRADOS--}}
                </div>  {{-- row --}}
        </div> {{-- JUMBOTRON --}}
        <div class="jumbotron p-1">
            <h3 class="display-5">Reportes</h3>
                <div class="row">
                    <div class="col col-md-3 col-lg-3 col-xl-3">
                        {{-- card  REPORTES--}}
                        <div class="card text-black bg-default m-1 p-1">
                            <div class="card-header p-1"><h5>Cuadro de Notas</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                    </p>
                                        {{-- BOTON DE INFORMACION --}}
                                        <button type="button" class="btn btn-dark btn-block" id="BuscarReportePorGrado" onclick="ReportePorGrado()" title="Informe">
                                            <h2><i class="fas fa-table"></i></h2>    Por Grado
                                        </button>
                                </div>
                        </div> 
                    </div>  {{-- COL --}}

                    <div class="col col-md-3 col-lg-3 col-xl-3">
                        {{-- card  REPORTES--}}
                        <div class="card text-black bg-default m-1 p-1">
                            <div class="card-header p-1"><h5>Licencias y Permisos</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                    </p>
                                        {{-- BOTON DE INFORMACION --}}
                                        <button type="button" class="btn btn-dark btn-block" id="BuscarReportePorGrado" onclick="ReporteLicenciasPermisos()" title="Informe">
                                            <h2><i class="fas fa-calendar-alt"></i></h2>Ver
                                        </button>
                                </div>
                        </div> 
                    </div>  {{-- COL --}}
                    <div class="col col-md-3 col-lg-3 col-xl-3">
                        {{-- card  REPORTES--}}
                        <div class="card text-black bg-default m-1 p-1">
                            <div class="card-header p-1"><h5>Boletas de Calificaciones</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                    </p>
                                        {{-- BOTON DE INFORMACION --}}
                                        <button type="button" class="btn btn-dark btn-block" id="BuscarReportePorGrado" onclick="ReporteBoletaCalificaciones()" title="Informe">
                                            <h2><i class="fas fa-id-badge"></i></h2>    Calificaciones
                                        </button>
                                </div>
                        </div> 
                    </div>  {{-- COL --}}

                    <div class="col col-md-3 col-lg-3 col-xl-3">
                        {{-- card  REPORTES--}}
                        <div class="card text-black bg-default m-1 p-1">
                            <div class="card-header p-1"><h5>Pre-matrícula</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                    </p>
                                        {{-- BOTON DE INFORMACION --}}
                                        <button type="button" class="btn btn-dark btn-block" id="BuscarReportePrematricula" onclick="ReportePrematricula()" title="Generar nómina de pre-matrícula">
                                            <h2><i class="fas fa-clipboard-list"></i></h2>    Generar
                                        </button>
                                </div>
                        </div> 
                    </div>  {{-- COL --}}
                    </div> {{-- DIV ROW --}}
        </div> {{-- JUMBOTRON --}}
    </div> 

    <div class="bg-info" id="NominaEstudiantes" style="display: none;">
        {{-- {{ csrf_field() }}
        {{ method_field('PATCH') }} --}}
        <div class="card">
            <div class="card-header">Estudiantes</div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table" id="TablaNominaEstudiantes">
                      <thead>
                          <tr class="bg-secondary">
                            <th>N.°</th>
                            <th>Foto</th>
                            <th>NIE</th>
                            <th>Nombre del Estudiante</th>
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
</section>
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
        $(document).ready(function() {
            $('#codigo_asignatura1').select2();
            $('#codigo_asignatura1').on('change', function(e){
                let valor = $('#codigo_asignatura1').select2('val');
                let text = $('#codigo_asignatura1 option:selected').text();
            });
        });
  
        // BOTON PARA LA BUSQUEDA DE ESTUDIANTES PRESENTES
        $("#BuscarEstudiantesPresentes").click(function () {
            BuscarPresentesRetirados('f');
        }); 

        // BOTON PARA LA BUSQUEDA DE ESTUDIANTES RETIRADOS
        $("#BuscarEstudiantesRetirados").click(function () {
            BuscarPresentesRetirados('t');
        }); 

        function BuscarPorAnnLectivo(AnnLectivo) {
            let url_ajax = '{{url("getGradoSeccion")}}'; 
            let codigo_personal = $('#codigo_personal').val();
            let codigo_annlectivo = $('#codigo_annlectivo').val();

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    "codigo_annlectivo": codigo_annlectivo
                },
                dataType: 'json',
                success:function(data) {
                    $('#contenido').empty();
                    let miselect = $("#codigo_grado_seccion_turno");
                    miselect.empty();
                    miselect.append('<option value="00">Seleccionar...</option>');
                    $.each( data, function( key, value ) {
                        miselect.append('<option value="' + value.codigo_gradoseccionturno + '">' + value.nombre_gradoseccionturno + '</option>'); 
                    });
                } 
            });
        }

        function BuscarPorGradoSeccionIndicadores(GradoSeccion) {
            $('#contenido').empty();
            let url_ajax = '{{url("getGradoSeccionIndicadores")}}'; 

            let codigo_personal = $('#codigo_personal').val();
            let codigo_annlectivo = $('#codigo_annlectivo').val();
            let codigo_grado_seccion_turno = $('#codigo_grado_seccion_turno').val();

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    "codigo_annlectivo": codigo_annlectivo,
                    "codigo_gradoseccionturno": GradoSeccion
                },
                dataType: 'json',
                success:function(data) {
                    $.each( data, function( key, value ) {
                        let masculino = Number(value.total_masculino);
                        let femenino = Number(value.total_femenino);
                        let femenino_retirado = Number(value.total_retirado_femenino);
                        let masculino_retirado = Number(value.total_retirado_masculino);
                        
                        let total_femenino =  femenino - femenino_retirado;
                        let total_masculino = masculino - masculino_retirado;
                        let total_retirados = femenino_retirado + masculino_retirado;
                        let total_estudiantes = (total_masculino + total_femenino);

                        $("label[for='totalEstudiantesFemenino']").text(value.total_femenino); 
                        $("label[for='totalEstudiantesMasculino']").text(value.total_masculino); 
                        $("label[for='totalEstudiantes']").text(value.total_estudiantes); 
                        
                        $("label[for='totalEstudiantesFemeninoPresentes']").text(total_femenino); 
                        $("label[for='totalEstudiantesMasculinoPresentes']").text(total_masculino); 
                        $("label[for='totalEstudiantesPresentes']").text(total_estudiantes); 
                        
                        $("label[for='totalEstudiantesFemeninoRetirados']").text(femenino_retirado); 
                        $("label[for='totalEstudiantesMasculinoRetirados']").text(masculino_retirado); 
                        $("label[for='totalEstudiantesRetirados']").text(total_retirados); 
                    });
                } 
            });
        }

        function BuscarPresentesRetirados(PR) {
            let codigo_annlectivo = $('#codigo_annlectivo').val();
            let codigo_institucion = $('#codigo_institucion').val();
            let codigo_gradoseccionturno = $('#codigo_grado_seccion_turno').val();
            
            if(codigo_annlectivo == '00' || codigo_gradoseccionturno == '00' || codigo_gradoseccionturno == null){
                alert('Debe seleccionar Año Lectivo y Grado-Sección-Turno');
                $('#codigo_annlectivo').focus();
            } else {
                $("#NominaEstudiantes").css("display","block");
                let url_ajax = '{{url("getGradoSeccionPresentes")}}'; 
                let codigo_personal = $('#codigo_personal').val();

                $.ajax({
                    type: "post",
                    url: url_ajax,
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": codigo_personal, 
                        "codigo_annlectivo": codigo_annlectivo,
                        "codigo_institucion": codigo_institucion,
                        "codigo_gradoseccionturno": codigo_gradoseccionturno,
                        "presentes_retirados": PR
                    },
                    dataType: 'json',
                    success:function(data) {
                        let linea = 0; 
                        let html = "";
                        $('#contenido').empty();
                        // MEJORA: Eliminado el $('#contenido').append(data) que generaba el error.

                        $.each( data, function( key, value ) {
                            linea++;
                            let fila_color = (linea % 2 === 0) ? '<tr style="background:#A5FFA5; color:black;">' : '<tr style="background:#FFFFFF; color:black;">';
                            
                            let datos_estudiantes = value.codigo_nie.trim() + "-" + value.codigo_alumno + "-" + value.codigo_matricula + "-" + codigo_gradoseccionturno + "-" + codigo_annlectivo.trim() + "-" + codigo_institucion.trim() + "-" + codigo_personal;

                            let url = '{{ url("/pdf", "id") }}'.replace('id', datos_estudiantes);

                            html += fila_color +
                            '<td>' + linea + '</td>' +
                            '<td><img src="' + value.foto + '" width="60" height="70" class="img-thumbnail"></td>' +
                            '<td>' + value.codigo_nie + '</td>' +
                            '<td>' + value.apellidos_nombres_estudiantes + '</td>' +
                            '<td><a class="btn btn-info mr-1" target="_blank" href="'+url+'-NO"><i class="fas fa-file"></i></a>'+
                                '<a class="btn btn-secondary" target="_blank" href="'+url+'-SI"><i class="fas fa-download"></i></a>'+
                            '</td></tr>';
                        });

                        $('#contenido').html(html);
                        
                        if(linea > 0) {
                            toastr.success("Registros Encontrados: " + linea, "Sistema");
                        } else {
                            toastr.info("No se encontraron estudiantes.", "Sistema");
                        }
                    } 
                });
            } 
        }

        // Funciones de Reportes
        function ReportePorGrado() {
            GenerarReporteGral('{{ url("/pdfRPG", "id") }}', "Tablero");
        }
        function ReporteBoletaCalificaciones() {
            GenerarReporteGral('{{ url("/pdf", "id") }}', "Tablero");
        }
        function ReportePrematricula() {
            GenerarReporteGral('{{ url("/prematricula", "id") }}', "Tablero");
        }
        function ReporteLicenciasPermisos() {
            let codigo_personal = $('#codigo_personal').val();
            let codigo_annlectivo = $('#codigo_annlectivo').val();
            let nombre_annlectivo = $('#codigo_annlectivo option:selected').html();
            let codigo_institucion = $("#codigo_institucion").val();

            if(codigo_annlectivo == "00"){
                toastr.warning("Debe Seleccionar Año Lectivo", "Sistema");
                return;
            }
            let datos = "Tablero-" + nombre_annlectivo.trim() + "-" + codigo_annlectivo.trim() + "-" + codigo_personal + "-" + codigo_institucion;
            AbrirVentana('{{ url("/pdfRLyP", "id") }}'.replace('id', datos));
        }

        function GenerarReporteGral(ruta_url, tablero) {
            let codigo_personal = $('#codigo_personal').val();
            let codigo_annlectivo = $('#codigo_annlectivo').val();
            let codigo_grado_seccion_turno = $('#codigo_grado_seccion_turno').val();
            let codigo_institucion = $("#codigo_institucion").val();

            if(codigo_annlectivo == "00" || codigo_grado_seccion_turno == "00" || codigo_grado_seccion_turno == null){
                toastr.warning("Debe Seleccionar Año Lectivo y Grado-Sección-Turno", "Sistema");
                return;
            }
            let datos = tablero + "-" + codigo_grado_seccion_turno + "-" + codigo_annlectivo.trim() + "-" + codigo_personal + "-" + codigo_institucion;
            AbrirVentana(ruta_url.replace('id', datos));
        }

        function AbrirVentana(url) {
            window.open(url, '_blank');
            return false;
        }
    </script>
@endsection