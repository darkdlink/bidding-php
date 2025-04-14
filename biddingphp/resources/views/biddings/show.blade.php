<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detalhes da Licitação - Sistema Bidding</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos customizados -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <style>
        /* Estilos para impressão */
        @media print {
            .no-print {
                display: none !important;
            }
            
            .card {
                border: 1px solid #ddd !important;
                margin-bottom: 20px !important;
            }
            
            .card-header {
                background-color: #f8f9fc !important;
                border-bottom: 1px solid #ddd !important;
                padding: 10px 15px !important;
            }
            
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            
            table, th, td {
                border: 1px solid #ddd !important;
            }
            
            th, td {
                padding: 10px !important;
                text-align: left !important;
            }
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="navbar navbar-dark sticky-top bg-primary flex-md-nowrap p-0 shadow no-print">
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
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse no-print">
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
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 no-print">
                    <h1 class="h3 mb-0 text-gray-800">Detalhes da Licitação</h1>
                    <div>
                        @if($bidding->canSubmitProposal())
                            @if($userHasProposal)
                                <a href="{{ route('proposals.index', ['bidding_id' => $bidding->id]) }}" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-file-alt fa-sm text-white-50"></i> Ver Minhas Propostas
                                </a>
                            @else
                                <a href="{{ route('proposals.create', $bidding) }}" class="btn btn-sm btn-success shadow-sm">
                                    <i class="fas fa-plus fa-sm text-white-50"></i> Criar Proposta
                                </a>
                            @endif
                        @endif

                        @can('update', $bidding)
                            <a href="{{ route('biddings.edit', $bidding) }}" class="btn btn-sm btn-info shadow-sm ms-2">
                                <i class="fas fa-edit fa-sm text-white-50"></i> Editar
                            </a>
                        @endcan

                        <a href="{{ route('biddings.index') }}" class="btn btn-sm btn-secondary shadow-sm ms-2">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Voltar
                        </a>
                        
                        <button onclick="window.print()" class="btn btn-sm btn-outline-dark shadow-sm ms-2">
                            <i class="fas fa-print fa-sm"></i> Imprimir
                        </button>
                    </div>
                </div>

                <!-- Alerta de Fechamento Próximo -->
                @if($bidding->isActive() && $bidding->daysUntilClosing() <= 3)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Atenção! Esta licitação encerra em 
                        @if($bidding->daysUntilClosing() == 0)
                            <strong>menos de 24 horas</strong>.
                        @else
                            <strong>{{ $bidding->daysUntilClosing() }} {{ $bidding->daysUntilClosing() == 1 ? 'dia' : 'dias' }}</strong>.
                        @endif
                    </div>
                @endif

                <!-- Informações Principais -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Informações da Licitação</h6>
                                <div class="dropdown no-arrow no-print">
                                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                        <li><h6 class="dropdown-header">Ações:</h6></li>
                                        @if($bidding->document_url)
                                            <li><a class="dropdown-item" href="{{ $bidding->document_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                                Ver Edital Original
                                            </a></li>
                                        @endif
                                        <li><a class="dropdown-item" href="#" onclick="window.print(); return false;">
                                            <i class="fas fa-print fa-sm fa-fw me-2 text-gray-400"></i>
                                            Imprimir
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Título:</strong> {{ $bidding->title }}</p>
                                        <p><strong>ID Externo:</strong> {{ $bidding->external_id ?? 'N/A' }}</p>
                                        <p><strong>Órgão:</strong> {{ $bidding->agency->name ?? 'N/A' }}</p>
                                        <p><strong>Tipo:</strong> {{ ucfirst($bidding->bidding_type) }}</p>
                                        <p><strong>Modalidade:</strong> {{ $bidding->modality ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong> 
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
                                        </p>
                                        <p><strong>Data de Publicação:</strong> {{ $bidding->publication_date ? $bidding->publication_date->format('d/m/Y') : 'N/A' }}</p>
                                        <p><strong>Data de Abertura:</strong> {{ $bidding->opening_date ? $bidding->opening_date->format('d/m/Y H:i') : 'N/A' }}</p>
                                        <p><strong>Data de Fechamento:</strong> {{ $bidding->closing_date ? $bidding->closing_date->format('d/m/Y H:i') : 'N/A' }}</p>
                                        <p><strong>Valor Estimado:</strong> {{ $bidding->estimated_value ? 'R$ ' . number_format($bidding->estimated_value, 2, ',', '.') : 'N/A' }}</p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h6 class="font-weight-bold">Descrição:</h6>
                                <div class="mb-4">
                                    {!! nl2br(e($bidding->description)) ??  '<em>Sem descrição</em>' !!}
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="font-weight-bold">Contato:</h6>
                                        <p><i class="fas fa-envelope me-1"></i> {{ $bidding->contact_email ?? 'N/A' }}</p>
                                        <p><i class="fas fa-phone me-1"></i> {{ $bidding->contact_phone ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        @if($bidding->document_url)
                                            <h6 class="font-weight-bold">Documento:</h6>
                                            <p>
                                                <a href="{{ $bidding->document_url }}" target="_blank" class="btn btn-sm btn-outline-primary no-print">
                                                    <i class="fas fa-external-link-alt"></i> Ver Edital Original
                                                </a>
                                                <span class="d-none d-print-block">{{ $bidding->document_url }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Status Card -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Status da Licitação</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    @if($bidding->isActive())
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i> Licitação ativa para envio de propostas.
                                        </div>
                                        @if($bidding->closing_date)
                                            <p class="mb-0">
                                                <strong>Tempo Restante:</strong><br>
                                                @if($bidding->daysUntilClosing() == 0)
                                                    <span class="text-danger">Menos de 24 horas</span>
                                                @else
                                                    {{ $bidding->daysUntilClosing() }} {{ $bidding->daysUntilClosing() == 1 ? 'dia' : 'dias' }}
                                                @endif
                                            </p>
                                        @endif
                                    @else
                                        <div class="alert alert-secondary">
                                            <i class="fas fa-times-circle"></i> Esta licitação não está aceitando propostas.
                                        </div>
                                        <p class="mb-0">
                                            <strong>Motivo:</strong><br>
                                            @if($bidding->status == 'draft')
                                                Licitação em rascunho, aguardando publicação.
                                            @elseif($bidding->status == 'closed')
                                                Período de envio de propostas encerrado.
                                            @elseif($bidding->status == 'awarded')
                                                Licitação já foi adjudicada.
                                            @elseif($bidding->status == 'cancelled')
                                                Licitação foi cancelada.
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                
                                @if($bidding->canSubmitProposal())
                                    <div class="text-center mt-4 no-print">
                                        @if($userHasProposal)
                                            <a href="{{ route('proposals.index', ['bidding_id' => $bidding->id]) }}" class="btn btn-block btn-primary w-100">
                                                <i class="fas fa-file-alt"></i> Ver Minhas Propostas
                                            </a>
                                        @else
                                            <a href="{{ route('proposals.create', $bidding) }}" class="btn btn-block btn-success w-100">
                                                <i class="fas fa-plus"></i> Criar Proposta
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Anexos -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Anexos</h6>
                            </div>
                            <div class="card-body">
                                @if($bidding->attachments->count() > 0)
                                    <ul class="list-group">
                                        @foreach($bidding->attachments as $attachment)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-file me-2"></i>
                                                    {{ $attachment->file_name }}
                                                    @if($attachment->description)
                                                        <br><small class="text-muted">{{ $attachment->description }}</small>
                                                    @endif
                                                </div>
                                                <a href="{{ route('biddings.attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary no-print">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-center text-muted">Nenhum anexo disponível.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Itens da Licitação -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Itens da Licitação</h6>
                    </div>
                    <div class="card-body">
                        @if($bidding->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Descrição</th>
                                            <th>Quantidade</th>
                                            <th>Unidade</th>
                                            <th>Preço Unitário Est.</th>
                                            <th>Valor Total Est.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bidding->items as $item)
                                            <tr>
                                                <td>{{ $item->item_number ?? $loop->iteration }}</td>
                                                <td>{{ $item->description }}</td>
                                                <td>{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                                <td>{{ $item->unit ?? '-' }}</td>
                                                <td>
                                                    @if($item->estimated_unit_price)
                                                        R$ {{ number_format($item->estimated_unit_price, 2, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->estimated_unit_price)
                                                        R$ {{ number_format($item->estimated_unit_price * $item->quantity, 2, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-end">Valor Total Estimado:</th>
                                            <th>R$ {{ number_format($bidding->totalEstimatedValue(), 2, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <p class="text-center text-muted">Nenhum item disponível para esta licitação.</p>
                        @endif
                    </div>
                </div>

                <!-- Propostas (apenas para admins) -->
                @can('view-proposals', $bidding)
                    <div class="card shadow mb-4 no-print">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Propostas Recebidas</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $allProposals = $bidding->proposals()->with('user')->get();
                            @endphp
                            
                            @if($allProposals->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Proponente</th>
                                                <th>Valor Total</th>
                                                <th>Desconto</th>
                                                <th>Data de Envio</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allProposals->whereIn('status', ['submitted', 'won', 'lost']) as $proposal)
                                                <tr>
                                                    <td>{{ $proposal->user->name }}</td>
                                                    <td>R$ {{ number_format($proposal->total_value, 2, ',', '.') }}</td>
                                                    <td>{{ number_format($proposal->discount_percentage, 2) }}%</td>
                                                    <td>{{ $proposal->submission_date ? $proposal->submission_date->format('d/m/Y H:i') : '-' }}</td>
                                                    <td>
                                                        @if($proposal->status == 'submitted')
                                                            <span class="badge bg-info">Enviada</span>
                                                        @elseif($proposal->status == 'won')
                                                            <span class="badge bg-success">Vencedora</span>
                                                        @elseif($proposal->status == 'lost')
                                                            <span class="badge bg-danger">Perdida</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.proposals.show', $proposal) }}" class="btn btn-sm btn-info" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            
                                                            @if($bidding->status == 'closed' && $proposal->status == 'submitted')
                                                                <a href="{{ route('admin.proposals.mark-as-winner', $proposal) }}" class="btn btn-sm btn-success" title="Marcar como Vencedora" onclick="return confirm('Confirma que esta proposta é a vencedora?');">
                                                                    <i class="fas fa-trophy"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-center text-muted">Ainda não foram recebidas propostas para esta licitação.</p>
                            @endif
                        </div>
                    </div>
                @endcan
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    
    <!-- Scripts customizados -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // Se necessário, adicione scripts específicos para esta página
        });
    </script>
</body>
</html>