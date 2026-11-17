<nav class="device-workspace-nav" aria-label="Navigasi perangkat">
    <div class="device-workspace-brand">
        <span class="device-workspace-brand__icon"><i class="fas fa-server"></i></span>
        <span>
            <small>Asset workspace</small>
            <strong>Perangkat</strong>
        </span>
    </div>
    <div class="device-workspace-links">
        @can('view-perangkat')
            <a href="{{ route('perangkat.index') }}" class="{{ ($activeDevicePage ?? '') === 'inventory' ? 'is-active' : '' }}">
                <i class="fas fa-list"></i><span>Inventaris</span>
            </a>
        @endcan
        @can('view-summary-perangkat')
            <a href="{{ route('perangkat.summary') }}" class="{{ ($activeDevicePage ?? '') === 'summary' ? 'is-active' : '' }}">
                <i class="fas fa-chart-pie"></i><span>Summary</span>
            </a>
        @endcan
        @can('create-perangkat')
            <a href="{{ route('perangkat.create') }}" class="{{ ($activeDevicePage ?? '') === 'create' ? 'is-active' : '' }}">
                <i class="fas fa-plus"></i><span>Tambah perangkat</span>
            </a>
        @endcan
    </div>
</nav>
