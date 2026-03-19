@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container mt-5">
        <div class="page-error">
            <div class="page-inner text-center">
                <h1 class="display-1 text-warning">419</h1>
                <div class="page-description">
                    <h2 class="h4">La sesión de seguridad ha expirado.</h2>
                    <p class="text-muted">Esto sucede por pasar mucho tiempo en la misma pantalla sin navegar.</p>
                </div>
                <div class="mt-4">
                    <button onclick="window.location.reload();" class="btn btn-warning btn-lg shadow-sm text-dark font-weight-bold">
                        <i class="fas fa-sync-alt mr-2"></i> Recargar y Reintentar
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection