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
                                {!! Form::select('codigo_annlectivo', ['placeholder'=>'Selecciona'] + $annlectivo, null, ['id' => 'codigo_annlectivo', 'onchange' => 'BuscarPorAnnLectivo(this.value)','class' => 'form-control']) !!}
                            </div>
        
                            <div class="col col-md-6 col-lg-6 col-xl-6">
                                {{ Form::label('LblGradoSeccionTurno', 'Grado-Sección-Turno:') }}
                                {!! Form::select('codigo_grado_seccion_turno', ['placeholder'=>'Selecciona'], null, ['id' => 'codigo_grado_seccion_turno','onchange' => 'BuscarPorGradoSeccionIndicadores(this.value)', 'class' => 'form-control']) !!}
                            </div>    
                        </div>       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-body">
        <div class="jumbotron p-3">
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
                                            <i class="fas fa-male"></i> Masculino: <label for="totalEstudiantesMasculino"></label>
                                        </div>

                                        <div class="float-md-right">
                                            <i class="fas fa-female"></i> Femenino: <label for="totalEstudiantesFemenino"></label>
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
                                        <h2 class="text-right"><i class="fa fa-user-friends f-left float-left"></i><label for="totalEstudiantesPresentes">#</label></h2>
                                    </p>
                                        <div class="float-md-left">
                                            <i class="fas fa-male"></i> Masculino: <label for="totalEstudiantesMasculinoPresentes"></label>
                                        </div>
        
                                        <div class="float-md-right">
                                            <i class="fas fa-female"></i> Femenino: <label for="totalEstudiantesFemeninoPresentes"></label>
                                        </div>
                                </div>
                            </div>
                    </div>                             {{-- card  TOTAL DE ESTUDIANTES PRESENTES--}}
                    <div class="col col-md-4 col-lg-4 col-xl-4">
                        {{-- card  TOTAL DE ESTUDIANTES RETIRADOS--}}
                        <div class="card text-white bg-warning mb-3 p-1" style="max-width: 13rem;">
                            <div class="card-header p-1"><h5>Retirados</h5></div>
                                <div class="card-body p-1">
                                    <h5 class="card-title"></h5>
                                    <p class="card-text">
                                        <h2 class="text-right"><i class="fa fa-user-friends f-left float-left"></i><label for="totalEstudiantesRetirados">#</label></h2>
                                    </p>
                                        <div class="float-md-left">
                                            <i class="fas fa-male"></i> Masculino: <label for="totalEstudiantesMasculinoRetirados"></label>
                                        </div>
        
                                        <div class="float-md-right">
                                            <i class="fas fa-female"></i> Femenino: <label for="totalEstudiantesFemeninoRetirados"></label>
                                        </div>
                                </div>
                            </div>
                    </div>                             {{-- card  TOTAL DE ESTUDIANTES RETIRADOS--}}
                </div>  {{-- row --}}
        </div> {{-- JUMBOTRON --}}
    </div> 
                 {{-- <div class="jumbotron">
                    <div class="row">
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card text-white bg-primary mb-3" style="max-width: 18rem;">
                                <div class="card-header"><h5>Total de Estudiantes</h5></div>
                                <div class="card-body">
                                  <h5 class="card-title"></h5>
                                  <p class="card-text">
                                    <h2 class="text-right"><i class="fa fa-users f-left float-left"></i><label for="totalEstudiantes">#</label></h2>
                                  </p>
                                </div>
                              </div>
                            <div class="card bg-primary order-card">
                                <div class="card-block m-1">
                                    <h5>Estudiantes</h5>                                               
                                        <h2 class="text-right"><i class="fa fa-users f-left float-left"></i><label for="totalEstudiantes">#</label></h2>
                                        <p class="m-b-0 text-right"><a href="#" class="text-white"> Ver más. . . </a></p>
                                </div>
                            </div> 
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card bg-info order-card">
                                <div class="card-block m-1">
                                <h5>Presentes</h5>                                               
                                    <h2 class="text-right"><i class="fa fa-user-check f-left float-left"></i><span></span></h2>
                                    <p class="m-b-0 text-right"><a href="#" class="text-white"> . . . </a></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card bg-warning order-card">
                                <div class="card-block m-1">
                                <h5>Retirados</h5>                                               
                                    <h2 class="text-right"><i class="fa fa-user-times f-left float-left"></i><span></span></h2>
                                    <p class="m-b-0 text-right"><a href="#" class="text-white"> . . . </a></p>
                                </div>
                            </div>
                        </div> 
                     </div>  
                </div>  --}}
