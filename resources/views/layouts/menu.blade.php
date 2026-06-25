@role('Administrador')
<li class="side-menus">
    <a class="nav-link {{ Request::is('home') ? 'active' : '' }}" href="{{ url('/home') }}">
        <i class="fas fa-building"></i><span> Tablero</span>
    </a>
    <a class="nav-link {{ Request::is('usuarios*') ? 'active' : '' }}" href="{{ url('usuarios') }}">
        <i class="fas fa-users"></i><span> Usuarios</span>
    </a>
    <a class="nav-link {{ Request::is('roles*') ? 'active' : '' }}" href="{{ url('roles') }}">
        <i class="fas fa-user-lock"></i><span> Roles</span>
    </a>
    <a class="nav-link {{ Request::is('bitacora') ? 'active' : '' }}" href="{{ url('bitacora') }}">
        <i class="fas fa-book"></i><span> Bitácora General</span>
    </a>

    <!-- Mantenimiento siempre visible -->
    <p class="mt-3 mb-1 fw-bold"><i class="fas fa-tools"></i> Mantenimiento</p>
    <a class="nav-link {{ Request::is('asignaturas*') ? 'active' : '' }}" href="{{ url('asignaturas') }}">
        <i class="fas fa-arrow-right"></i><span> Asignatura</span>
    </a>
    <a class="nav-link" href="#"><i class="fas fa-arrow-right"></i><span> Modalidad</span></a>
    <a class="nav-link" href="#"><i class="fas fa-arrow-right"></i><span> Año Lectivo</span></a>
</li>
@endrole

@role('Docente')
<li class="side-menus">
    <a class="nav-link {{ Request::is('home') ? 'active' : '' }}" href="{{ url('/home') }}">
        <i class="fas fa-building"></i><span> Tablero</span>
    </a>

    <!-- Estudiante siempre visible -->
    <p class="mt-3 mb-1 fw-bold"><i class="fas fa-users"></i> Estudiante</p>
    <a class="nav-link {{ Request::is('calificacionporasignatura*') ? 'active' : '' }}" href="{{ url('calificacionporasignatura') }}">
        <i class="fas fa-arrow-right"></i><span> Calificaciones</span>
    </a>
    <a class="nav-link {{ Request::is('asistenciaDiaria*') ? 'active' : '' }}" href="{{ url('asistenciaDiaria') }}">
        <i class="fas fa-arrow-right"></i><span> Asistencia Diaria</span>
    </a>
    <a class="nav-link {{ Request::is('estudiante/informacion*') ? 'active' : '' }}" href="{{ url('estudiante/informacion') }}">
        <i class="fas fa-arrow-right"></i><span> Información</span>
    </a>
<li class="side-menus {{ Request::is('bitacora/docente*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('bitacora.index_docente') }}">
        <i class="fas fa-book"></i> <span>Bitácora de Alumnos</span>
    </a>
</li>

<li class="side-menus {{ Request::is('consolidado-conducta*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('consolidado.index') }}">
        <i class="fas fa-chart-bar text-warning"></i> <span>Consolidado Deméritos</span>
    </a>
</li>
</li>
@endrole
