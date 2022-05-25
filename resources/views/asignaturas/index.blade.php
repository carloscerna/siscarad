@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">asignaturas</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                
            
                        @can('crear-asignatura')
                        <a class="btn btn-warning" href="{{ route('asignaturas.create') }}">Nuevo</a>
                        @endcan
            
                        <table class="table table-striped mt-2">
                                <thead style="background-color:#6777ef">                                     
                                    <th style="display: none;">ID</th>
                                    <th style="color:#fff;">Nombre</th>
                                    <th style="color:#fff;">Código</th>                                    
                                    <th style="color:#fff;">Acciones</th>                                                                   
                              </thead>
                              <tbody>
                            @foreach ($asignatura as $asignaturas)
                            <tr>
                                <td style="display: none;">{{ $asignaturas->id_asignatura }}</td>                                
                                <td>{{ $asignaturas->nombre }}</td>
                                <td>{{ $asignaturas->codigo }}</td>
                                <td>
                                    <form action="{{ route('asignaturas.destroy',$asignaturas->id_asignatura) }}" method="POST">                                        
                                        @can('editar-asignatura')
                                        <a class="btn btn-info" href="{{ route('asignaturas.edit',$asignaturas->id_asignatura) }}">Editar</a>
                                        @endcan

                                        @csrf
                                        @method('DELETE')
                                        @can('borrar-asignatura')
                                        <button type="submit" class="btn btn-danger">Borrar</button>
                                        @endcan
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <!-- Ubicamos la paginacion a la derecha -->
                        <div class="pagination justify-content-end">
                            {!! $asignatura->links() !!}
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
