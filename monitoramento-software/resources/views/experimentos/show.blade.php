<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico do Experimento</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .btn-voltar { 
            display: inline-block;
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn-voltar:hover { background-color: #d32f2f; }
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
        <a href="{{ route('experimentos.index') }}" class="btn-voltar">Voltar</a>
        <h1>{{ $experimento['nome'] }}</h1>
        <p><strong>Período:</strong> {{ $experimento['inicio'] }} 
            @if($experimento['fim'])
                até {{ $experimento['fim'] }}
            @else
                (em andamento)
            @endif
        </p>
        
        <div class="chart-container">
            <canvas id="temperaturaChart"></canvas>
        </div>
    </div>

    <!-- SEU SCRIPT DO GRÁFICO AQUI (mantenha o conteúdo atual) -->
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