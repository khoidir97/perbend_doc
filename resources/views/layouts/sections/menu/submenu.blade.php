@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

$isAdmin = Auth::check() && Auth::user()->role == 0;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)

    @if (!empty($submenu->adminOnly) && !$isAdmin)
      @continue
    @endif

    {{-- active menu method --}}
    @php
      $activeClass = null;
      $active = 'active open';
      $currentRouteName = Route::currentRouteName() ?? '';

      $isCurrentRoute = false;
      if (!empty($submenu->slug)) {
          if (is_array($submenu->slug)) {
              foreach ($submenu->slug as $slug) {
                  if ($currentRouteName === $slug || str_starts_with($currentRouteName, $slug . '.') || str_starts_with($currentRouteName, $slug . '-')) {
                      $isCurrentRoute = true;
                      break;
                  }
              }
          } else {
              $isCurrentRoute = ($currentRouteName === $submenu->slug) 
                  || str_starts_with($currentRouteName, $submenu->slug . '.')
                  || str_starts_with($currentRouteName, $submenu->slug . '-');
          }
      }

      if (!$isCurrentRoute && !empty($submenu->url)) {
          $subPath = trim($submenu->url, '/');
          if ($subPath === '') {
              $isCurrentRoute = request()->is('/');
          } else {
              $isCurrentRoute = request()->is($subPath) || request()->is($subPath . '/*');
          }
      }

      if ($isCurrentRoute) {
          $activeClass = 'active';
      }
      elseif (isset($submenu->submenu)) {
        if (is_array($submenu->slug)) {
          foreach($submenu->slug as $slug){
            if (str_contains($currentRouteName,$slug) && strpos($currentRouteName,$slug) === 0) {
                $activeClass = $active;
            }
          }
        }
        else{
          if (str_contains($currentRouteName,$submenu->slug) && strpos($currentRouteName,$submenu->slug) === 0) {
            $activeClass = $active;
          }
        }
      }
    @endphp

      <li class="menu-item {{$activeClass}}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
          <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
          @isset($submenu->badge)
            <div class="badge rounded-pill bg-{{ $submenu->badge[0] }} text-uppercase ms-auto">{{ $submenu->badge[1] }}</div>
          @endisset
        </a>

        {{-- submenu --}}
        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu',['menu' => $submenu->submenu])
        @endif
      </li>
    @endforeach
  @endif
</ul>
