<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Monitoramento de Experimentos</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#303a51] min-h-screen">
    <header class="w-full bg-[#26557d] py-4 shadow-lg">
        <div class="container mx-auto px-6 flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-white">
                <i class="fas fa-users mr-2"></i>Lista de Usuários
            </h1>
            <div class="flex space-x-2">
                <a href="{{ route('users.create') }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                    <i class="fas fa-plus mr-1"></i>Novo Usuário
                </a>
                <a href="{{ route('home') }}" class="px-4 py-2 bg-white text-[#26557d] rounded-lg hover:bg-gray-100 transition">
                    Voltar
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-10">
        @if(!empty($users))
            <div class="bg-white rounded-2xl shadow-lg p-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Nome</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Administrador</th>
                            <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-100">
                            <td class="px-4 py-3 text-gray-800">{{ $user['name'] }}</td>
                            <td class="px-4 py-3">
                                @if($user['isAdmin'])
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sim</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Não</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center space-x-2">
                                <a href="{{ route('users.edit', $user['name']) }}" class="text-blue-600 hover:underline" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user['name']) }}" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir {{ $user['name'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                Nenhum usuário encontrado.
            </div>
        @endif
    </div>
</body>
</html>
