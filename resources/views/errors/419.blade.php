@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container mt-5">
        <div class="page-error">
            <div class="page-inner text-center">
                <h1 class="display-1 text-warning">419</h1>
                <div class="page-description">
                    <h2 class="h4">La sesión de seguridad ha expirado.</h2>
                    <p class="text-muted">Por seguridad, el sistema cierra la conexión tras un periodo de inactividad.</p>
                </div>
                <div class="mt-4">
                    {{-- Cambiamos el reload por una redirección limpia --}}
                    <a href="{{ url('CalificacionPorAsignatura') }}" class="btn btn-warning btn-lg shadow-sm text-dark font-weight-bold">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Ingresar Notas
                    </a>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Si el problema persiste, por favor cierra sesión y vuelve a entrar.</small>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection