@extends('layouts.app')

@php
// llamada de valores
use Illuminate\Support\Facades;
    $correo_docente = Auth::user()->email;                                        
    $nombre_docente = Auth::user()->name;
    $codigo_personal = Auth::user()->codigo_personal;                                        
@endphp
@section('content')
@role("Docente")
<section class="section">
    <div class="section-header">
        <h4 class="page__heading">{{$nombre_docente}} - {{$codigo_personal}}</h4>
    </div>
    <div class="section-body mb-0 h-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                </div>
            </div>
        </div>
    </div>
</section>
@endrole

    <div class="form-group">
        {!! Form::hidden('codigo_personal', $codigo_personal,['id'=>'codigo_personal', 'class'=>'form-control']) !!}
        {{ Form::label('LblAnnLectivo', 'Año Lectivo:') }}
        {!! Form::select('codigo_annlectivo', ['placeholder'=>'Selecciona'] + $annlectivo, null, ['id' => 'codigo_annlectivo', 'onchange' => 'BuscarPorAnnLectivo(this.value)','class' => 'form-control']) !!}

        {{ Form::label('LblGradoSeccionTurno', 'Grado-Sección-Turno:') }}
        {!! Form::select('codigo_grado_seccion_turno', ['placeholder'=>'Selecciona'], null, ['id' => 'codigo_grado_seccion_turno','onchange' => 'BuscarPorGradoSeccionAsignaturas(this.value)', 'class' => 'form-control']) !!}

        {{ Form::label('LblNombreAsignatura', 'Asignatura:') }}
        {!! Form::select('codigo_asignatura', ['placeholder'=>'Selecciona'], null, ['class' => 'form-control', 'id' => 'codigo_asignatura', 'onchange' => 'BuscarPorAsignatura(this.value)']) !!}
        {!! Form::hidden('codigo_area', '00',['id'=>'codigo_area', 'class'=>'form-control']) !!}

        {{ Form::label('LblPeriodoTrimestre', 'Período o Trimestre:') }}
        {!! Form::select('codigo_periodo', ['00'=>'Seleccionar...','01'=>'Periodo 1','02'=>'Periodo 2','03'=>'Periodo 3'], null, ['id' => 'codigo_periodo','onchange' => 'BuscarPorPeriodo(this.value)', 'class' => 'form-control']) !!}

        {{ Form::label('LblActividadPorcentaje', 'Actividades (%):') }}
        {!! Form::select('codigo_actividad_porcentaje', ['00'=>'Seleccionar...','01'=>'Actividad 1 (35%)','02'=>'Actividad 2 (35%)','03'=>'Examen o Prueba Objetiva (30%)'], null, ['id' => 'codigo_actividad_porcentaje','onchange' => 'BuscarPorActividadPorcentaje(this.value)', 'class' => 'form-control']) !!}
    </div>

<div class="bg-light" id="NominaEstudiantes" style="display: none;">
    {{-- {{ csrf_field() }}
    {{ method_field('PATCH') }} --}}
    <div class="card">
        <div class="card-header bg-success">Estudiantes</div>
        <div class="card-body">
            <div class="table-responsive-sm">
                <table class="table" id="TablaNominaEstudiantes">
                  <thead>
                      <tr>
                        <th>N.ª</th>
                        <th>Nombre del Estudiante</th>
                        <th>Calificación</th>
                        <th>Nota Promedio</th>
                      </tr>
                  </thead>
                  <tbody id="contenido">
                      <tr>
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
                          <button type="button" class="btn btn-success" id = "goCalificacionGuardar" onclick="GuardarRegistros()">
                              Guardar
                          </button>
                </td>
            </tr>
        </div>
      </div>
