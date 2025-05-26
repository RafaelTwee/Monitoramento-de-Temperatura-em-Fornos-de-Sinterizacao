<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico do Experimento</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
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
        .charts-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .reset-zoom {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 100;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .reset-zoom:hover {
            opacity: 1;
        }
        
        .chart-wrapper {
            position: relative;
            margin-bottom: 2rem;
            flex: 1;
            min-width: 300px;
        }

        .reset-zoom {
            margin-top: 0.5rem;
        }

        .experimento-card {
            transition: all 0.3s ease;
        }
        .experimento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        tbody {
            display: block;
            overflow-y: auto;
            max-height: 400px;
        }
        thead, tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .table-container {
            width: 100%;
            max-height: 400px;
            margin-top: 20px;
            overflow-y: auto;
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
            height: 400px;
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
    
    <!-- Container para os dois gráficos -->
    <div class="container">
        <div class="charts-container">
            <div class="chart-wrapper">
                <div class="chart-container">
                    <canvas id="temperaturaChart"></canvas>
                </div>
            </div>
            <div class="chart-wrapper">
                <div class="chart-container">
                    <canvas id="diferencasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-16">
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
            let temperaturaChart;
            let diferencasChart;

            // Registrar o plugin de zoom
            Chart.register(ChartZoom);
        
            // Configurações comuns de zoom
            const zoomOptions = {
                pan: {
                    enabled: true,
                    mode: 'xy',
                    modifierKey: 'shift'
                },
                zoom: {
                    wheel: {
                        enabled: true,
                    },
                    pinch: {
                        enabled: true
                    },
                    mode: 'xy',
                    onZoom: ({chart}) => {
                        // Guarda o estado original do zoom
                        chart.originalScaleLimits = {
                            x: {min: chart.scales.x.min, max: chart.scales.x.max},
                            y: {min: chart.scales.y.min, max: chart.scales.y.max}
                        };
                    }
                }
            };

            // Gráfico de temperatura original
            const ctxTemperatura = document.getElementById('temperaturaChart');
            
            const labels = @json($dadosGrafico['labels']);
            const data = @json($dadosGrafico['temperaturas']);
            
            if (!labels || !data || labels.length === 0 || data.length === 0) {
                console.error('Dados do gráfico vazios:', {labels, data});
                ctxTemperatura.innerHTML = '<p class="text-danger">Nenhum dado disponível para exibição</p>';
                return;
            }

            const numericData = data.map(Number);
            const numericLabels = labels.map(Number);

            temperaturaChart = new Chart(ctxTemperatura, {

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
                    },
                    plugins: {
                        zoom: zoomOptions,
                        legend: {
                            onClick: (e, legendItem, legend) => {
                                // Desativa o comportamento padrão de esconder o dataset
                                return;
                            }
                        }
                    }
                },
                plugins: [ChartZoom]
            });

            // Gráfico de derivadas (dT/dt) vs tempo
            const ctxDiferencas = document.getElementById('diferencasChart');
                        
            // Calcula as derivadas (ΔT/Δt) entre medições consecutivas
            const derivadas = [];
            const labelsDerivada = [];

            for (let i = 0; i < numericLabels.length; i++) {
                if (i === 0) {
                    derivadas.push(0); // Primeiro ponto tem derivada zero
                } else {
                    const deltaT = numericData[i] - numericData[i-1];
                    const deltaTime = numericLabels[i] - numericLabels[i-1];
                    derivadas.push(deltaTime !== 0 ? deltaT / deltaTime : 0);
                }
                labelsDerivada.push(numericLabels[i]);
            }

            // Cria o gráfico de derivadas
            diferencasChart = new Chart(ctxDiferencas, {
                type: 'line',
                data: {
                    labels: labelsDerivada,
                    datasets: [{
                        label: 'Taxa de Variação (dT/dt)',
                        data: derivadas,
                        backgroundColor: function(context) {
                            return context.raw >= 0 
                                ? 'rgba(255, 99, 132, 0.7)' 
                                : 'rgba(54, 162, 235, 0.7)';
                        },
                        borderColor: function(context) {
                            return context.raw >= 0 
                                ? 'rgba(255, 99, 132, 1)' 
                                : 'rgba(54, 162, 235, 1)';
                        },
                        borderWidth: 1,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            title: {
                                display: true,
                                text: 'Taxa de Variação (°C/s)'
                            },
                            beginAtZero: false
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tempo Decorrido (s)'
                            }
                        }
                    },
                    plugins: {
                        zoom: zoomOptions,
                        legend: {
                            onClick: (e, legendItem, legend) => {
                                return;
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Taxa: ${context.parsed.y.toFixed(4)}°C/s`;
                                },
                                afterLabel: function(context) {
                                    const i = context.dataIndex;
                                    const currentTemp = numericData[i].toFixed(2);
                                    const prevTemp = i > 0 ? numericData[i-1].toFixed(2) : null;
                                    
                                    let tooltip = `Temperatura atual: ${currentTemp}°C`;
                                    if (prevTemp !== null) {
                                        tooltip += `\nTemperatura anterior: ${prevTemp}°C`;
                                        tooltip += `\nΔT: ${(numericData[i] - numericData[i-1]).toFixed(2)}°C`;
                                        tooltip += `\nΔt: ${(numericLabels[i] - numericLabels[i-1]).toFixed(2)}s`;
                                    }
                                    return tooltip;
                                }
                            }
                        }
                    }
                },
                plugins: [ChartZoom]
            });

            // Adiciona botão de reset para ambos os gráficos
            function addResetButtons() {
                const resetZoom = (chart) => {
                    if (chart) {
                        chart.resetZoom(); // método do plugin chartjs-plugin-zoom
                    }
                };

                const container1 = document.querySelector('#temperaturaChart').closest('.chart-wrapper');
                const btn1 = document.createElement('button');
                btn1.className = 'reset-zoom bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs';
                btn1.textContent = 'Resetar Zoom';
                btn1.onclick = () => resetZoom(temperaturaChart);
                container1.appendChild(btn1);

                const container2 = document.querySelector('#diferencasChart').closest('.chart-wrapper');
                const btn2 = document.createElement('button');
                btn2.className = 'reset-zoom bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs';
                btn2.textContent = 'Resetar Zoom';
                btn2.onclick = () => resetZoom(diferencasChart);
                container2.appendChild(btn2);
            }

            addResetButtons();
        });
    </script>
</body>
</html>