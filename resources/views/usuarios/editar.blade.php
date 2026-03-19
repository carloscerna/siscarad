@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading text-warning">
                <i class="fas fa-user-edit mr-2"></i>Editar Usuario
            </h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        
                        <div class="card-header bg-warning text-dark d-flex align-items-center">
                            <h4 class="mb-0" style="font-size: 1.1rem;">Modificando datos de: <strong>{{ $user->name }}</strong></h4>
                        </div>

                        <div class="card-body">
                     
                        @if ($errors->any())                                                
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                <strong><i class="fas fa-exclamation-circle"></i> ¡Revise los campos!</strong>                        
                                <div class="mt-2">
                                    @foreach ($errors->all() as $error)                                    
                                        <span class="badge badge-light text-danger mr-1 mb-1">{{ $error }}</span>
                                    @endforeach
                                </div>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        {!! Form::model($user, ['method' => 'PATCH','route' => ['usuarios.update', $user->id]]) !!}
                        
                        <div class="row">
                            {{-- Fila 1: Datos --}}
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">Nombre Completo</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-user text-warning"></i></span>
                                        </div>
                                        {!! Form::text('name', null, array('class' => 'form-control')) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="email" class="font-weight-bold">Correo Electrónico</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-warning"></i></span>
                                        </div>
                                        {!! Form::text('email', null, array('class' => 'form-control')) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Fila 2: Seguridad --}}
                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="password" class="font-weight-bold">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-lock text-warning"></i></span>
                                        </div>
                                        {!! Form::password('password', array('class' => 'form-control', 'placeholder' => 'Dejar en blanco para no cambiar')) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="confirm-password" class="font-weight-bold">Confirmar Nueva Contraseña</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-check-double text-warning"></i></span>
                                        </div>
                                        {!! Form::password('confirm-password', array('class' => 'form-control', 'placeholder' => 'Repita la nueva contraseña')) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Fila 3: Asignaciones --}}
                        <div class="row bg-light p-3 rounded shadow-sm mx-1 border-left border-warning" style="border-left-width: 5px !important;">
                            <div class="col-12 col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold"><i class="fas fa-user-tag mr-1 text-warning"></i> Rol de Usuario</label>
                                    {!! Form::select('roles[]', $roles, $userRole, array('class' => 'form-control')) !!}
                                </div>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold"><i class="fas fa-chalkboard-teacher mr-1 text-warning"></i> Personal (Docente)</label>
                                    {!! Form::select('codigo_personal', $personal, null, ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold"><i class="fas fa-school mr-1 text-warning"></i> Institución</label>
                                    {!! Form::select('codigo_institucion', $institucion, null, ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                        
                        </div>

                        <div class="card-footer bg-white border-top text-right py-3">
                            <button type="submit" class="btn btn-warning shadow-sm px-4 font-weight-bold">
                                <i class="fas fa-sync-alt mr-1"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('usuarios.index') }}" class="btn btn-light border px-4 ml-2">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                        
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection