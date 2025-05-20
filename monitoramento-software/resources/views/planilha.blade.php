<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados da Planilha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .experimento-card {
            transition: all 0.3s ease;
        }
        .experimento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
        table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Cabeçalho e Botão Voltar -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dados dos Experimentos</h1>
            <a href="/" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>

        @if (!empty($experimentos))
            <div class="space-y-8">
                @foreach ($experimentos as $experimento)
                    <div class="experimento-card bg-white rounded-xl shadow-md overflow-hidden p-6">
                        <!-- Cabeçalho do Experimento -->
                        <div class="mb-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800">{{ $experimento['nome'] }}</h2>
                                    <div class="flex space-x-4 mt-2 text-sm text-gray-600">
                                        <span><i class="far fa-clock mr-1"></i> <strong>Início:</strong> {{ $experimento['inicio'] }}</span>
                                        <span><i class="far fa-clock mr-1"></i> <strong>Fim:</strong> {{ $experimento['fim'] ?? 'Não registrado' }}</span>
                                        <span><i class="fas fa-ruler-combined mr-1"></i> <strong>Medições:</strong> {{ count($experimento['dados']) }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('experimentos.grafico', $experimento['id']) }}" 
                                   class="flex items-center px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm">
                                    <i class="fas fa-chart-line mr-1"></i> Ver Gráfico
                                </a>
                            </div>
                        </div>

                        <!-- Tabela de Dados -->
                        <div class="table-container border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tempo Decorrido
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Temperatura (°C)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($experimento['dados'] as $linha)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $linha['tempo'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $linha['temperatura'] > 30 ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $linha['temperatura'] ?? '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md p-8 text-center">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900">Nenhum dado encontrado</h3>
                <p class="mt-2 text-sm text-gray-500">Não há experimentos registrados na planilha.</p>
            </div>
        @endif
    </div>
</body>
</html>