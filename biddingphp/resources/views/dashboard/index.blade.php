<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard - Sistema Bidding</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <link href="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.css" rel="stylesheet">
    
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
                            <a class="nav-link active" href="{{ route('dashboard') }}">
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
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    <div>
                        <a href="{{ route('reports.monthly') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Gerar Relatório Mensal
                        </a>
                    </div>
                </div>

                <!-- Indicadores Rápidos -->
                <div class="row">
                    <!-- Licitações Ativas -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start border-primary shadow h-100 py-2" style="border-left-width: 4px !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Licitações Ativas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeBiddings }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Licitações Fechando em Breve -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start border-warning shadow h-100 py-2" style="border-left-width: 4px !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Fechando em 7 Dias</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $closingSoonBiddings }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Taxa de Sucesso -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start border-info shadow h-100 py-2" style="border-left-width: 4px !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Taxa de Sucesso</div>
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-auto">
                                                <div class="h5 mb-0 me-3 font-weight-bold text-gray-800">{{ number_format($successRate, 1) }}%</div>
                                            </div>
                                            <div class="col">
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                        style="width: {{ min($successRate, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percent fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Valor Total Ganho -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-start border-success shadow h-100 py-2" style="border-left-width: 4px !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Valor Total Ganho</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">R$ {{ number_format($totalWonValue, 2, ',', '.') }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row">
                    <!-- Licitações por Mês -->
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Licitações por Mês</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="biddingsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Propostas por Mês -->
                    <div class="col-xl-6 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Propostas por Mês</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="proposalsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Análises Detalhadas -->
                <div class="row">
                    <!-- Licitações por Tipo -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Licitações por Tipo</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie pt-4 pb-2">
                                    <canvas id="typePieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Licitações por Órgão -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Licitações por Órgão (Top 5)</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-bar">
                                    <canvas id="agencyBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listagens -->
                <div class="row">
                    <!-- Licitações Recentes -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Licitações Recentes</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Título</th>
                                                <th>Órgão</th>
                                                <th>Data Abertura</th>
                                                <th>Data Fechamento</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentBiddings as $bidding)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('biddings.show', $bidding) }}">
                                                        {{ Str::limit($bidding->title, 30) }}
                                                    </a>
                                                </td>
                                                <td>{{ $bidding->agency->name ?? '-' }}</td>
                                                <td>{{ $bidding->opening_date ? $bidding->opening_date->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $bidding->closing_date ? $bidding->closing_date->format('d/m/Y') : '-' }}</td>
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
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('biddings.index') }}" class="btn btn-sm btn-primary">Ver Todas</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Propostas Recentes -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Minhas Propostas Recentes</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Licitação</th>
                                                <th>Valor</th>
                                                <th>Desconto</th>
                                                <th>Data de Envio</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentProposals as $proposal)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('proposals.show', $proposal) }}">
                                                        {{ Str::limit($proposal->bidding->title ?? 'Sem título', 30) }}
                                                    </a>
                                                </td>
                                                <td>R$ {{ number_format($proposal->total_value, 2, ',', '.') }}</td>
                                                <td>{{ number_format($proposal->discount_percentage, 2) }}%</td>
                                                <td>{{ $proposal->submission_date ? $proposal->submission_date->format('d/m/Y') : '-' }}</td>
                                                <td>
                                                    @if($proposal->status == 'submitted')
                                                        <span class="badge bg-info">Enviada</span>
                                                    @elseif($proposal->status == 'won')
                                                        <span class="badge bg-success">Vencedora</span>
                                                    @elseif($proposal->status == 'lost')
                                                        <span class="badge bg-danger">Perdida</span>
                                                    @elseif($proposal->status == 'cancelled')
                                                        <span class="badge bg-secondary">Cancelada</span>
                                                    @else
                                                        <span class="badge bg-warning">Rascunho</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('proposals.index') }}" class="btn btn-sm btn-primary">Ver Todas</a>
                                </div>
                            </div>
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    
    <!-- Scripts customizados -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // Gráfico de Licitações por Mês
            var biddingsChartCanvas = document.getElementById("biddingsChart");
            var biddingsChart = new Chart(biddingsChartCanvas, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_column($biddingsChartData, 'month')) !!},
                    datasets: [{
                        label: "Licitações",
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: {!! json_encode(array_column($biddingsChartData, 'total')) !!},
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                // Inclui prefixo
                                callback: function(value, index, values) {
                                    return value;
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': ' + tooltipItem.yLabel;
                            }
                        }
                    }
                }
            });

            // Gráfico de Propostas por Mês
            var proposalsChartCanvas = document.getElementById("proposalsChart");
            var proposalsChart = new Chart(proposalsChartCanvas, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($proposalsChartData, 'month')) !!},
                    datasets: [
                        {
                            label: "Total",
                            backgroundColor: "rgba(78, 115, 223, 0.8)",
                            borderColor: "rgba(78, 115, 223, 1)",
                            data: {!! json_encode(array_column($proposalsChartData, 'total')) !!},
                        },
                        {
                            label: "Vencedoras",
                            backgroundColor: "rgba(28, 200, 138, 0.8)",
                            borderColor: "rgba(28, 200, 138, 1)",
                            data: {!! json_encode(array_column($proposalsChartData, 'won')) !!},
                        }
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'month'
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 6
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                min: 0,
                                maxTicksLimit: 5,
                                padding: 10,
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    legend: {
                        display: true
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    }
                }
            });

            // Gráfico de pizza - Licitações por Tipo
            var typePieChartCanvas = document.getElementById("typePieChart");
            var typePieChart = new Chart(typePieChartCanvas, {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($biddingsByType)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($biddingsByType)) !!},
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#858796'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#484a54', '#60616f'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    cutoutPercentage: 0,
                }
            });

            // Gráfico de barras - Licitações por Órgão
            var agencyBarChartCanvas = document.getElementById("agencyBarChart");
            var agencyBarChart = new Chart(agencyBarChartCanvas, {
                type: 'horizontalBar',
                data: {
                    labels: {!! json_encode(array_column($biddingsByAgency, 'name')) !!},
                    datasets: [{
                        label: "Licitações",
                        backgroundColor: "#4e73df",
                        hoverBackgroundColor: "#2e59d9",
                        borderColor: "#4e73df",
                        data: {!! json_encode(array_column($biddingsByAgency, 'total')) !!},
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                min: 0,
                                maxTicksLimit: 5,
                            },
                            gridLines: {
                                display: true,
                                drawBorder: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 10,
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            }
                        }],
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    }
                }
            });
        });
    </script>
</body>
</html>