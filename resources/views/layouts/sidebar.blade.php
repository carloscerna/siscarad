<aside id="sidebar-wrapper">
    <div class="sidebar-brand bg-white">
        <img class="navbar-brand-full app-header-logo" src="{{ asset('img/logo_cerz.png') }}" width="65"
             alt="Infyom Logo">
        <a href="{{ url('/') }}"></a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="{{ url('/') }}" class="small-sidebar-text">
            <img class="navbar-brand-full" src="{{ asset('img/logo_cerz.png') }}" width="45px" alt=""/>
        </a>
    </div>
    <ul class="sidebar-menu mt-4 bg-dark text-white">
        @include('layouts.menu')
    </ul>
</aside>
