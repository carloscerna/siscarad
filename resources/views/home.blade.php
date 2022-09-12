@extends('layouts.app')
Cargando...
@php
use App\Models\User;
    $cant_usuarios = User::count();                                                
use Spatie\Permission\Models\Role;
    $cant_roles = Role::count();                                                
use App\Models\mantenimiento\asignatura\Asignatura;
    $cant_asignaturas = Asignatura::count();        
use Illuminate\Support\Facades;
    $correo_docente = Auth::user()->email;                                        
    $nombre_docente = Auth::user()->name;
    $codigo_personal = Auth::user()->codigo_personal; 
use App\Models\Tablas\EncargadoGrado;
use App\Models\Tablas\Personal;

    $EncargadoGrado = EncargadoGrado::from('encargado_grado as eg')
        ->join('personal as p',function($join){$join->on('p.id_personal','=','eg.codigo_docente')
        ->where('eg.codigo_docente','=',13);})
            ->select("eg.codigo_docente",TRIM("p.nombres"))
                ->where('eg.codigo_ann_lectivo','=','22')->get();
@endphp

@section('content')
@role("Docente")
<section class="section">
    <div class="section-header mb-1">
        <h4 class="page__heading">{{$nombre_docente}}{{$codigo_personal}} </h4>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="row">
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card bg-primary order-card">
                                <div class="card-block m-1">
                                    <h5>Estudiantes</h5>                                               
                                        <h2 class="text-right"><i class="fa fa-users f-left float-left"></i><span></span></h2>
                                        <p class="m-b-0 text-right"><a href="/roles" class="text-white"> . . . </a></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card bg-primary order-card">
                                <div class="card-block m-1">
                                <h5>Presentes</h5>                                               
                                    <h2 class="text-right"><i class="fa fa-user-check f-left float-left"></i><span></span></h2>
                                    <p class="m-b-0 text-right"><a href="/roles" class="text-white"> . . . </a></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card bg-primary order-card">
                                <div class="card-block m-1">
                                <h5>Retirados</h5>                                               
                                    <h2 class="text-right"><i class="fa fa-user-times f-left float-left"></i><span></span></h2>
                                    <p class="m-b-0 text-right"><a href="/roles" class="text-white"> . . . </a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endrole


@role('Administrador')
<section class="section">
        <div class="section-header">
            <h3 class="page__heading">Tablero</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">                          
                                <div class="row">
                                    <div class="col-md-4 col-xl-4">
                                    
                                    <div class="card bg-primary order-card">
                                            <div class="card-block m-1">
                                            <h5>Usuarios</h5>                                               
                                                <h2 class="text-right"><i class="fa fa-users f-left float-left"></i><span>{{$cant_usuarios}}</span></h2>
                                                <p class="m-b-0 text-right"><a href="/usuarios" class="text-white">Ver más</a></p>
                                            </div>                                            
                                        </div>                                    
                                    </div>
                                    
                                    <div class="col-md-4 col-xl-4">
                                        <div class="card bg-secondary order-card">
                                            <div class="card-block m-1">
                                            <h5>Roles</h5>                                               

                                                <h2 class="text-right"><i class="fa fa-user-lock f-left float-left"></i><span>{{$cant_roles}}</span></h2>
                                                <p class="m-b-0 text-right"><a href="/roles" class="text-white">Ver más</a></p>
                                            </div>
                                        </div>
                                    </div>                                                                
                                    
                                    <div class="col-md-4 col-xl-4">
                                        <div class="card bg-info order-card">
                                            <div class="card-block m-1">
                                                <h5>Asignaturas</h5>                                               

                                                <h2 class="text-right"><i class="fa fa-book f-left float-left"></i><span>{{$cant_asignaturas}}</span></h2>
                                                <p class="m-b-0 text-right"><a href="/asignaturas" class="text-white">Ver más</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endrole
@endsection