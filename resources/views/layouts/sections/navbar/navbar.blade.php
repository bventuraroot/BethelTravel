@php
    $containerNav = $containerNav ?? 'container-fluid';
    $navbarDetached = $navbarDetached ?? '';
    $authUser = Auth::user();
    $displayName = $authUser->name ?? 'Invitado';
    $displayRole = 'Usuario';
    if ($authUser && method_exists($authUser, 'getRoleNames')) {
        $displayRole = $authUser->getRoleNames()->first() ?? 'Usuario';
    }
    $isAdminUser = $authUser && method_exists($authUser, 'hasRole') ? $authUser->hasRole('Admin') : false;
@endphp

<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
    <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
        id="layout-navbar">
@endif
@if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
@endif

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
    <div class="py-0 navbar-brand app-brand demo d-none d-xl-flex me-4">
        <a href="{{ url('/') }}" class="gap-2 app-brand-link">
            <span class="app-brand-logo demo">
                @include('_partials.macros', ['height' => 20])
            </span>
            <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
        </a>
    </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
    <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="px-0 nav-item nav-link me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

    <!-- Style Switcher -->
    <div class="navbar-nav align-items-center">
        <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
            <i class='ti ti-sm'></i>
        </a>
    </div>
    <!--/ Style Switcher -->



    <ul class="flex-row navbar-nav align-items-center ms-auto">

        @auth
        <!-- Fecha + calendario (a la izquierda de notificaciones) -->
        <li class="nav-item navbar-dropdown dropdown navbar-calendar-dropdown me-2 me-xl-3 position-relative z-3">
            <button type="button" id="brandDateToggle"
                class="nav-link hide-arrow btn btn-sm btn-text-secondary rounded-pill d-flex align-items-center gap-1 px-2 py-2 navbar-calendar-btn"
                data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-offset="0,6" aria-expanded="false"
                title="Calendario">
                <i class="ti ti-calendar ti-md text-primary flex-shrink-0"></i>
                <span id="navbarUserDate" class="small text-heading text-nowrap fw-medium d-none d-sm-inline">—</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end navbar-calendar-dropdown-menu"
                aria-labelledby="brandDateToggle">
                <div class="navbar-calendar-panel">
                    <div class="navbar-calendar-panel-body">
                        <input type="text" id="brandCalendarMount" class="d-none" readonly tabindex="-1"
                            autocomplete="off" aria-hidden="true" />
                    </div>
                </div>
            </div>
        </li>
        <!-- Actividad reciente (navbar-dropdown = estilos del tema + dropdown funcional) -->
        <li class="nav-item navbar-dropdown dropdown dropdown-notifications position-relative z-3 me-2 me-xl-3">
            <a class="nav-link hide-arrow btn btn-icon btn-text-secondary rounded-pill position-relative p-2"
                href="javascript:void(0);"
                data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-offset="0,6" id="activityNotifToggle"
                data-activity-url="{{ route('activity.recent') }}"
                data-unread-url="{{ route('activity.unread-count') }}"
                data-mark-seen-url="{{ route('activity.mark-seen') }}"
                data-activity-scope="{{ $isAdminUser ? 'global' : 'personal' }}"
                aria-expanded="false" title="Mis últimos movimientos">
                <i class="ti ti-bell ti-md"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white shadow-sm activity-notif-badge d-none"
                    id="activityNotifBadge">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0 activity-dropdown-panel"
                aria-labelledby="activityNotifToggle">
                <li class="dropdown-menu-header activity-notif-header border-bottom-0 py-3 px-3 rounded-top">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <h6 class="activity-notif-header-title mb-0">
                            {{ $isAdminUser ? 'Actividad del sistema' : 'Mis últimos movimientos' }}
                        </h6>
                        <span class="badge rounded-pill activity-notif-header-badge d-none" id="activityNotifHeaderCount"></span>
                    </div>
                    <p class="activity-notif-header-desc mb-0 mt-1">
                        {{ $isAdminUser ? 'Movimientos recientes de todos los usuarios' : 'Acciones recientes en el sistema' }}
                    </p>
                </li>
                <li class="p-0 border-top-0">
                    <div class="scrollable-container activity-notif-scroll-wrapper">
                        <ul class="list-unstyled mb-0 dropdown-notifications-list py-0" id="activityNotifList">
                            <li class="px-3 py-4 text-center text-muted small" id="activityNotifPlaceholder">Cargando…</li>
                        </ul>
                    </div>
                </li>
            </ul>
        </li>
        @endauth

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown position-relative z-2">
            <a class="nav-link dropdown-toggle hide-arrow navbar-user-trigger py-2" href="javascript:void(0);"
                data-bs-toggle="dropdown" data-bs-offset="0,6" aria-expanded="false">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="avatar avatar-online flex-shrink-0">
                        <img src="{{ $authUser ? asset('assets/img/avatars/'.$authUser->image) : asset('assets/img/avatars/1.png') }}"
                            alt="{{ $displayName }}" class="w-px-40 h-px-35 rounded-circle">
                    </div>
                    <div class="d-none d-md-flex flex-column align-items-start text-start min-w-0">
                        <span class="fw-semibold text-heading lh-sm text-truncate w-100" style="max-width: 12rem"
                            title="{{ $displayName }}">{{ $displayName }}</span>
                        <span
                            class="badge bg-label-primary rounded-pill px-2 py-1 mt-1 text-uppercase fw-semibold"
                            style="font-size: 0.65rem; letter-spacing: 0.04em;">{{ $displayRole }}</span>
                    </div>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 pt-0 navbar-user-dropdown-menu">
                <li class="px-0 py-0">
                    <div class="navbar-user-dropdown-header px-3 py-3 border-bottom">
                        <div class="d-flex align-items-start gap-2 gap-sm-3">
                            <div class="avatar avatar-online flex-shrink-0">
                                <img src="{{ $authUser ? asset('assets/img/avatars/'.$authUser->image) : asset('assets/img/avatars/1.png') }}"
                                    alt="{{ $displayName }}" class="w-px-40 h-px-40 rounded-circle">
                            </div>
                            <div class="flex-grow-1 min-w-0" style="min-width: 0;">
                                <span class="fw-semibold d-block text-heading text-break">{{ $displayName }}</span>
                                <span
                                    class="badge bg-label-secondary rounded-pill mt-1 text-uppercase fw-semibold"
                                    style="font-size: 0.65rem;">{{ $displayRole }}</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item py-2"
                        href="{{ Route::has('profile.edit') ? route('profile.edit') : 'javascript:void(0);' }}">
                        <i class="ti ti-settings me-2 ti-sm text-muted"></i>
                        <span class="align-middle">Mi perfil</span>
                    </a>
                </li>
                {{-- @if (Auth::check() && Laravel\Jetstream\Jetstream::hasApiFeatures())
              <li>
                <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
                  <i class='ti ti-key me-2 ti-sm'></i>
                  <span class="align-middle">API Tokens</span>
                </a>
              </li>
              @endif --}}
                {{-- @if (Auth::User() && Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <h6 class="dropdown-header">Manage Team</h6>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ Auth::user() ? route('teams.show', Auth::user()->currentTeam->id) : 'javascript:void(0)' }}">
                            <i class='ti ti-settings me-2'></i>
                            <span class="align-middle">Team Settings</span>
                        </a>
                    </li>
                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <li>
                            <a class="dropdown-item" href="{{ route('teams.create') }}">
                                <i class='ti ti-user me-2'></i>
                                <span class="align-middle">Create New Team</span>
                            </a>
                        </li>
                    @endcan
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <lI>
                        <h6 class="dropdown-header">Switch Teams</h6>
                    </lI>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    @if (Auth::user())
                        @foreach (Auth::user()->allTeams() as $team)
                            {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream.

                            {{-- <x-jet-switchable-team :team="$team" />
                        @endforeach
                    @endif
                @endif --}}
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                @if (Auth::check())
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class='ti ti-logout me-2'></i>
                            <span class="align-middle">Salir</span>
                        </a>
                    </li>
                    <form method="POST" id="logout-form" action="{{ route('logout') }}">
                        @csrf
                    </form>
                @else
                    <li>
                        <a class="dropdown-item"
                            href="{{ Route::has('login') ? route('login') : 'javascript:void(0)' }}">
                            <i class='ti ti-login me-2'></i>
                            <span class="align-middle">Login</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>

@if (!isset($navbarDetached))
    </div>
@endif
</nav>
<!-- / Navbar -->

@auth
@push('scripts-app')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/navbar-brand-calendar.js') }}"></script>
<script src="{{ asset('assets/js/user-activity-notifications.js') }}"></script>
<script src="{{ asset('assets/js/navbar-user-clock.js') }}"></script>
@endpush
@endauth
