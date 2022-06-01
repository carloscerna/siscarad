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
  {{ Form::label('LblAnnLectivo', 'Año Lectivo:') }}
  {!! Form::select('codigo_annlectivo', $annlectivo, null, ['class' => 'form-control']) !!}
</div>

<script>
  $(document).ready(function(){
      $('#select2').select2();
      $('#select2').on('change', function(e){
          let valor = $('#select2').select2('val');
          let text = $('#select2 option:selected').text();
          @this.set('seleccionado', text);
      })
  })
</script>
@endsection