@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    .dataTables_filter input {
        border-radius: 20px;
        padding-left: 15px;
        border: 1px solid #ced4da;
    }
    .table th {
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0"><i class="fas fa-users-cog me-2"></i> Control de Información: Fotos y Firmas</h5>
            <span class="badge badge-light text-primary fw-bold">{{ count($alumnos) }} Estudiantes Asignados</span>
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

            <div class="table-responsive">
                <table id="tablaAlumnosDataTable" class="table table-hover align-middle border-bottom table-striped">
                    <thead class="table-light text-secondary text-uppercase small">
                        <tr>
                            <th width="10%">Foto Actual</th>
                            <th>NIE</th>
                            <th>Estudiante</th>
                            <th>Grado / Sección</th>
                            <th>Turno / Modalidad</th>
                            <th>Estados</th>
                            <th class="text-center" width="10%">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnos as $al)
                            <tr>
<td class="text-center">
    <div class="rounded-circle border bg-white shadow-sm d-inline-block" style="width: 50px; height: 50px; overflow: hidden;">
        @if($al->foto && trim($al->foto) !== 'foto_no_disponible.jpg')
            <img src="{{ asset('fotos_origen/' . trim($al->foto)) }}" 
                 onerror="this.onerror=null; this.src='{{ asset('img_central/' . ($al->codigo_genero == '02' ? 'avatar_femenino.png' : 'avatar_masculino.png')) }}';" 
                 alt="Foto Estudiante" class="h-100 w-100" style="object-fit: cover;">
        @else
            @if($al->codigo_genero == '02')
                <img src="{{ asset('img_central/avatar_femenino.png') }}" alt="Avatar Femenino" class="h-100 w-100" style="object-fit: cover;">
            @else
                <img src="{{ asset('img_central/avatar_masculino.png') }}" alt="Avatar Masculino" class="h-100 w-100" style="object-fit: cover;">
            @endif
        @endif
    </div>
</td>
                                <td class="fw-bold align-middle text-primary">{{ $al->codigo_nie ?? 'N/A' }}</td>
                                <td class="align-middle font-weight-bold text-dark">
                                    {{ trim($al->apellido_paterno) }} {{ trim($al->apellido_materno) }}, {{ trim($al->nombre_completo) }}
                                </td>
                                <td class="align-middle">
                                    <span class="d-block fw-semibold text-dark">{{ trim($al->grado_nombre) }}</span>
                                    <span class="badge badge-secondary px-2 py-1 mt-1">Sección: {{ trim($al->seccion_nombre) }}</span>
                                </td>
                                <td class="align-middle">
                                    <small class="d-block text-secondary mb-1"><i class="far fa-clock"></i> {{ trim($al->turno_nombre ?? 'N/A') }}</small>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;">
                                        {{ $al->bachillerato_nombre ? trim($al->bachillerato_nombre) : 'Básica Regular' }}
                                    </small>
                                </td>
<td class="align-middle">

    @php
        $idFinal = $al->id_estudiante_unico ?? ($al->id_alumno ?? null);
    @endphp

    @if($al->foto && trim($al->foto) !== 'foto_no_disponible.jpg')
        <span class="badge badge-success mb-1 d-inline-block"><i class="fas fa-camera"></i> Con Foto</span>
    @else
        <span class="badge badge-warning text-dark mb-1 d-inline-block"><i class="fas fa-camera"></i> Sin Foto</span>
    @endif

    @if($al->firma_autorizacion)
        <span class="badge badge-success d-inline-block"><i class="fas fa-signature"></i> Firmado</span>
    @else
        <span class="badge badge-danger d-inline-block"><i class="fas fa-signature"></i> Falta Firma</span>
    @endif
</td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('estudiante.informacion.edit', $al->id_alumno) }}" class="btn btn-sm btn-primary btn-block shadow-sm">
                                        <i class="fas fa-edit"></i> Capturar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
    $('#tablaAlumnosDataTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "responsive": true,
// CORRECCIÓN AQUÍ: Al dejarlo vacío, DataTables respeta al 100% el orden que viene desde PostgreSQL
        "order": [],
        "columnDefs": [
            { "orderable": false, "targets": [0, 5, 6] } // Desactiva ordenación en foto, estados y botón capturar
        ]
    });
});
</script>
@endsection