</section>
@endrole

{{-- <h6 id="totaldeEstudiantes">
    <label for="totalEstudiantesMasculino"></label> - Masculino
    <label for="totalEstudiantesFemenino"></label> - Femenino
</h6>
<h6><label for="totalEstudiantes"></label> - Estudiantes</h6> --}}


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
  
        // funcion onchange
        function BuscarPorAnnLectivo(AnnLectivo) {
            url_ajax = '{{url("getGradoSeccion")}}' 
            csrf_token = '{{csrf_token()}}' 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    codigo_annlectivo: codigo_annlectivo
                },
                dataType: 'json',
                success:function(data) {
                     var miselect=$("#codigo_grado_seccion_turno");
                             miselect.empty();
                             miselect.append('<option value="">Seleccionar...</option>');
                                 $.each( data, function( key, value ) {
                                        //console.log(value.codigo_gradoseccionturno);
                                        //console.log(value.nombre_gradoseccionturno);
                                            miselect.append('<option value="' + value.codigo_gradoseccionturno + '">' + value.nombre_gradoseccionturno + '</option>'); 
                                });
                } 
            });
        }
        // funcion onchange. CUANDO SELECCIONO EL GRADO Y SECCION
        function BuscarPorGradoSeccionIndicadores(GradoSeccion) {
            url_ajax = '{{url("getGradoSeccionIndicadores")}}' 
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
                    codigo_gradoseccionturno: GradoSeccion
                },
                dataType: 'json',
                success:function(data) {
                    $.each( data, function( key, value ) {
                        console.log(" Total: " + value.total_estudiantes + 
                                    " Total Masculino: " + value.total_masculino + 
                                    " Total Femenino: " + value.total_femenino + 
                                    " Presentes: " + value.presentes + 
                                    " Retirados Masculino: " + value.total_retirado_masculino + 
                                    " Retirados Masculino: " + value.total_retirado_femenino + 
                                    " Sobreedad: " + value.sobreedad +
                                    " Repitentes: " + value.repitentes);
                                     // TOTAL DE ALUMNOS MASCULINO Y FEMENINO
                                            var masculino = Number(value.total_masculino);
                                            var femenino = Number(value.total_femenino);
                                            // TOTAL DE ALUMNOS MASCULINO Y FEMENINO RETIRADOS.
                                            var femenino_retirado = Number(value.total_retirado_femenino);
                                            var masculino_retirado = Number(value.total_retirado_masculino);
                                            // TOTAL DE ALUMNOS MASCULINO Y FEMENINO RETIRADOS.
                                                var total_femenino =  femenino - femenino_retirado;
                                                var total_masculino = masculino - masculino_retirado;
                                                var total_retirados = femenino_retirado + masculino_retirado;
                                            // TOTAL DE ALUMNOS.
                                            var total_estudiantes = (total_masculino + total_femenino);
                                        // COLOAR VALOR EN LA ETIQUETA PARA LOS INDICADORES MASCULINO Y FEMENINO.  
                                            $("label[for='totalEstudiantesFemenino']").text(value.total_femenino); 
                                            $("label[for='totalEstudiantesMasculino']").text(value.total_masculino); 
                                            $("label[for='totalEstudiantes']").text(value.total_estudiantes); 
                                        // COLOAR VALOR EN LA ETIQUETA PARA LOS INDICADORES MASCULINO Y FEMENINO.  
                                            $("label[for='totalEstudiantesFemeninoPresentes']").text(total_femenino); 
                                            $("label[for='totalEstudiantesMasculinoPresentes']").text(total_masculino); 
                                            $("label[for='totalEstudiantesPresentes']").text(total_estudiantes); 
                                        // COLOAR VALOR EN LA ETIQUETA PARA LOS INDICADORES MASCULINO Y FEMENINO.  RETIRADOS
                                            $("label[for='totalEstudiantesFemeninoRetirados']").text(femenino_retirado); 
                                            $("label[for='totalEstudiantesMasculinoRetirados']").text(masculino_retirado); 
                                            $("label[for='totalEstudiantesRetirados']").text(total_retirados); 
                    });
                } 
            });
        }

    </script>
@endsection