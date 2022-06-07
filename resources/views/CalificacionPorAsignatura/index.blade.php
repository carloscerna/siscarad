@extends('layouts.app')
/* llamada de valores
@php
use Illuminate\Support\Facades;
    $correo_docente = Auth::user()->email;                                        
    $nombre_docente = Auth::user()->name;
    $codigo_personal = Auth::user()->codigo_personal;                                        
@endphp
@section('content')
@role("Docente")
<section class="section">
    <div class="section-header mb-1">
        <h4 class="page__heading">{{$nombre_docente}} - {{$codigo_personal}}</h4>
    </div>
    <div class="section-body">
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
  {!! Form::select('codigo_asignatura', ['placeholder'=>'Selecciona'], null, ['class' => 'form-control', 'id' => 'codigo_asignatura']) !!}

  {{ Form::label('LblPeriodoTrimestre', 'Período o Trimestre:') }}
  {!! Form::select('codigo_periodo', ['00'=>'Seleccionar...','01'=>'Periodo 1','02'=>'Periodo 2','03'=>'Periodo 3'], null, ['id' => 'codigo_periodo','onchange' => 'BuscarPorPeriodo(this.value)', 'class' => 'form-control']) !!}

  {{ Form::label('LblActividadPorcentaje', 'Actividades (%):') }}
  {!! Form::select('codigo_actividad_porcentaje', ['00'=>'Seleccionar...','01'=>'Actividad 1 (35%)','02'=>'Actividad 2 (35%)','03'=>'Examen o Prueba Objetiva (30%)'], null, ['id' => 'codigo_periodo','onchange' => 'BuscarPorActividadPorcentaje(this.value)', 'class' => 'form-control']) !!}
</div>


<div class="bg-light" id="NominaEstudiantes" style="display: none;">
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
                      <tr>
                          <td colspan = "4" style="text-align: right;">
                                    <button type="button" class="btn btn-success" id = "goCalificacionGuardar">
                                        Guardar
                                    </button>
                          </td>
                      </tr>
                  </tfoot>
                </table>
              </div>
        </div>
        <div class="card-footer">

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
       // funcion onchange
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
                                        console.log(value.codigo_asignatura);
                                        console.log(value.nombre_asignatura);
                                            miselect.append('<option value="' + value.codigo_asignatura + '">' + value.nombre_asignatura + '</option>'); 
                                });
                } 
            });
        }
        // funcion onchange
        function BuscarPorPeriodo(Periodo) {
        // Botón Otro... visible.
            $("#NominaEstudiantes").css("display","none");
        // 	lIMPIAR SECTION QUE CONTIENE EL PORTAFOLIO.
		    $('#ListarPortafolio').empty();
        }

        // funcion onchange
        function BuscarPorActividadPorcentaje(ActividadPorcentaje) {
			// Botón Otro... visible.
				$("#NominaEstudiantes").css("display","block");
            //
            url_ajax = '{{url("getGradoSeccionCalificacionesAsignaturas")}}' 
            csrf_token = '{{csrf_token()}}' 

            codigo_personal = $('#codigo_personal').val();
            codigo_annlectivo = $('#codigo_annlectivo').val();
            codigo_asignatura = $("#codigo_asignatura").val();
            codigo_periodo = $("#codigo_periodo").val();
            codigo_gradoseccionturno = $("#codigo_grado_seccion_turno").val();

            $.ajax({
                type: "post",
                url: url_ajax,
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": codigo_personal, 
                    codigo_annlectivo: codigo_annlectivo,
                    codigo_gradoseccionturno: codigo_gradoseccionturno,
                    codigo_asignatura: codigo_asignatura,
                    codigo_actividad: ActividadPorcentaje,
                    codigo_periodo: codigo_periodo
                },
                dataType: 'json',
                success:function(data) {
                    var linea = 0;
                    $.each( data, function( key, value ) {
                        linea = linea + 1;
                        console.log(value.codigo_calificacion);
                        console.log(value.codigo_nombre_asignatura);
                        console.log(value.nombre_completo);

                        html += '<tr>' +
                        '<td>' + linea + '</td>' +
                        '<td>' + value.codigo_calificacion + '</td>' +
                        '<td>' + value.codigo_nombre_asignatura + '</td>' +
                        '<td>' + value.nombre_completo + '</td>' +
                        '</tr>';

                    });
                        $('#TablaNominaEstudiantes').html(html);
                } 
            });
        }
    </script>
@endsection
        