<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Licitações - Sistema Bidding</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Estilos customizados -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Cabeçalho -->
    <header class="navbar navbar-dark sticky-top bg-primary flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="{{ route('dashboard') }}">Sistema Bidding</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link px-3 btn btn-link">Sair</button>
                </form>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Menu lateral -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('biddings.index') }}">
                                <i class="fas fa-gavel"></i>
                                Licitações
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('proposals.index') }}">
                                <i class="fas fa-file-alt"></i>
                                Propostas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('biddings.scrape.form') }}">
                                <i class="fas fa-download"></i>
                                Importar Licitações
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Relatórios</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('reports.monthly') }}">
                                <i class="fas fa-chart-bar"></i>
                                Relatório Mensal
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Conteúdo principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Licitações</h1>
                    <div>
                        @can('create', App\Models\Bidding::class)
                        <a href="{{ route('biddings.create') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Nova Licitação
                        </a>
                        @endcan

                        @can('scrape-biddings')
                        <a href="{{ route('biddings.scrape.form') }}" class="btn btn-sm btn-secondary shadow-sm ms-2">
                            <i class="fas fa-download fa-sm text-white-50"></i> Importar Licitações
                        </a>
                        @endcan
                    </div>
                </div>

                <!-- Alerta de sucesso/erro -->
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Filtros -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('biddings.index') }}" method="GET" class="row row-cols-lg-auto g-3 align-items-center">
                            <div class="col-12">
                                <div class="input-group">
                                    <div class="input-group-text">Busca</div>
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Buscar..." value="{{ request('search') }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-group">
                                    <div class="input-group-text">Status</div>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Todos os Status</option>
                                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicada</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Em Andamento</option>
                                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Fechada</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                        <option value="awarded" {{ request('status') == 'awarded' ? 'selected' : '' }}>Adjudicada</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-group">
                                    <div class="input-group-text">Órgão</div>
                                    <select class="form-select" id="agency_id" name="agency_id">
                                        <option value="">Todos os Órgãos</option>
                                        @foreach($agencies as $agency)
                                        <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                            {{ $agency->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="input-group">
                                    <div class="input-group-text">Tipo</div>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">Todos os Tipos</option>
                                        @foreach($biddingTypes as $type)
                                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="{{ route('biddings.index') }}" class="btn btn-secondary ms-2">Limpar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabela de Licitações -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Licitações Disponíveis</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="biddingsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>
                                            <a href="{{ route('biddings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'title', 'direction' => request('sort') === 'title' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Título
                                                @if(request('sort') === 'title')
                                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('biddings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'agency_id', 'direction' => request('sort') === 'agency_id' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Órgão
                                                @if(request('sort') === 'agency_id')
                                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('biddings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'bidding_type', 'direction' => request('sort') === 'bidding_type' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Tipo
                                                @if(request('sort') === 'bidding_type')
                                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('biddings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'publication_date', 'direction' => request('sort') === 'publication_date' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Data de Publicação
                                                @if(request('sort') === 'publication_date')
                                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('biddings.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'closing_date', 'direction' => request('sort') === 'closing_date' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Data de Fechamento
                                                @if(request('sort') === 'closing_date')
                                                    <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($biddings as $bidding)
                                    <tr>
                                        <td>
                                            <a href="{{ route('biddings.show', $bidding) }}">
                                                {{ Str::limit($bidding->title, 50) }}
                                            </a>
                                        </td>
                                        <td>{{ $bidding->agency->name ?? '-' }}</td>
                                        <td>{{ ucfirst($bidding->bidding_type) }}</td>
                                        <td>{{ $bidding->publication_date ? $bidding->publication_date->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            @if($bidding->closing_date)
                                                @if($bidding->daysUntilClosing() <= 3 && $bidding->isActive())
                                                    <span class="text-danger">{{ $bidding->closing_date->format('d/m/Y H:i') }}</span>
                                                @else
                                                    {{ $bidding->closing_date->format('d/m/Y H:i') }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($bidding->status == 'published')
                                                <span class="badge bg-success">Publicada</span>
                                            @elseif($bidding->status == 'in_progress')
                                                <span class="badge bg-info">Em Andamento</span>
                                            @elseif($bidding->status == 'closed')
                                                <span class="badge bg-secondary">Fechada</span>
                                            @elseif($bidding->status == 'awarded')
                                                <span class="badge bg-primary">Adjudicada</span>
                                            @elseif($bidding->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelada</span>
                                            @else
                                                <span class="badge bg-warning">Rascunho</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('biddings.show', $bidding) }}" class="btn btn-sm btn-info" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($bidding->canSubmitProposal())
                                                    <a href="{{ route('proposals.create', $bidding) }}" class="btn btn-sm btn-success" title="Criar Proposta">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                @endif
                                                
                                                @can('update', $bidding)
                                                    <a href="{{ route('biddings.edit', $bidding) }}" class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('delete', $bidding)
                                                    <form action="{{ route('biddings.destroy', $bidding) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta licitação?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhuma licitação encontrada.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="mt-3">
                            {{ $biddings->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Scripts customizados -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializa Select2 para selects
            $('#agency_id, #status, #type').select2({
                width: '100%'
            });
        });
    </script>
</body>
</html>