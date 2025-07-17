<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Monitoramento de Experimentos</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#303a51] min-h-screen flex items-center justify-center">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Editar Usuário</h2>
        <form method="POST" action="{{ route('users.update', $name) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="rowIndex" value="{{ $rowIndex }}">

            <div>
                <label for="name" class="block text-gray-700 font-medium mb-1">Nome</label>
                <input id="name" name="name" type="text" value="{{ old('name', $name) }}" required autofocus
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-gray-700 font-medium mb-1">
                    Senha <span class="text-sm text-gray-500">(deixe em branco para manter a atual)</span>
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    value="{{ old('password') }}"              {{-- fica só old('password') --}}
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input id="isAdmin" name="isAdmin" type="checkbox" value="1"
                       {{ old('isAdmin', $isAdmin ?? false) ? 'checked' : '' }}
                       class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                <label for="isAdmin" class="ml-2 block text-gray-700">É Administrador?</label>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('users.index') }}"
                   class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-[#26557d] text-white rounded-lg hover:bg-[#1f4566]">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</body>
</html>