</div>
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
        // function onchange- CUANDO SELECCIONO LA ASIGNATURA.
        function BuscarPorAsignatura(CodigoAsignatura) {
            // SELECCIONAR EL PRIMERO ELEMENTO DE CADA SELECT Y LIMPIAR LA TABLA.
                $('#codigo_periodo').val('00');
                $('#codigo_actividad_porcentaje').val('00');
                $('#codigo_area').val(CodigoAsignatura);
                $('#contenido').empty();
        }
       // funcion onchange. CUANDO SELECCIONO EL GRADO Y SECCION
        function BuscarPorGradoSeccionAsignaturas(GradoSeccion) {
            url_ajax = '{{url("getGradoSeccionAsignaturas")}}' 
            csrf_token = '{{csrf_token()}}' 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();

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
                     var miselect=$("#codigo_asignatura");
                             miselect.empty();
                             miselect.append('<option value="">Seleccionar...</option>');
                                 $.each( data, function( key, value ) {
                                        console.log("codigo_asignatura: " + value.codigo_asignatura + " codigo area: " + value.codigo_area + " Nombre: " + value.nombre_asignatura);
                                            miselect.append('<option value="' + value.codigo_asignatura + value.codigo_area + '">' + value.nombre_asignatura + '</option>'); 
                                    // rellenar hidden
                                        $("#codigo_area").val(value.codigo_area);
                                    // SELECCIONAR EL PRIMERO ELEMENTO DE CADA SELECT Y LIMPIAR LA TABLA.
                                        $('#codigo_asignatura option:nth-child(1)').val();
                                        $('#codigo_periodo').val('00');
                                        $('#codigo_actividad_porcentaje').val('00');
                                        $('#contenido').empty();
                                });
                } 
            });
        }

        // funcion onchange
        function BuscarPorPeriodo(Periodo) {
            codigo_asignatura_area = $("#codigo_asignatura").val();
            conteo_codigo_asignatura = codigo_asignatura_area.length;

            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
            }else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
            }
            if(codigo_area == '01' || codigo_area == '02' || codigo_area == '03' || codigo_area == '08'){
                miselect = $("#codigo_actividad_porcentaje");
                miselect.empty();
                miselect.append('<option value=00>Seleccionar...</option>'); 
                miselect.append('<option value=01>Actividad 1 (35%)</option>'); 
                miselect.append('<option value=02>Actividad 2 (35%)</option>'); 
                miselect.append('<option value=03>Examen o Prueba Objetiva (30%)</option>'); 
            }else{
                miselect = $("#codigo_actividad_porcentaje");
                miselect.empty();
                miselect.append('<option value=00>Seleccionar...</option>'); 
                miselect.append('<option value=01>Periodo 1</option>'); 
                miselect.append('<option value=02>Periodo 2</option>'); 
                miselect.append('<option value=03>Periodo 3</option>'); 
                    // Llamar a la funciond e busqueda
                    BuscarPorActividadPorcentaje(Periodo);
            }
            // Botón Otro... visible.
            $("#NominaEstudiantes").css("display","none");
            // 	lIMPIAR SECTION QUE CONTIENE EL PORTAFOLIO.
		    $('#ListarPortafolio').empty();
            // SELECCIONAR EL PRIMERO ELEMENTO DE CADA SELECT Y LIMPIAR LA TABLA.
                $('#codigo_actividad_porcentaje').val('00');
                $('#contenido').empty();
        }

        // funcion onchange. CUANDO SELECCIONO EL PERIODO
        function BuscarPorActividadPorcentaje(ActividadPorcentaje) {
			// Botón Otro... visible.
				$("#NominaEstudiantes").css("display","block");
            //
            url_ajax = '{{url("getGradoSeccionCalificacionesAsignaturas")}}'; 
            csrf_token = '{{csrf_token()}}'; 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            codigo_asignatura_area = $("#codigo_asignatura").val();
            conteo_codigo_asignatura = codigo_asignatura_area.length;
            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
            }else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
            }
            
            codigo_periodo = $("#codigo_periodo").val();
            codigo_gradoseccionturno = $("#codigo_grado_seccion_turno").val();

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_gradoseccionturno: codigo_gradoseccionturno,
                    codigo_asignatura: codigo_asignatura,
                    codigo_area: codigo_area,
                    codigo_actividad: ActividadPorcentaje,
                    codigo_periodo: codigo_periodo
                },
                dataType: 'json',
                success:function(data) {
                    var linea = 0; var html= "";
                    $('#contenido').empty();
                    $('#contenido').append(data);
                    $.each( data, function( key, value ) {
                        linea = linea + 1;
                        
                        html += '<tr>' +
                        '<td>' + linea + '</td>' +
                        '<td>' + value.codigo_nie + '</td>' +
                        '<td>' + value.full_name + '</td>' +
                        "<td><input type=number step=0.1 class=form-control name=calificacion id=calificacion value=" + value.nota_actividad + " max=10.0 min=0.0 maxlength=4 oninput='maxLengthNumber(this)'>" +
                            "<input type=hidden class=form-control name=codigo_calificacion id=codigo_calificacion value=" + value.id_notas + ">"+"</td>" +
                        "<td><input type=hidden name=_method value=PUT>"+
                        '</tr>';

                    });
                    $('#contenido').html(html);
                    toastr.error('{{ Session::get('error') }}');

                } 
            });
        }
        // funcionar para guardar las calificaciones.
        function GuardarRegistros() {
            csrf_token = '{{csrf_token()}}';

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            codigo_asignatura_area = $("#codigo_asignatura").val();
            conteo_codigo_asignatura = codigo_asignatura_area.length;
            if(conteo_codigo_asignatura == 4){
                codigo_asignatura = codigo_asignatura_area.substring(0,2);
                codigo_area = codigo_asignatura_area.substring(2,4);
            }else{
                codigo_asignatura = codigo_asignatura_area.substring(0,3);
                codigo_area = codigo_asignatura_area.substring(3,5);
            }
            codigo_actividad = $("#codigo_actividad_porcentaje").val();
            codigo_periodo = $("#codigo_periodo").val();
            codigo_gradoseccionturno = $("#codigo_grado_seccion_turno").val();
            // leer tabla de datos con ID y calificaciòn.
            var $objCuerpoTabla=$("#TablaNominaEstudiantes").children().prev().parent();
                var codigo_calificacion_ = []; var calificacion_ = [];               
                var fila = 0;
                // recorre el contenido de la tabla.
                $objCuerpoTabla.find("tbody tr").each(function(){
                                var codigo_calificacion =$(this).find('td').eq(3).find("input[name='codigo_calificacion']").val();
                                var calificacion =$(this).find('td').eq(3).find("input[name='calificacion']").val();
                        // dar valor a las arrays.
                        codigo_calificacion_[fila] = codigo_calificacion;
                        calificacion_[fila] = calificacion;
                        fila = fila + 1;
                });
                url_ajax = "{{ URL('/getActualizarCalificacion') }}"; 
            //////
            $.ajax({
                type: "PUT",
                url: url_ajax,
                data: {
                    _token:'{{ csrf_token() }}',
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_gradoseccionturno: codigo_gradoseccionturno,
                    codigo_asignatura: codigo_asignatura,
                    codigo_area: codigo_area,
                    codigo_actividad: codigo_actividad,
                    codigo_periodo: codigo_periodo,
                    codigo_calificacion: codigo_calificacion_,
                    calificacion: calificacion_,
                    fila: fila
                },
                dataType: 'json',
                success:function(data) {
                   $('#codigo_annlectivo').focus();
                    toastr["success"]("Guardado", "HI");
                } 
            });
        }


        function maxLengthNumber(valor) {
            console.log(valor.value);
            {
		    var amount = valor.value;
		    console.log(amount);
                   //d+ permite caracteres enteros
                   //si hay un caracter que no es dígito entonces evalua lo que está en paréntesis (?) significa opcional
		    var patron = /^(\d+(.{1}\d{1})?)$/;     		    
                    if (!patron.test(amount))
		   		 	{
		       	 		console.log('cantidad ingresada incorrectamente');
		        		valor.value = "";
		        		return false;
        			}
		   			 else if(amount > 10){
                     console.log('cantidad ingresada incorrectamente');
                     valor.value = "";
                     return false;
                     }
                     else{
		        		return true;}
		  }
        }
    </script>
@endsection
        