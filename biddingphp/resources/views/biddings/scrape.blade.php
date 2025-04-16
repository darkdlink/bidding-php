<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Importar Licitações - Sistema Bidding</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

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
                            <a class="nav-link" href="{{ route('biddings.index') }}">
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
                            <a class="nav-link active" href="{{ route('biddings.scrape.form') }}">
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Importar Licitações</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="{{ route('biddings.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Execução Manual de Scraping</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i> Selecione um órgão para importar licitações. O sistema utilizará as configurações de scraping ativas para obter os dados.
                                </div>

                                <form action="{{ route('biddings.scrape') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="agency_id" class="form-label"><strong>Órgão</strong></label>
                                        <select class="form-select @error('agency_id') is-invalid @enderror" id="agency_id" name="agency_id" required>
                                            <option value="">Selecione um órgão...</option>
                                            @foreach($agencies as $agency)
                                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('agency_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-download"></i> Iniciar Scraping
                                    </button>
                                </form>

                                @if(session('success'))
                                    <div class="alert alert-success mt-3">
                                        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger mt-3">
                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Informações sobre Scraping</h6>
                            </div>
                            <div class="card-body">
                                <h5 class="font-weight-bold text-gray-800">O que é scraping?</h5>
                                <p>
                                    Scraping é o processo de coleta automatizada de informações de sites de licitações públicas.
                                    O sistema acessa as páginas configuradas, extrai os dados relevantes e os importa para o sistema.
                                </p>

                                <h5 class="font-weight-bold text-gray-800 mt-4">Como funciona?</h5>
                                <ol>
                                    <li>Selecione o órgão para o qual deseja importar licitações</li>
                                    <li>O sistema buscará a configuração ativa para este órgão</li>
                                    <li>As licitações encontradas serão importadas automaticamente</li>
                                    <li>Se uma licitação já existir no sistema, ela será ignorada</li>
                                </ol>

                                <h5 class="font-weight-bold text-gray-800 mt-4">Dicas importantes</h5>
                                <ul>
                                    <li>O processo pode levar alguns minutos, dependendo do volume de dados</li>
                                    <li>As configurações devem estar corretamente definidas para cada órgão</li>
                                    <li>É recomendável verificar os logs após a execução para identificar possíveis erros</li>
                                </ul>

                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Atenção:</strong> Alguns sites podem bloquear requisições automatizadas. Em caso de problemas, verifique os registros de log do sistema.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs Recentes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Logs Recentes de Scraping</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Órgão</th>
                                        <th>Data de Execução</th>
                                        <th>Status</th>
                                        <th>Itens Encontrados</th>
                                        <th>Itens Processados</th>
                                        <th>Duração</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $recentLogs = App\Models\ScrapingLog::with('config.agency')
                                                                          ->orderBy('created_at', 'desc')
                                                                          ->limit(10)
                                                                          ->get();
                                    @endphp

                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td>{{ $log->config->agency->name ?? 'N/A' }}</td>
                                            <td>{{ $log->start_time->format('d/m/Y H:i:s') }}</td>
                                            <td>
                                                @if($log->status == 'success')
                                                    <span class="badge bg-success">Sucesso</span>
                                                @elseif($log->status == 'partial')
                                                    <span class="badge bg-warning">Parcial</span>
                                                @else
                                                    <span class="badge bg-danger">Falha</span>
                                                @endif
                                            </td>
                                            <td>{{ $log->items_found }}</td>
                                            <td>{{ $log->items_processed }}</td>
                                            <td>
                                                @if($log->end_time)
                                                    {{ $log->start_time->diff($log->end_time)->format('%i min %s seg') }}
                                                @else
                                                    Em andamento
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Nenhum log de scraping encontrado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <a href="{{ route('admin.scraping-logs.index') }}" class="btn btn-sm btn-primary mt-3">
                            <i class="fas fa-list"></i> Ver Todos os Logs
                        </a>
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

    <!-- Scripts customizados -->
    <script src="{{ asset('js/app.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Inicializa Select2 para selects
            $('#agency_id').select2({
                width: '100%',
                placeholder: 'Selecione um órgão...'
            });
        });
    </script>
</body>         
</html>
