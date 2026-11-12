<div class="mobile-navigation d-xl-none">
    @if ($activeMenu)
        @php
            $sheet = match ($activeMenu) {
                'project' => ['label' => 'Workspace', 'title' => 'Menu Project', 'description' => 'Kelola project dan seluruh monitoring terkait.', 'icon' => 'fa-layer-group'],
                'perangkat' => ['label' => 'Asset', 'title' => 'Menu Perangkat', 'description' => 'Akses data perangkat, ringkasan, dan warehouse.', 'icon' => 'fa-server'],
                'case' => ['label' => 'Support', 'title' => 'Menu Case', 'description' => 'Kelola case dan aktivitas monitoring harian.', 'icon' => 'fa-ticket-alt'],
                default => ['label' => 'Aplikasi', 'title' => 'Menu Lainnya', 'description' => 'Pengaturan akun dan fitur pendukung.', 'icon' => 'fa-th-large'],
            };
        @endphp

        <button type="button" class="mobile-sheet-backdrop" wire:click="closeMenu" aria-label="Tutup menu"></button>
        <section class="mobile-menu-sheet" aria-label="{{ $sheet['title'] }}">
            <div class="mobile-sheet-handle"></div>
            <div class="mobile-sheet-header">
                <div class="mobile-sheet-title-group">
                    <span class="mobile-sheet-title-icon"><i class="fas {{ $sheet['icon'] }}"></i></span>
                    <div>
                        <span class="app-page-eyebrow text-primary">{{ $sheet['label'] }}</span>
                        <h2>{{ $sheet['title'] }}</h2>
                        <p>{{ $sheet['description'] }}</p>
                    </div>
                </div>
                <button type="button" class="mobile-sheet-close" wire:click="closeMenu" aria-label="Tutup"><i class="fas fa-times"></i></button>
            </div>

            <div class="mobile-submenu-list">
                @if ($activeMenu === 'project')
                    @can('view-project')
                        <a href="{{ route('project.index') }}" class="{{ request()->routeIs('project.index') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-folder-open"></i></span>
                            <span class="mobile-submenu-copy"><strong>Semua Project</strong><small>Lihat dan cari seluruh data project</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('maintenance-project')
                        <a href="{{ route('project.maintenance') }}" class="{{ request()->routeIs('project.maintenance') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-tools"></i></span>
                            <span class="mobile-submenu-copy"><strong>Data Maintenance</strong><small>Project dengan layanan maintenance</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('create-project')
                        <a href="{{ route('project.create') }}" class="{{ request()->routeIs('project.create') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-plus"></i></span>
                            <span class="mobile-submenu-copy"><strong>Tambah Project Baru</strong><small>Buat dan lengkapi project baru</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('view-project')
                        <a href="{{ route('project.report.monitoring') }}" class="{{ request()->routeIs('project.report.monitoring') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="mobile-submenu-copy"><strong>Monitoring Laporan</strong><small>Status laporan dan dokumen signoff</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                        <a href="{{ route('project.ba.monitoring') }}" class="{{ request()->routeIs('project.ba.monitoring') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-file-signature"></i></span>
                            <span class="mobile-submenu-copy"><strong>Monitoring BA</strong><small>TOP, signer, dan progres berita acara</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                @elseif ($activeMenu === 'perangkat')
                    @can('view-perangkat')
                        <a href="{{ route('perangkat.index') }}" class="{{ request()->routeIs('perangkat.index') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-server"></i></span>
                            <span class="mobile-submenu-copy"><strong>Semua Perangkat</strong><small>Cari dan kelola seluruh perangkat</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('view-summary-perangkat')
                        <a href="{{ route('perangkat.summary') }}" class="{{ request()->routeIs('perangkat.summary') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-chart-pie"></i></span>
                            <span class="mobile-submenu-copy"><strong>Summary Perangkat</strong><small>Ringkasan kondisi dan distribusi asset</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('view-warehouse')
                        <a href="{{ route('warehouse.index') }}" class="{{ request()->routeIs('warehouse.index') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-warehouse"></i></span>
                            <span class="mobile-submenu-copy"><strong>Dokumen Warehouse</strong><small>Dokumen perangkat masuk dan keluar</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('perangkat-warehouse')
                        <a href="{{ route('warehouse.perangkat') }}" class="{{ request()->routeIs('warehouse.perangkat') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-boxes"></i></span>
                            <span class="mobile-submenu-copy"><strong>Monitoring Warehouse</strong><small>Pantau posisi perangkat di warehouse</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                @elseif ($activeMenu === 'case')
                    @can('view-case')
                        <a href="{{ route('cases.index') }}" class="{{ request()->routeIs('cases.index') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-list-alt"></i></span>
                            <span class="mobile-submenu-copy"><strong>Daftar Case</strong><small>Lihat dan tindak lanjuti seluruh case</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('create-case')
                        <a href="{{ route('cases.create') }}" class="{{ request()->routeIs('cases.create') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-plus"></i></span>
                            <span class="mobile-submenu-copy"><strong>Tambah Case</strong><small>Catat case atau gangguan baru</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('sub-menu-daily-monitoring')
                        <a href="{{ route('daily-monitoring.index') }}" class="{{ request()->is('daily-monitoring*') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-calendar-check"></i></span>
                            <span class="mobile-submenu-copy"><strong>Daily Monitoring</strong><small>Aktivitas dan pembaruan monitoring harian</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('menu-interferance-view')
                        <a href="{{ route('interference-ratio.index') }}" class="{{ request()->is('interference-ratio*') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-wave-square"></i></span>
                            <span class="mobile-submenu-copy"><strong>Interference Ratio</strong><small>Monitoring rasio gangguan koneksi</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                @else
                    @can('menu_monitoring_ap')
                        <a href="{{ route('traffic-ap.monitor') }}" class="{{ request()->is('traffic-ap*') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-wifi"></i></span>
                            <span class="mobile-submenu-copy"><strong>Monitoring AP</strong><small>Traffic dan kondisi access point</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('view-user')
                        <a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-users"></i></span>
                            <span class="mobile-submenu-copy"><strong>Pengguna</strong><small>Kelola user dan akses aplikasi</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    @can('update-settings')
                        <a href="{{ route('settings.index') }}" class="{{ request()->is('settings*') ? 'is-active' : '' }}">
                            <span class="mobile-submenu-icon"><i class="fas fa-cog"></i></span>
                            <span class="mobile-submenu-copy"><strong>Pengaturan</strong><small>Konfigurasi aplikasi dan perusahaan</small></span>
                            <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                        </a>
                    @endcan
                    <a href="{{ route('profile.edit', auth()->user()) }}" class="{{ request()->is('profile*') ? 'is-active' : '' }}">
                        <span class="mobile-submenu-icon"><i class="fas fa-user-circle"></i></span>
                        <span class="mobile-submenu-copy"><strong>Profil Saya</strong><small>Data akun dan informasi pribadi</small></span>
                        <i class="fas fa-chevron-right mobile-submenu-arrow"></i>
                    </a>
                @endif
            </div>

            @if ($activeMenu === 'more')
                <form action="{{ route('logout') }}" method="POST" class="mobile-logout-form">
                    @csrf
                    <button type="submit"><i class="fas fa-sign-out-alt"></i> Keluar dari aplikasi</button>
                </form>
            @endif
        </section>
    @endif

    <nav class="mobile-bottom-nav" aria-label="Navigasi utama">
        @can('menu-dashboard')
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home"></i><span>Beranda</span></a>
        @elsecan('menu-dashboard-user')
            <a href="{{ route('home_user') }}" class="{{ request()->routeIs('home_user') ? 'active' : '' }}"><i class="fas fa-home"></i><span>Beranda</span></a>
        @endcan

        @can('view-project')
            <button type="button" wire:click="toggleMenu('project')" class="{{ request()->is('project*') || $activeMenu === 'project' ? 'active' : '' }}" aria-expanded="{{ $activeMenu === 'project' ? 'true' : 'false' }}"><i class="fas fa-layer-group"></i><span>Project</span></button>
        @endcan
        @canany(['view-perangkat', 'view-summary-perangkat', 'view-warehouse', 'perangkat-warehouse'])
            <button type="button" wire:click="toggleMenu('perangkat')" class="{{ request()->is('perangkat*') || request()->is('warehouse*') || $activeMenu === 'perangkat' ? 'active' : '' }}" aria-expanded="{{ $activeMenu === 'perangkat' ? 'true' : 'false' }}"><i class="fas fa-server"></i><span>Perangkat</span></button>
        @endcanany
        @canany(['view-case', 'create-case', 'sub-menu-daily-monitoring'])
            <button type="button" wire:click="toggleMenu('case')" class="{{ request()->is('cases*') || request()->is('daily-monitoring*') || $activeMenu === 'case' ? 'active' : '' }}" aria-expanded="{{ $activeMenu === 'case' ? 'true' : 'false' }}"><i class="fas fa-ticket-alt"></i><span>Case</span></button>
        @endcanany
        <button type="button" wire:click="toggleMenu('more')" class="{{ $activeMenu === 'more' ? 'active' : '' }}" aria-expanded="{{ $activeMenu === 'more' ? 'true' : 'false' }}"><i class="fas fa-th-large"></i><span>Lainnya</span></button>
    </nav>
</div>
