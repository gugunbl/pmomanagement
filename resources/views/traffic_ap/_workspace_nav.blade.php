@php
    $trafficApQuery = array_filter([
        'id_project' => $selectedProjectId ?? null,
        'id_lokasi' => $selectedLokasiId ?? null,
    ], function ($value) {
        return $value !== null && $value !== '';
    });
@endphp

<nav class="traffic-workflow" aria-label="Navigasi pengelolaan traffic access point">
    <div class="traffic-workflow__intro">
        <span class="traffic-workflow__eyebrow">AP Traffic Workspace</span>
        <strong>Kelola data dalam satu alur</strong>
        <small>Mapping perangkat, input harian, lalu pantau trennya.</small>
    </div>
    <div class="traffic-workflow__steps">
        <a href="{{ route('traffic-ap.mapping.index', $trafficApQuery) }}"
           class="traffic-workflow__step {{ ($activeTrafficPage ?? '') === 'mapping' ? 'is-active' : '' }}"
           {{ ($activeTrafficPage ?? '') === 'mapping' ? 'aria-current=page' : '' }}>
            <span class="traffic-workflow__number">1</span>
            <span><small>Persiapan</small><strong>Mapping AP</strong></span>
        </a>
        <a href="{{ route('traffic-ap.input', $trafficApQuery) }}"
           class="traffic-workflow__step {{ ($activeTrafficPage ?? '') === 'input' ? 'is-active' : '' }}"
           {{ ($activeTrafficPage ?? '') === 'input' ? 'aria-current=page' : '' }}>
            <span class="traffic-workflow__number">2</span>
            <span><small>Operasional</small><strong>Input Harian</strong></span>
        </a>
        <a href="{{ route('traffic-ap.monitor', $trafficApQuery) }}"
           class="traffic-workflow__step {{ ($activeTrafficPage ?? '') === 'monitor' ? 'is-active' : '' }}"
           {{ ($activeTrafficPage ?? '') === 'monitor' ? 'aria-current=page' : '' }}>
            <span class="traffic-workflow__number">3</span>
            <span><small>Analisis</small><strong>Monitoring</strong></span>
        </a>
    </div>
</nav>
