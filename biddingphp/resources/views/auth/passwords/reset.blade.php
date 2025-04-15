<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Redefinir Senha - Sistema Bidding</title>

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
                            <div class="col-lg-6 d-none d-lg-block bg-password-image">
                                <!-- Imagem de fundo para a tela de redefinição de senha -->
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Redefinir Senha</h1>
                                    </div>

                                    <form class="user" method="POST" action="{{ route('password.update') }}">
                                        @csrf

                                        <input type="hidden" name="token" value="{{ $token }}">

                                        <div class="form-group mb-3">
                                            <input type="email" class="form-control form-control-user @error('email') is-invalid @enderror"
                                                id="email" name="email" placeholder="E-mail"
                                                value="{{ $email ?? old('email') }}" required readonly>

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <input type="password" class="form-control form-control-user @error('password') is-invalid @enderror"
                                                id="password" name="password" placeholder="Nova Senha" required>

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <input type="password" class="form-control form-control-user"
                                                id="password-confirm" name="password_confirmation"
                                                placeholder="Confirmar Nova Senha" required>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block w-100">
                                            Redefinir Senha
                                        </button>
                                    </form>

                                    <hr>

                                    <div class="text-center">
                                        <a class="small" href="{{ route('login') }}">
                                            Voltar para Login
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
