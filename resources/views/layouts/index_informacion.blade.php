@extends('layouts.app') @section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="card-title mb-0"><i class="fas fa-search me-2"></i> Seleccionar Estudiante para Actualización</h5>
        </div>
        <div class="card-body p-4">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('estudiante.informacion.index') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por Nombre, Apellido o NIE..." value="{{ $buscar }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        @if($buscar)
                            <a href="{{ route('estudiante.informacion.index') }}" class="btn btn-secondary">Limpiar</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th>NIE</th>
                            <th>Estudiante</th>
                            <th>Estado Foto / Firma</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alumnos as $al)
                            <tr>
                                <td class="fw-bold">{{ $al->codigo_nie ?? 'N/A' }}</td>
                                <td>{{ trim($al->apellido_paterno) }} {{ trim($al->apellido_materno) }}, {{ trim($al->nombre_completo) }}</td>
                                <td>
                                    @if($al->foto)
                                        <span class="badge badge-success"><i class="fas fa-camera"></i> Con Foto</span>
                                    @else
                                        <span class="badge badge-warning text-dark"><i class="fas fa-camera"></i> Sin Foto</span>
                                    @endif

                                    @if($al->encargadoPrincipal && $al->encargadoPrincipal->firma_autorizacion)
                                        <span class="badge badge-success"><i class="fas fa-signature"></i> Firmado</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-signature"></i> Falta Firma</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('estudiante.informacion.edit', $al->id_alumno) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                        <i class="fas fa-user-edit"></i> Gestionar Info
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No se encontraron estudiantes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $alumnos->appends(['buscar' => $buscar])->links() }}
            </div>

        </div>
    </div>
</div>
@endsection