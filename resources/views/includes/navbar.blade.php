<nav class="sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
        <!-- Brand -->
        <div class="sidenav-header d-flex align-items-center">
            <a class="navbar-brand" href="{{ route('home') }}" data-toggle="tooltip" data-original-title="{{ setting('company_name') }}">
                @php $logo = setting('company_logo'); @endphp
                @if ($logo)
                    <img alt="{{ setting('company_name') }}" height="45" class="navbar-brand-img" src="{{ asset($logo) }}">
                @else
                    {{ substr(setting('company_name'), 0, 15) }}...
                @endif
            </a>
            <div class="ml-auto">
                <!-- Sidenav toggler -->
                <div class="sidenav-toggler d-none d-xl-block" data-action="sidenav-unpin" data-target="#sidenav-main">
                    <div class="sidenav-toggler-inner">
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="navbar-inner">
            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
                <!-- Nav items -->
                <ul class="navbar-nav">
                    @can('menu-dashboard')
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('home*')) ? 'active' : '' }}" href="{{route('home')}}">
                            <i class="ni ni-shop text-primary"></i>
                            <span class="nav-link-text">Dashboard</span>
                        </a>
                    </li>
                    @endcan
                    @can('update-settings')
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('settings*')) ? 'active' : '' }}" href="{{route('settings.index')}}">
                                <i class="ni ni-settings-gear-65 text-primary"></i>
                                <span class="nav-link-text">Manage Settings</span>
                            </a>
                        </li>
                    @endcan


                    @canany(['view-entertainment', 'create-entertainment'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('entertainment*')) ? 'active' : '' }}" href="#navbar-entertainment"  data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-entertainment">
                                <i class="fas text-primary fa-list-alt"></i>
                                <span class="nav-link-text">Manage Entertainment</span>
                            </a>
                            <div class="collapse" id="navbar-entertainment">
                                <ul class="nav nav-sm flex-column">
                                 @can('view-listentertainment')
                                    <li class="nav-item">
                                        <a href="{{route('entertainment.index')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">List Data Entertainment</span></a>
                                    </li>
                                    @endcan
                                    @can( 'create-entertainment')
                                    <li class="nav-item">
                                        <a href="{{route('entertainment.create')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Tambah Data Entertainment</span></a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcanany


                    @canany(['view-category', 'create-category'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('category*')) ? 'active' : '' }}" href="#navbar-category"  data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-category">
                                <i class="fas text-primary fa-list-alt"></i>
                                <span class="nav-link-text">Manage Category</span>
                            </a>
                            <div class="collapse" id="navbar-category">
                                <ul class="nav nav-sm flex-column">
                                 @can('view-category')
                                    <li class="nav-item">
                                        <a href="{{route('category.index')}}" class="nav-link"><span class="sidenav-mini-icon">C </span><span class="sidenav-normal">All Categories</span></a>
                                    </li>
                                    @endcan
                                    @can( 'create-category')
                                    <li class="nav-item">
                                        <a href="{{route('category.create')}}" class="nav-link"><span class="sidenav-mini-icon">C </span><span class="sidenav-normal">Add New Category</span></a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcanany

                    @can('menu-manage-customer')
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('customer*')) ? 'active' : '' }}" href="{{route('customer.index')}}">
                            <i class="fas text-primary fa-users"></i>
                            <span class="nav-link-text">Manage Customer</span>
                        </a>
                    </li>
                    @endcan

                    @can('menu-manage-sales')
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('sales*')) ? 'active' : '' }}" href="{{route('sales.index')}}">
                            <i class="fas text-primary fa-user-tag"></i>
                            <span class="nav-link-text">Manage Sales</span>
                        </a>
                    </li>
                    @endcan

                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('team-support*')) ? 'active' : '' }}" href="{{route('team-support.index')}}">
                            <i class="fas text-primary fa-users-cog"></i>
                            <span class="nav-link-text">Manage Team Support</span>
                        </a>
                    </li>
<!-- Manage Data Perangkat -->
                    @canany(['view-perangkat', 'view-summary-perangkat','view-warehouse','perangkat-warehouse'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('perangkat*')) ? 'active' : '' }}" href="#navbar-perangkat"  data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-perangkat">
                                <i class="fas text-primary fa-list-alt"></i>
                                <span class="nav-link-text">Manage Perangkat</span>
                            </a>
                            <div class="collapse" id="navbar-perangkat">
                                <ul class="nav nav-sm flex-column">
                                 @can('view-perangkat')
                                    <li class="nav-item">
                                        <a href="{{route('perangkat.index')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Data Semua Perangkat</span></a>
                                    </li>
                                    @endcan
                                    @can( 'summary-perangkat')
                                    <li class="nav-item">
                                        <a href="{{route('perangkat.summary')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Summary Perangkat</span></a>
                                    </li>
                                    @endcan
                                    @can( 'view-warehouse')
                                    <li class="nav-item">
                                        <a href="{{route('warehouse.index')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Dokumen Masuk dan Keluar Gudang</span></a>
                                    </li>
                                    @endcan

                                    @can( 'perangkat-warehouse')
                                    <li class="nav-item">
                                        <a href="{{route('warehouse.perangkat')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Monitoring Perangkat Warehouse</span></a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcanany
    
                    @canany(['view-case', 'create-case'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('cases*')) ? 'active' : '' }}" href="#navbar-cases"  data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-cases">
                                <i class="fas text-primary fa-briefcase"></i>
                                <span class="nav-link-text">Case Management</span>
                            </a>
                            <div class="collapse" id="navbar-cases">
                                <ul class="nav nav-sm flex-column">
                                    @can('view-case')
                                    <li class="nav-item">
                                        <a href="{{route('cases.index')}}" class="nav-link">
                                            <span class="sidenav-mini-icon">D </span>
                                            <span class="sidenav-normal">Daftar Case</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('create-case')
                                    <li class="nav-item">
                                        <a href="{{route('cases.create')}}" class="nav-link">
                                            <span class="sidenav-mini-icon">C </span>
                                            <span class="sidenav-normal">Tambah Case</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view-case')
                                    <li class="nav-item">
                                        <a href="{{route('interference-ratio.index')}}" class="nav-link">
                                            <span class="sidenav-mini-icon">I </span>
                                            <span class="sidenav-normal">List Interference Ratio</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcanany

                    @canany(['view-project', 'create-project','maintenance-project'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('project*')) ? 'active' : '' }}" href="#navbar-project"  data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-project">
                                <i class="fas text-primary fa-list-alt"></i>
                                <span class="nav-link-text">Manage project</span>
                            </a>
                            <div class="collapse" id="navbar-project">
                                <ul class="nav nav-sm flex-column">
                                 @can('view-project')
                                    <li class="nav-item">
                                        <a href="{{route('project.index')}}" class="nav-link"><span class="sidenav-mini-icon">P </span><span class="sidenav-normal">Data Semua Project</span></a>
                                    </li>
                                    @endcan
                                 @can('maintenance-project')
                                    <li class="nav-item">
                                        <a href="{{route('project.maintenance')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Data Maintenance</span></a>
                                    </li>
                                    @endcan
                                    @can( 'create-project')
                                    <li class="nav-item">
                                        <a href="{{route('project.create')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Tambah Project Baru</span></a>
                                    </li>
                                    @endcan
                                    @can('view-project')
                                    <li class="nav-item">
                                        <a href="{{route('project.report.monitoring')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Monitoring Laporan</span></a>
                                    </li>
                                    @endcan
                                    @can('view-project')
                                    <li class="nav-item">
                                        <a href="{{route('project.ba.monitoring')}}" class="nav-link">
                                            <span class="sidenav-mini-icon">D </span>
                                            <span class="sidenav-normal">Monitoring BA</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcanany

                    @canany(['view-user', 'create-user'])

                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('users*')) ? 'active' : '' }}" href="#navbar-users"  data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-users">
                                <i class="fas text-primary fa-tasks"></i>
                                <span class="nav-link-text">Manage Users</span>
                            </a>
                            <div class="collapse" id="navbar-users">
                                <ul class="nav nav-sm flex-column">
                                 @can('view-user')
                                    <li class="nav-item">
                                        <a href="{{route('users.index')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">All Users</span></a>
                                    </li>
                                    @endcan
                                    @can( 'create-user')
                                    <li class="nav-item">
                                        <a href="{{route('users.create')}}" class="nav-link"><span class="sidenav-mini-icon">D </span><span class="sidenav-normal">Add New User</span></a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcanany

                    @canany(['view-permission', 'create-permission'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('permissions*')) ? 'active' : '' }}" href="{{route('permissions.index')}}">
                                <i class="fas fa-lock-open text-primary"></i>
                                <span class="nav-link-text">Manage Permissions</span>
                            </a>
                        </li>
                    @endcanany
                    @canany(['view-role', 'create-role'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('roles*')) ? 'active' : '' }}" href="{{route('roles.index')}}">
                                <i class="fas fa-lock text-primary"></i>
                                <span class="nav-link-text">Manage Roles</span>
                            </a>
                        </li>
                    @endcanany

                    @canany(['media'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('media*')) ? 'active' : '' }}" href="{{route('media.index')}}">
                                <i class="fas fa-images text-primary"></i>
                                <span class="nav-link-text">Manage Media</span>
                            </a>
                        </li>
                    @endcanany
                    @canany(['view-activity-log'])
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('activity-log*')) ? 'active' : '' }}" href="{{route('activity-log.index')}}">
                                <i class="fas fa-history text-primary"></i>
                                <span class="nav-link-text">Activity Log</span>
                            </a>
                        </li>
                    @endcanany
                    <li class="nav-item">
                        <hr class="my-3">
                    </li>
                    <li class="nav-item">
                            <a class="nav-link active active-pro" href="{{route('components')}}">
                                <i class="ni ni-send text-primary"></i>
                                <span class="nav-link-text">Components</span>
                            </a>
                        </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
