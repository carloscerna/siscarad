@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h5 class="page__heading"><i class="fas fa-users mr-2"></i>Gestión de Usuarios</h5>
        </div>
        
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow">
                        
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Listado de Usuarios Activos</h6>
                            <a href="{{route('usuarios.create')}}" class="btn btn-success shadow-sm">
                                <i class="fas fa-plus-circle mr-1"></i> Nuevo Usuario
                            </a>
                        </div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mt-2" id="DatosUsuarios" width="100%" cellspacing="0">
                                    <thead class="bg-primary text-white">
                                      <tr>
                                        <th style="display:none;">ID</th>
                                        <th>Nombre</th>
                                        <th>E-mail</th>
                                        <th>Rol / Perfil</th>
                                        <th class="text-center">Acciones</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($usuarios as $usuario)
                                        <tr>
                                          <td style="display: none;">{{ $usuario->id }}</td>
                                          <td class="align-middle font-weight-bold text-dark">{{ $usuario->name }}</td>
                                          <td class="align-middle">{{ $usuario->email }}</td>
                                          <td class="align-middle">
                                            @if(!empty($usuario->getRoleNames()))
                                              @foreach($usuario->getRoleNames() as $rolNombre)                                       
                                                <span class="badge badge-info px-3 py-2 shadow-sm">{{ $rolNombre }}</span>
                                              @endforeach
                                            @endif
                                          </td>
      
                                          <td class="text-center align-middle">
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-outline-info btn-sm mr-1" href="{{ route('usuarios.edit',$usuario->id) }}" title="Editar Usuario">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
          
                                                {!! Form::open(['method' => 'DELETE','route' => ['usuarios.destroy', $usuario->id],'style'=>'display:inline', 'class' => 'frm-delete']) !!}
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar Usuario">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                {!! Form::close() !!}
                                            </div>
                                          </td>
                                        </tr>
                                      @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
{{-- 1. Cargar CSS de DataTables --}}
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

{{-- 2. Cargar JS de DataTables (Orden crítico) --}}
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Verificar si DataTable existe antes de inicializar para evitar el error de "not a function"
    if ($.fn.DataTable) {
        $('#DatosUsuarios').DataTable({
            responsive: true,
            autoWidth: false,
            destroy: true, // Permite re-inicializar si hay conflictos
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            }
        });
    }

    // Confirmación de borrado
    $('.frm-delete').submit(function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Confirmar eliminación?',
            text: "El usuario será removido del sistema permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>
@endpush