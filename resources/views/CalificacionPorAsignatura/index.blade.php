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
  {!! Form::select('codigo_grado_seccion_turno', ['placeholder'=>'Selecciona'], null, ['id' => 'codigo_grado_seccion_turno','onchange' => 'BuscarPorGradoSeccion(this.value)', 'class' => 'form-control']) !!}

  {{ Form::label('LblNombreAsignatura', 'Asignatura:') }}
  {!! Form::select('codigo_asignatura', ['placeholder'=>'Selecciona'], null, ['class' => 'form-control', 'id' => 'codigo_asignatura']) !!}

  {{ Form::label('LblPeriodoTrimestre', 'Período o Trimestre:') }}
  {!! Form::select('codigo_periodo', ['00'=>'Seleccionar...','01'=>'Periodo 1','02'=>'Periodo 2','03'=>'Periodo 3'], null, ['id' => 'codigo_periodo','onchange' => 'BuscarPorPeriodo(this.value)', 'class' => 'form-control']) !!}
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
            //alert(AnnLectivo + ' ' + codigo_personal);

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

                    alert("FUNCIONO" + data);
                } 
            });
        }
       // funcion onchange
        function BuscarPorGradoSeccion(GradoSeccion) {
            alert(GradoSeccion);
        }
    </script>
@endsection
        