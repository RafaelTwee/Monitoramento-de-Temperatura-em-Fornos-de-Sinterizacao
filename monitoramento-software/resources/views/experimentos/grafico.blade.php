{{-- resources/views/experimentos/grafico.blade.php --}}
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
    <!-- noUiSlider CSS e JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.js"></script>

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
            max-height: 750px;
        }
        thead, tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .table-container {
            width: 100%;
            max-height: 800px;
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

        /* Aumento de fonte para cabeçalho e células da tabela */
        .table-container table th,
        .table-container table td {
            font-size: 1rem; /* ajuste para o tamanho desejado */
        }

        /* zebra striping nas linhas da tabela */
        .table-container table tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }
        .table-container table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="flex justify-between items-center mb-4 mt-8">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-chart-line mr-2"></i> Gráfico do Experimento
            </h1>
            <a href="{{ route('welcome') }}" class="btn-voltar">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>

        {{-- ================= Nome editável ================= --}}
        <div class="flex items-center space-x-2">
            <span id="nomeTexto" class="text-xl font-semibold text-gray-800">
                {{ $experimento['nome'] }}
            </span>
            <button id="btnEditar" type="button" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-pencil-alt"></i>
            </button>
        </div>
        <div id="areaEdicao" class="mt-2 hidden">
            <input
                type="text"
                id="inputNome"
                class="border border-gray-300 rounded px-2 py-1 text-gray-800"
                value="{{ $experimento['nome'] }}"
                maxlength="255"
            >
            <button id="btnSalvarNome" class="ml-2 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                Salvar
            </button>
            <button id="btnCancelarEdicao" class="ml-1 px-3 py-1 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                Cancelar
            </button>
            <p id="erroNome" class="text-sm text-red-600 mt-1 hidden"></p>
        </div>
        {{-- ================================================= --}}
    </div>

    {{-- Container para os dois gráficos --}}
    <div class="container">
        <div class="charts-container">
            <div class="chart-wrapper">
                <div class="chart-container">
                    <canvas id="temperaturaChart"></canvas>
                    <!-- slider logo abaixo do eixo x -->
                    <div id="rangeSlider" class="mt-2"></div>
                    <!-- exibe os valores selecionados -->
                    <div id="rangeValues" class="text-sm text-gray-700 mt-1"></div>
                </div>
            </div>
            <div class="chart-wrapper">
                <div class="chart-container">
                    <canvas id="diferencasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="container flex space-x-4 mt-10">
        <!-- 1) Botão para Excel -->
        <a
            href="{{ route('experimentos.downloadExcel', $experimento['id']) }}"
            class="px-4 py-2 mb-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
        >
            📥 Baixar Dados (Excel)
        </a>

        <!-- 2) Botão para PNGs -->
        <button
            id="btnDownloadPNGs"
            class="px-4 py-2 mb-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
        >
            🖼️ Baixar Gráficos (PNG)
        </button>
    </div>

    <div class="container mb-16">
        <div class="experimento-card bg-white rounded-xl shadow-md overflow-hidden p-6">
            <!-- Cabeçalho do Experimento -->
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex space-x-4 mt-2 text-sm text-gray-800">
                            <span>
                                <i class="far fa-clock mr-1"></i>
                                <strong>Início:</strong> {{ $experimento['inicio'] }}
                            </span>
                            <span>
                                <i class="far fa-clock mr-1"></i>
                                <strong>Fim:</strong> {{ $experimento['fim'] ?? 'Não registrado' }}
                            </span>
                            <span>
                                <i class="fas fa-ruler-combined mr-1"></i>
                                <strong>Medições:</strong> {{ count($experimento['dados']) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Dados -->
            <div class="table-container border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-650 uppercase tracking-wider"
                            >
                                Tempo Decorrido
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-650 uppercase tracking-wider"
                            >
                                Temperatura (°C)
                            </th>
                            <!-- Nova coluna de derivada -->
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-650 uppercase tracking-wider"
                            >
                                Derivada (dT/dt)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-300">
                        @php
                            $prev = null;
                        @endphp

                        @foreach ($experimento['dados'] as $linha)
                            @php
                                // Se for a primeira linha, derivada = 0
                                if (is_null($prev)) {
                                    $deriv = 0;
                                } else {
                                    $deltaT    = $linha['temperatura'] - $prev['temperatura'];
                                    $deltaTime = $linha['tempo']       - $prev['tempo'];
                                    $deriv     = $deltaTime != 0
                                                ? $deltaT / $deltaTime
                                                : 0;
                                }
                                $prev = $linha;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                    {{ $linha['tempo'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $linha['temperatura'] > 30 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $linha['temperatura'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $deriv < 0 ? 'text-red-600' : 'text-blue-600' }}">
                                    {{ number_format($deriv, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= SCRIPT de Chart.js + Zoom ================= --}}
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
                    datasets: [{
                        label: 'Temperatura (°C)',
                        // dados como {x: instante, y: temperatura}
                        data: numericLabels.map((t,i) => ({ x: t, y: numericData[i] })),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true,
                        parsing: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'linear',
                            position: 'bottom',
                            title: {
                                display: true,
                                text: 'Tempo Decorrido'
                            }
                        },
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Temperatura (°C)'
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

            const derivadas = [];
            const labelsDerivada = [];

            for (let i = 0; i < numericLabels.length; i++) {
                if (i === 0) {
                    derivadas.push(0);
                } else {
                    const deltaT = numericData[i] - numericData[i-1];
                    const deltaTime = numericLabels[i] - numericLabels[i-1];
                    derivadas.push(deltaTime !== 0 ? deltaT / deltaTime : 0);
                }
                labelsDerivada.push(numericLabels[i]);
            }

            diferencasChart = new Chart(ctxDiferencas, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Taxa de Variação (dT/dt)',
                        data: labelsDerivada.map((t,i) => ({ x: t, y: derivadas[i] })),
                        parsing: false,
                        backgroundColor: function(ctx) {
                            return ctx.raw >= 0 
                                ? 'rgba(255, 99, 132, 0.7)' 
                                : 'rgba(54, 162, 235, 0.7)';
                        },
                        borderColor: function(ctx) {
                            return ctx.raw >= 0 
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
                        x: {
                            type: 'linear',
                            position: 'bottom',
                            title: {
                                display: true,
                                text: 'Tempo Decorrido (s)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Taxa de Variação (°C/s)'
                            },
                            beginAtZero: false
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
                        chart.resetZoom();
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

            // ======== download dos PNGs =========
            document
                .getElementById('btnDownloadPNGs')
                .addEventListener('click', () => {
                    [temperaturaChart, diferencasChart].forEach((chart, idx) => {
                        const a = document.createElement('a');
                        a.href = chart.toBase64Image();
                        a.download = `grafico_${idx+1}.png`;
                        a.click();
                    });
                });

            // prepare dados numéricos para slider
            // cria array de índices correspondentes aos instantes
            const indices = numericLabels.map((_, i) => i);

            // função genérica de filtro
            function filterTable(start, end) {
                document.querySelectorAll('.table-container tbody tr').forEach(row => {
                    const t = Number(row.children[0].textContent);
                    row.style.display = (t >= start && t <= end) ? '' : 'none';
                });
            }

            // Cria slider usando valores reais de tempo
            const slider = document.getElementById('rangeSlider');
            noUiSlider.create(slider, {
                start: [numericLabels[0], numericLabels.at(-1)],
                connect: true,
                range: { min: numericLabels[0], max: numericLabels.at(-1) },
                step: 1,
                tooltips: [true, true]
            });

            const rangeValues = document.getElementById('rangeValues');

            // ao arrastar slider, converte strings→números diretamente
            slider.noUiSlider.on('update', (values) => {
               const start = Number(values[0]);
               const end   = Number(values[1]);

                filterTable(start, end);
                rangeValues.textContent = `Início: ${start} — Fim: ${end}`;

                // limita eixos X dos gráficos
                temperaturaChart.options.scales.x.min = start;
                temperaturaChart.options.scales.x.max = end;
                temperaturaChart.update();
                diferencasChart.options.scales.x.min = start;
                diferencasChart.options.scales.x.max = end;
                diferencasChart.update();
            });

            // filtro inicial
            filterTable(numericLabels[0], numericLabels.at(-1));
        });
    </script>

    {{-- ========= SCRIPT de “in-place edit” ========= --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Referências aos elementos do DOM
        const nomeTexto        = document.getElementById('nomeTexto');
        const btnEditar        = document.getElementById('btnEditar');
        const areaEdicao       = document.getElementById('areaEdicao');
        const inputNome        = document.getElementById('inputNome');
        const btnSalvarNome    = document.getElementById('btnSalvarNome');
        const btnCancelarEdicao= document.getElementById('btnCancelarEdicao');
        const erroNome         = document.getElementById('erroNome');

        // ID do experimento (mesmo que o SheetsController gerou via md5(início+startRow))
        const experimentoId = "{{ $experimento['id'] }}";

        // Ao clicar no ícone de lápis → mostra o campo de edição
        btnEditar.addEventListener('click', () => {
            nomeTexto.classList.add('hidden');
            btnEditar.classList.add('hidden');
            areaEdicao.classList.remove('hidden');
            inputNome.focus();
        });

        // Ao clicar em “Cancelar” → reverte ao estado original
        btnCancelarEdicao.addEventListener('click', () => {
            inputNome.value = nomeTexto.textContent.trim();
            erroNome.classList.add('hidden');
            areaEdicao.classList.add('hidden');
            nomeTexto.classList.remove('hidden');
            btnEditar.classList.remove('hidden');
        });

        // Ao clicar em “Salvar” → dispara o patch via fetch
        btnSalvarNome.addEventListener('click', async () => {
            const novoNome = inputNome.value.trim();
            erroNome.textContent = '';
            erroNome.classList.add('hidden');

            if (novoNome.length === 0) {
                erroNome.textContent = 'O nome não pode ficar vazio.';
                erroNome.classList.remove('hidden');
                return;
            }

            const payload = {
                nome: novoNome,
                _token: '{{ csrf_token() }}'
            };

            try {
                const resposta = await fetch(
                    `{{ url('/experimentos') }}/${experimentoId}/nome`,
                    {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    }
                );

                if (!resposta.ok) {
                    const jsonErro = await resposta.json();
                    throw new Error(jsonErro.message || 'Erro ao atualizar.');
                }

                const dados = await resposta.json();
                // Atualiza o texto e volta para modo readonly
                nomeTexto.textContent = dados.nome;
                areaEdicao.classList.add('hidden');
                nomeTexto.classList.remove('hidden');
                btnEditar.classList.remove('hidden');
            } catch (err) {
                erroNome.textContent = err.message || 'Não foi possível salvar.';
                erroNome.classList.remove('hidden');
            }
        });

        // Enter = Salvar, Escape = Cancelar
        inputNome.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                btnSalvarNome.click();
            }
            if (e.key === 'Escape') {
                btnCancelarEdicao.click();
            }
        });
    });
    </script>
    {{-- =========================================== --}}
</body>
</html>
