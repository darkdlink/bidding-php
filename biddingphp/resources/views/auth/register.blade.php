<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registrar - Sistema Bidding</title>

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
                            <div class="col-lg-5 d-none d-lg-block bg-register-image">
                                <!-- Imagem de fundo para a tela de registro -->
                            </div>
                            <div class="col-lg-7">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Criar uma Conta</h1>
                                    </div>

                                    <form class="user" method="POST" action="{{ route('register') }}">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <input type="text" class="form-control form-control-user @error('name') is-invalid @enderror"
                                                id="name" name="name" placeholder="Nome Completo"
                                                value="{{ old('name') }}" required autofocus>

                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <input type="email" class="form-control form-control-user @error('email') is-invalid @enderror"
                                                id="email" name="email" placeholder="E-mail"
                                                value="{{ old('email') }}" required>

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group row mb-3">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <input type="password" class="form-control form-control-user @error('password') is-invalid @enderror"
                                                    id="password" name="password" placeholder="Senha" required>

                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="password" class="form-control form-control-user"
                                                    id="password-confirm" name="password_confirmation"
                                                    placeholder="Confirmar Senha" required>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block w-100">
                                            Registrar Conta
                                        </button>
                                    </form>

                                    <hr>

                                    @if (Route::has('password.request'))
                                        <div class="text-center">
                                            <a class="small" href="{{ route('password.request') }}">
                                                Esqueceu sua senha?
                                            </a>
                                        </div>
                                    @endif

                                    <div class="text-center">
                                        <a class="small" href="{{ route('login') }}">
                                            Já tem uma conta? Faça login!
                                        </a>
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
