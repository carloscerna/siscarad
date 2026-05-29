@role('Administrador')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ url('/home') }}">
        <i class="fas fa-building"></i><span>Tablero</span>
    </a>
    <a class="nav-link" href="usuarios">
        <i class="fas fa-users"></i><span>Usuarios</span>
    </a>
    <a class="nav-link" href="roles">
        <i class="fas fa-user-lock"></i><span>Roles</span>
    </a>

    <a href="#adminMantenimientoSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        <i class="fas fa-tools"></i><span>Mantenimiento</span>
    </a>
    <ul class="collapse list-unstyled" id="adminMantenimientoSubmenu">
        <li>
            <a href="asignaturas"><i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span>Asignatura</span>
            </a>
        </li>
        <li>
            <a href="#"><i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span>Modalidad</span>
            </a>
        </li>
        <li>
            <a href="#"><i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span>Año Lectivo</span>
            </a>
        </li>
    </ul>
</li>
@endrole

@role('Docente')
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ url('/home') }}">
        <i class="fas fa-building"></i><span>Tablero</span>
    </a>
    
    <a href="#estudianteSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        <i class="fas fa-users"></i><span>Estudiante</span>
    </a>
    <ul class="collapse list-unstyled" id="estudianteSubmenu">
        <li>
            <a href="calificacionporasignatura"><i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span>Calificaciones</span>
            </a>
        </li>
        <li>
            <a href="asistenciaDiaria"><i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span>Asistencia Diaria</span>
            </a>
        </li>
        
<li>
    <a href="{{ route('estudiante.informacion.index') }}"><i class="fa fa-arrow-right" aria-hidden="true"></i>
        <span>Información</span>
    </a>
</li>

        {{-- <li>
              <a href="matricula"><i class="fa fa-arrow-right" aria-hidden="true"></i>
                <span>Matricula</span>
            </a>   
        </li> --}}
    </ul>
</li>
@endrole