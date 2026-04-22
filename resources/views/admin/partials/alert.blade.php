@php
    $adminAlerts = [
        ['key' => 'success', 'class' => 'success', 'icon' => 'fa-check-circle'],
        ['key' => 'error', 'class' => 'danger', 'icon' => 'fa-exclamation-circle'],
        ['key' => 'warning', 'class' => 'warning', 'icon' => 'fa-exclamation-triangle'],
        ['key' => 'info', 'class' => 'info', 'icon' => 'fa-info-circle'],
    ];
@endphp

@foreach($adminAlerts as $alert)
    @if(session($alert['key']))
        <div class="alert alert-{{ $alert['class'] }} alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="fa {{ $alert['icon'] }}"></i>
            <span>{{ session($alert['key']) }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach