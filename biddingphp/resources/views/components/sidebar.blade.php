<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-gavel"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Bidding</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Licitações
    </div>

    <!-- Nav Item - Licitações -->
    <li class="nav-item {{ Request::routeIs('biddings.index') || Request::routeIs('biddings.show') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('biddings.index') }}">
            <i class="fas fa-fw fa-search-dollar"></i>
            <span>Licitações Disponíveis</span>
        </a>
    </li>

    <!-- Nav Item - Propostas -->
    <li class="nav-item {{ Request::routeIs('proposals.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('proposals.index') }}">
            <i class="fas fa-fw fa-file-alt"></i>
            <span>Minhas Propostas</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Relatórios
    </div>

    <!-- Nav Item - Relatórios -->
    <li class="nav-item {{ Request::routeIs('dashboard.reports') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.reports') }}">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Análises Avançadas</span>
        </a>
    </li>

    <!-- Nav Item - Relatórios Dropdown -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReports"
            aria-expanded="true" aria-controls="collapseReports">
            <i class="fas fa-fw fa-file-pdf"></i>
            <span>Relatórios</span>
        </a>
        <div id="collapseReports" class="collapse" aria-labelledby="headingReports" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Tipos de Relatórios:</h6>
                <a class="collapse-item" href="{{ route('reports.monthly') }}">Mensal</a>
                <a class="collapse-item" href="{{ route('reports.performance') }}">Desempenho</a>
            </div>
        </div>
    </li>

    @can('access-admin')
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Administração
    </div>

    <!-- Nav Item - Admin -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdmin"
            aria-expanded="true" aria-controls="collapseAdmin">
            <i class="fas fa-fw fa-cog"></i>
            <span>Administração</span>
        </a>
        <div id="collapseAdmin" class="collapse" aria-labelledby="headingAdmin" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Opções de Admin:</h6>
                <a class="collapse-item" href="{{ route('admin.users.index') }}">Usuários</a>
                <a class="collapse-item" href="{{ route('admin.agencies.index') }}">Órgãos Licitantes</a>
                <a class="collapse-item" href="{{ route('admin.scraping-configs.index') }}">Config. de Scraping</a>
                <a class="collapse-item" href="{{ route('admin.settings') }}">Configurações</a>
            </div>
        </div>
    </li>
    @endcan

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
