<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico do Experimento</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 20px; 
        }
        .container { 
            max-width: 900px;
            margin: 0 auto;
        }

        .experimento-card {
            transition: all 0.3s ease;
        }
        .experimento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .table-container {
            width: 100%;
            height: 500px;
            margin-top: 20px;
        }
        table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .btn-voltar { 
            display: inline-block;
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
            transition: background-color 0.3s;
        }
        .btn-voltar:hover { 
            background-color: #d32f2f; 
        }
        .chart-container {
            width: 100%;
            height: 500px;
            margin-top: 20px;
        }
        .text-danger { color: red; }

    </style>
</head>
<body>
    <div class="container">
        <div class="flex justify-between items-center mb-4 mt-8"> 
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-chart-line mr-2"></i> Gráfico do Experimento
            </h1>
            <a href="{{ route('welcome') }}" class="btn-voltar" >
                <i class="fas fa-arrow-left mr-1 "></i> Voltar
            </a>
        </div>
        <h2 class="text-xl font-semibold text-gray-800">{{ $experimento['nome'] }}</h2>
    </div>
    
    <!-- Restante do seu código permanece igual -->
    <div class="container">
        <div class="chart-container">
            <canvas id="temperaturaChart"></canvas>
        </div>
    </div>

    <div class="container">
        <div class="experimento-card bg-white rounded-xl shadow-md overflow-hidden p-6">
            <!-- Cabeçalho do Experimento -->
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex space-x-4 mt-2 text-sm text-gray-800">
                            <span><i class="far fa-clock mr-1"></i> <strong>Início:</strong> {{ $experimento['inicio'] }}</span>
                            <span><i class="far fa-clock mr-1"></i> <strong>Fim:</strong> {{ $experimento['fim'] ?? 'Não registrado' }}</span>
                            <span><i class="fas fa-ruler-combined mr-1"></i> <strong>Medições:</strong> {{ count($experimento['dados']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Dados -->
            <div class="table-container border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-650 uppercase tracking-wider">
                                Tempo Decorrido
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-650 uppercase tracking-wider">
                                Temperatura (°C)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-300">
                        @foreach ($experimento['dados'] as $linha)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
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
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('temperaturaChart');
        
        // Verifica se existem dados
        const labels = @json($dadosGrafico['labels']);
        const data = @json($dadosGrafico['temperaturas']);
        
        if (!labels || !data || labels.length === 0 || data.length === 0) {
            console.error('Dados do gráfico vazios:', {labels, data});
            ctx.innerHTML = '<p class="text-danger">Nenhum dado disponível para exibição</p>';
            return;
        }

        // Converte strings para números (caso ainda não estejam)
        const numericData = data.map(Number);
        const numericLabels = labels.map(Number);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: numericLabels,
                datasets: [{
                    label: 'Temperatura (°C)',
                    data: numericData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Temperatura (°C)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tempo Decorrido'
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>