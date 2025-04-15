<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verificar E-mail - Sistema Bidding</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">

    <!-- Estilos customizados -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-verify-image">
                                <!-- Imagem de fundo para a tela de verificação -->
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Verificar Endereço de E-mail</h1>
                                    </div>

                                    @if (session('resent'))
                                        <div class="alert alert-success" role="alert">
                                            Um novo link de verificação foi enviado para seu endereço de e-mail.
                                        </div>
                                    @endif

                                    <p class="mb-4 text-center">
                                        Antes de continuar, por favor, verifique seu e-mail para um link de verificação.
                                        Se você não recebeu o e-mail,
                                    </p>

                                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-user btn-block w-100">
                                            Clique aqui para solicitar outro
                                        </button>
                                    </form>

                                    <hr>

                                    <div class="text-center">
                                        <a class="small" href="{{ route('logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Sair
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
</body>
</html>
