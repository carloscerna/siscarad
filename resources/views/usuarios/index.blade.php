@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
                <h5 class="page__heading">Usuarios</h5>
        </div>
        
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        
                        {{-- ==== MEJORA 1: CARD-HEADER ==== --}}
                        {{-- Añadimos un encabezado al card para el título y el botón "Nuevo" --}}
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span>Usuarios Activos</span>
                            {{-- Botón "Nuevo" con ícono --}}
                            <a href="{{route('usuarios.create')}}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Nuevo
                            </a>
                        </div>
                        
                        <div class="card-body">
                            {{-- El botón "Nuevo" se eliminó de aquí --}}

                            {{-- ==== MEJORA 2: TABLA RESPONSIVA ==== --}}
                            {{-- Envolvemos la tabla para que tenga scroll horizontal en móviles --}}
                            <div class="table-responsive">
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
                                            {{-- ==== MEJORA 3: ÍCONOS EN BOTONES ==== --}}
                                            
                                            {{-- Botón Editar con ícono y clase btn-sm (más pequeño) --}}
                                            <a class="btn btn-info btn-sm" href="{{ route('usuarios.edit',$usuario->id) }}">
                                                <i class="fas fa-pencil-alt"></i> Editar
                                            </a>
      
                                            {{-- Botón Borrar: Lo cambiamos a <button> para poder usar íconos --}}
                                            {!! Form::open(['method' => 'DELETE','route' => ['usuarios.destroy', $usuario->id],'style'=>'display:inline', 'class' => 'frm-delete']) !!}
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt"></i> Borrar
                                                </button>
                                            {!! Form::close() !!}
                                          </td>
                                        </tr>
                                      @endforeach
                                    </tbody>
                                </table>
                            </div> {{-- Fin de table-responsive --}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#DatosUsuarios').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
        }
    });

    // === MEJORA OPCIONAL: Confirmación de borrado ===
    // Esto usa SweetAlert2 para un aviso más amigable
    $('.frm-delete').submit(function(e) {
        e.preventDefault(); // Evita el envío inmediato
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡bórralo!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit(); // Si confirma, envía el formulario
            }
        });
    });
});
</script>

{{-- Si decides usar la confirmación, necesitas añadir SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush