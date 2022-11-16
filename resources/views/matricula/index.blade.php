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
    <!-- The modal PARA LOS DATOS DE LA MATRICULA.-->  
</section>

<!-- Modal -->
<div id="MyMatricula" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
      <!-- Contenido del modal -->
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h4 class="modal-title">¡Matricular!</h4>
          <label style="text-color: white"></label><span class="badge badge-primary" id="PromocionMatricula">#</span>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <form>
                <!-- CODIGO Y NOMBRE -->
                <div class="row">
                  <div class="col">
                    <label style="text-color: white"> Id: </label><span class="badge badge-light" id="IdMatricula">#</span>
                    <label style="text-color: white"> Estudiante: </label><span class="badge badge-light" id="EstudianteMatricula">#</span>
                  </div>
                </div>

                    <!-- AÑO LECTIVO Y NIVEL-GRADO-SECCION-TURNO -->
                    <div class="row">
                        <div class="col">
                            <label style="text-color: white"> Año lectivo: </label><span class="badge badge-light" id="AnnLectivoMatricula">#</span>

                            <label style="text-color: white"> Código Modalidad: </label><span class="badge badge-light" id="CodigoModalidadMatricula">#</span>
                            <label style="text-color: white"> Código Grado: </label><span class="badge badge-light" id="CodigoGradoMatricula">#</span>
                            <label style="text-color: white"> Código Sección: </label><span class="badge badge-light" id="CodigoSeccionMatricula">#</span>
                            <label style="text-color: white"> Código Turno: </label><span class="badge badge-light" id="CodigoTurnoMatricula">#</span>
                        </div>
                    <!-- INFORMACIÓN DEL RESPONSABLE -->
                    </div>
            </form>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-success" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="GuardarMatricula()">Guardar</button>
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
                    //
                    // variables a utilizar.
                    //
                    var linea = 0; var html= ""; var presentes_ = 0; var retirados_ = 0; var sobreedad_ = 0; var promovidos_ = 0; var no_promovidos_ = 0; var sinsobreedad_ = 0;
                    var matricula_ = 0;  var cantidad_escala_0 = 0; var cantidad_escala_1 = 0; var cantidad_escala_2 = 0; var cantidad_escala_3 = 0; var matricula = true;
                        $('#contenido').empty();
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
                                }
                            // Validar Sobreedad.
                                if(sobreedad == ""){
                                    var td_sobreedad = "<td class='bg-primary text-white text-small'> Sin Sobreedad </td>";
                                    sinsobreedad_++;
                                }else{
                                    var td_sobreedad = "<td class='bg-warning text-white text-small'> Sobreedad </td>";
                                    sobreedad_++;
                                    matricula = false;
                                }
                                // Validar Sobreedad.
                                if(codigo_resultado == "3"){
                                    var td_codigo_resultado = "<td class='bg-primary text-white text-small'> Promovido </td>";
                                    promovidos_++;
                                    matricula = true;
                                }else{
                                    var td_codigo_resultado = "<td class='bg-info text-white text-small'> No Promovido </td>";
                                    no_promovidos_++;
                                    matricula = false;
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

        //alert("Nombre: " + nombre + " Código: " + codigo_alumno + " Nie: " + nie);           
        $("#IdMatricula").text(codigo_alumno);
        $("#EstudianteMatricula").text(nombre);

        $("#PromocionMatricula").text(promocion);
        // Validar si es promovido o no promovido.
        if(promocion.trim() == "Promovido"){
            //promocion_verificar(codigo_gradoseccionturno)
            codigo_grado = codigo_gradoseccionturno.substring(0,2);
            codigo_seccion = codigo_gradoseccionturno.substring(2,4);
            codigo_turno = codigo_gradoseccionturno.substring(4,6);
            codigo_modalidad = codigo_gradoseccionturno.substring(6,8);

            $("#AnnLectivoMatricula").text(codigo_annlectivo);
            $("#CodigoModalidadMatricula").text(codigo_modalidad);    
            $("#CodigoGradoMatricula").text(codigo_grado);    
            $("#CodigoSeccionMatricula").text(codigo_seccion);    
            $("#CodigoTurnoMatricula").text(codigo_turno);    
            
        }

        if(promocion == "No Promovido"){
            
        }
        // form modal.
        $("#MyMatricula").modal("show");
    });
// funcionar para guardar las calificaciones.
    function GuardarMatricula() {
        alert("Proceso para guardar la matricula");
    }
// VERIFICAR LA PROMOCION PARA EL CAMBIO DE GRADO SECCION TURNO
function promocion_verificar(gradoseccionturno) {
    codigo_grado = codigo_asignatura_area.substring(0,2);
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
