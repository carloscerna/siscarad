@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
                <h5 class="page__heading">Usuarios</h5>
        </div>
        <div class="row">
          <div class="col-12">
            {{$usuarios}}  
          </div>
      </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <a href="{{route('usuarios.create')}}" class="btn btn-success">Nuevo</a>

                            <table class="table table-striped mt-2" id="DatosUsuarios">
                                <thead style="background-color:#6777ef">
                                  <tr>
                                    <th style="display:none;">ID</th>
                                    <th style="color:#fff;">Nombre</th>
                                    <th style="color:#fff;">E-mail</th>
                                    <th style="color:#fff;">Rol</th>
                                    <th style="color:#fff;">Acciones</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach ($usuarios as $usuario)
                                    <tr>
                                      <td style="display: none;">{{ $usuario->id }}</td>
                                      <td>{{ $usuario->name }}</td>
                                      <td>{{ $usuario->email }}</td>
                                      <td>
                                        @if(!empty($usuario->getRoleNames()))
                                          @foreach($usuario->getRoleNames() as $rolNombre)                                       
                                            <h5><span class="badge badge-dark">{{ $rolNombre }}</span></h5>
                                          @endforeach
                                        @endif
                                      </td>
  
                                      <td>                                  
                                        <a class="btn btn-info" href="{{ route('usuarios.edit',$usuario->id) }}">Editar</a>
  
                                        {!! Form::open(['method' => 'DELETE','route' => ['usuarios.destroy', $usuario->id],'style'=>'display:inline']) !!}
                                            {!! Form::submit('Borrar', ['class' => 'btn btn-danger']) !!}
                                        {!! Form::close() !!}
                                      </td>
                                    </tr>
                                  @endforeach
                                </tbody>
                            </table>
                                <!-- Centramos la paginacion a la derecha -->
                          <div class="pagination justify-content-end">
                            {!! $usuarios->links() !!}
                          </div>  
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection