@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container mt-5">
        <div class="page-error">
            <div class="page-inner text-center">
                <h1 class="display-1 text-primary">403</h1>
                <div class="page-description">
                    <h2 class="h4">¡Ups! Tu sesión ha expirado o no tienes acceso aquí.</h2>
                    <p class="text-muted">Por seguridad, el sistema te ha desconectado tras un tiempo de inactividad.</p>
                </div>
                <div class="page-search mt-4">
                    <div class="mt-3">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-sign-in-alt mr-2"></i> Volver a Ingresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection