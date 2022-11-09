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

    <div class="section-body">
        <div class="jumbotron p-3">
            <h3 class="display-5">Matricula</h3>
                <div class="row">
                    <div class="col col-md-4 col-lg-4 col-xl-4">
                    </div>
                </div>  {{-- row --}}
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
        $('#codigo_asignatura1').select2();
            $('#codigo_asignatura1').on('change', function(e){
                let valor = $('#codigo_asignatura1').select2('val');
                let text = $('#codigo_asignatura1 option:selected').text();
                //@this.set('seleccionado', text);
            })
  
       // funcion onchange
        function BuscarPorAnnLectivo(AnnLectivo) {
            url_ajax = '{{url("getGradoSeccionMatricula")}}' 
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
                    // limpiar empty NominaEstudiantes
                        $('#contenido').empty();
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
       

        // FUNCION PARA PRESENTES O RETIRADOS.
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
                    var linea = 0; var html= "";
                    $('#contenido').empty();
                    $('#contenido').append(data);
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
                            // Validar retirado.
                                if(retirado == ""){
                                    var td_retirado = "<td class='bg-primary text-white'> Presente </td>";
                                }else{
                                    var td_retirado = "<td class='bg-danger text-white'> Retirado </td>";
                                }
                            // Validar Sobreedad.
                                if(sobreedad == ""){
                                    var td_sobreedad = "<td class='bg-primary text-white'> Sin Sobreedad </td>";
                                }else{
                                    var td_sobreedad = "<td class='bg-warning text-white'> Sobreedad </td>";
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
                        // armar el thml de la tabla.
                        html += fila_color +
                        '<td>' + linea + '</td>' +
                        '<td>' + value.codigo_nie + '</td>' +
                        '<td>' + value.apellidos_nombres_estudiantes + '</td>' +
                        '<td>' + edad + '</td>' +
                        td_retirado +
                        td_sobreedad +
                        '</tr>';
                    });
                    $('#contenido').html(html);
                    $('#contenido').focus();
                        // Display an info toast with no title
                        toastr.success("Registros Encontrados... " + linea, "Sistema");
                } 
            });
            } 
        }
    </script>
@endsection