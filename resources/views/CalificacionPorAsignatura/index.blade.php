@extends('layouts.app')

@section('content')
<x-slot name="header">
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Select2') }}
  </h2>
</x-slot>

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