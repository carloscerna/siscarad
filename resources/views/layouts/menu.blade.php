<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    <a class="nav-link" href="/home">
        <i class="fas fa-building"></i><span>Tablero</span>
        @role('Administrador')
            $cant_asignaturas = "Si Administrador";
            @else
            $cant_asignaturas = "NO Administrador";
        @endrole
        <span>{{$cant_asignaturas}}</span>
    </a>
    <a class="nav-link" href="/usuarios">
        <i class="fas fa-users"></i><span>Usuarios</span>
    </a>
    <a class="nav-link" href="/roles">
        <i class="fas fa-user-lock"></i><span>Roles</span>
    </a>

    <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
        <i class="fas fa-tools"></i><span>Mantenimiento</span>
    </a>
    <ul class="collapse list-unstyled" id="pageSubmenu">
        <li>
            <a href="/asignaturas"><i class="fa fa-arrow-right" aria-hidden="true"></i>
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
