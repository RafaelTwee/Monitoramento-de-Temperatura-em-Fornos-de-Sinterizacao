<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"   content="width=device-width, initial-scale=1.0">
  <title>Cadastrar Usuário</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    rel="stylesheet">
</head>
<body class="bg-[#303a51] min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Novo Usuário</h2>
    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
      @csrf

      <div>
        <label for="name" class="block text-gray-700 mb-1">Nome</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required
               class="w-full px-4 py-2 border rounded-lg focus:ring-indigo-500" />
        @error('name')
          <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="password" class="block text-gray-700 mb-1">Senha</label>
        <input id="password" name="password" type="password" required
               class="w-full px-4 py-2 border rounded-lg focus:ring-indigo-500" />
        @error('password')
          <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex items-center">
        <input id="isAdmin" name="isAdmin" type="checkbox" value="1"
               {{ old('isAdmin') ? 'checked' : '' }}
               class="h-5 w-5 text-indigo-600 border-gray-300 rounded" />
        <label for="isAdmin" class="ml-2 text-gray-700">É Administrador?</label>
      </div>

      <div class="flex justify-between">
        <a href="{{ route('users.index') }}"
           class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
          Cancelar
        </a>
        <button type="submit"
                class="px-4 py-2 bg-[#26557d] text-white rounded-lg hover:bg-[#1f4566]">
          Cadastrar
        </button>
      </div>
    </form>
  </div>
</body>
</html>
