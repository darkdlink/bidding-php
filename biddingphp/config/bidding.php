<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bidding Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains specific configuration for the bidding system.
    |
    */

    // Configurações de Scraping
    'scraping' => [
        // Número máximo de tentativas para scraping
        'max_attempts' => 3,

        // Tempo limite para requisições em segundos
        'timeout' => 60,

        // Intervalo entre requisições em milissegundos
        'request_interval' => 2000,

        // User agent para requisições
        'user_agent' => 'Bidding System/1.0',

        // Caminho para armazenar arquivos temporários
        'temp_path' => storage_path('app/scraping'),
    ],

    // Configurações de Propostas
    'proposals' => [
        // Porcentagem mínima de desconto permitida
        'min_discount' => 0,

        // Porcentagem máxima de desconto permitida
        'max_discount' => 50,

        // Tempo máximo para edição após fechamento da licitação (em horas)
        'edit_after_closing' => 24,

        // Tipos de arquivos permitidos para anexos
        'allowed_attachments' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png',
        ],

        // Tamanho máximo de anexos em MB
        'max_attachment_size' => 10,
    ],

    // Configurações de Notificações
    'notifications' => [
        // Notificações por email
        'email' => [
            'enabled' => true,
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@bidding.com'),
                'name' => env('MAIL_FROM_NAME', 'Bidding System'),
            ],
        ],

        // Notificações no sistema
        'system' => [
            'enabled' => true,
            'expiration_days' => 30, // dias para expirar notificações
        ],

        // Notificações push
        'push' => [
            'enabled' => false, // desabilitado por padrão
        ],
    ],

    // Configurações de Análise
    'analytics' => [
        // Período padrão para análises em meses
        'default_period' => 6,

        // Tipos de gráficos disponíveis
        'available_charts' => [
            'line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea',
        ],
    ],

    // Configurações de Relatórios
    'reports' => [
        // Caminho para armazenar relatórios gerados
        'storage_path' => storage_path('app/reports'),

        // Validade dos relatórios em dias
        'expiration_days' => 30,

        // Formatos disponíveis
        'formats' => [
            'xlsx' => 'Excel',
            'pdf' => 'PDF',
            'csv' => 'CSV',
        ],
    ],

    // Papéis e permissões
    'roles' => [
        'admin' => [
            'name' => 'Administrador',
            'permissions' => ['*'],
        ],
        'manager' => [
            'name' => 'Gerente',
            'permissions' => [
                'biddings.*', 'proposals.*', 'reports.*', 'scrape-biddings'
            ],
        ],
        'analyst' => [
            'name' => 'Analista',
            'permissions' => [
                'biddings.index', 'biddings.show',
                'proposals.*',
                'reports.view'
            ],
        ],
        'viewer' => [
            'name' => 'Visualizador',
            'permissions' => [
                'biddings.index', 'biddings.show',
                'proposals.index', 'proposals.show',
            ],
        ],
    ],
];
