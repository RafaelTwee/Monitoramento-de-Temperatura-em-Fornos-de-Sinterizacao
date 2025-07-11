<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoramento de Experimentos</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#303a51] flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-semibold text-center mb-6 text-gray-800">Login</h2>
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-gray-700 font-medium mb-1">Usuário</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-gray-700 font-medium mb-1">Senha</label>
                <input id="password" name="password" type="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center px-4 py-2 bg-[#26557d] text-white font-semibold rounded-lg hover:bg-[#1f4566] transition">
                    Entrar
                </button>
            </div>
        </form>
    </div>
</body>
</html>