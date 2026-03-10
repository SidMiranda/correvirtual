<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Corre Virtual')</title>

    <link rel="stylesheet" href="{{ asset('css/auth-modal.css') }}">
    
    @stack('styles')
</head>
<body>

    @if(session('success'))
        <div style="background: #1db954; color: white; padding: 15px; text-align: center; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #e74c3c; color: white; padding: 15px; text-align: center;">
            <ul style="margin: 0; padding-left: 20px; list-style-type: none;">
                @foreach($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <div class="modal-overlay" id="authModal">
        <div class="modal">
            <span class="close-modal" onclick="closeModal()">×</span>

            <form method="POST" action="{{ route('login') }}" class="form-container" id="loginForm">
                @csrf
                <h2>Login</h2>
                
                <input type="text" name="email_or_cpf" placeholder="CPF ou Email" value="{{ old('email_or_cpf') }}" required>
                <input type="password" name="password" placeholder="Senha" required>
                
                <button type="submit" class="btn-primary">Entrar</button>
                
                <div class="form-links">
                    <span onclick="showRegister()">Criar conta</span>
                    <span onclick="showReset()">Esqueci a senha</span>
                </div>
            </form>

            <form method="POST" action="{{ route('register') }}" class="form-container hidden" id="registerForm">
                @csrf
                <h2>Registrar</h2>
                
                <input type="text" name="name" placeholder="Nome completo" value="{{ old('name') }}" required>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}" required>
                <select name="sex" required>
                    <option value="">Sexo</option>
                    <option value="male">Masculino</option>
                    <option value="female">Feminino</option>
                    <option value="other">Outro</option>
                </select>
                <input type="text" name="phone" placeholder="Celular" value="{{ old('phone') }}" required>
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                <input type="text" name="cpf" placeholder="CPF" value="{{ old('cpf') }}" required>
                <input type="password" name="password" placeholder="Senha" required>
                
                <button type="submit" class="btn-primary">Registrar</button>
                
                <div class="form-links">
                    <span onclick="showLogin()">Já tenho conta</span>
                </div>
            </form>

            <div class="form-container hidden" id="resetForm">
                <h2>Resetar Senha</h2>
                
                <input type="email" placeholder="Digite seu email">
                
                <button type="button" class="btn-primary">Enviar nova senha</button>
                
                <div class="form-links">
                    <span onclick="showLogin()">Voltar ao login</span>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/auth-modal.js') }}"></script>
    
    @stack('scripts')
</body>
</html>