@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

$isAdmin = Auth::check() && Auth::user()->role == 0;
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    <div class="app-brand demo">
        <a href="{{url('/')}}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">{{config('variables.templateName')}}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)

        {{-- Sembunyikan menu admin jika bukan role admin (role == 0) --}}
        @if (!empty($menu->adminOnly) && !$isAdmin)
            @continue
        @endif

        {{-- adding active and open class if child is active --}}

        {{-- menu headers --}}
        @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
        @else

        {{-- active menu method --}}
        @php
        $activeClass = null;
        $currentRouteName = Route::currentRouteName() ?? '';

        // Cek kecocokan berdasarkan Route Name, Pola Route (dokumen.*), atau URL Path
        $isCurrentRoute = false;
        if (!empty($menu->slug)) {
            if (is_array($menu->slug)) {
                foreach ($menu->slug as $slug) {
                    if ($currentRouteName === $slug || str_starts_with($currentRouteName, $slug . '.') || str_starts_with($currentRouteName, $slug . '-')) {
                        $isCurrentRoute = true;
                        break;
                    }
                }
            } else {
                $isCurrentRoute = ($currentRouteName === $menu->slug) 
                    || str_starts_with($currentRouteName, $menu->slug . '.')
                    || str_starts_with($currentRouteName, $menu->slug . '-');
            }
        }

        // Fallback cek kecocokan berdasarkan URL path (misal: /dokumen, /users, /skpd)
        if (!$isCurrentRoute && !empty($menu->url)) {
            $menuPath = trim($menu->url, '/');
            if ($menuPath === '') {
                $isCurrentRoute = request()->is('/');
            } else {
                $isCurrentRoute = request()->is($menuPath) || request()->is($menuPath . '/*');
            }
        }

        if ($isCurrentRoute) {
            $activeClass = 'active';
        }
        elseif (isset($menu->submenu)) {
            if (is_array($menu->slug)) {
                foreach($menu->slug as $slug){
                    if (str_contains($currentRouteName,$slug) && strpos($currentRouteName,$slug) === 0) {
                        $activeClass = 'active open';
                    }
                }
            }
            else{
                if (str_contains($currentRouteName,$menu->slug) && strpos($currentRouteName,$menu->slug) === 0) {
                    $activeClass = 'active open';
                }
            }
        }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{$activeClass}}">
            <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
                @isset($menu->icon)
                <i class="{{ $menu->icon }}"></i>
                @endisset
                <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                @isset($menu->badge)
                <div class="badge rounded-pill bg-{{ $menu->badge[0] }} text-uppercase ms-auto">{{ $menu->badge[1] }}</div>
                @endisset
            </a>

            {{-- submenu --}}
            @isset($menu->submenu)
            @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
            @endisset
        </li>
        @endif
        @endforeach
    </ul>

</aside>
