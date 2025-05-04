<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Experimentos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .experimento-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .btn-ver {
            display: inline-block;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn-ver:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div style="margin-bottom: 20px;">
        <a href="/" style="display: inline-block; padding: 8px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
            Voltar para Página Principal
        </a>
    </div>

    <h1>Experimentos Registrados</h1>

    @if (!empty($experimentos))
        @foreach ($experimentos as $experimento)
            <div class="experimento-card">
                <h2>{{ $experimento['nome'] }}</h2>
                <p><strong>Início:</strong> {{ $experimento['inicio'] }}</p>
                <p><strong>Fim:</strong> {{ $experimento['fim'] ?? 'Não registrado' }}</p>
                <p><strong>Medições:</strong> {{ count($experimento['dados']) }}</p>
                <a href="{{ route('experimentos.show', $experimento['id']) }}" class="btn-ver">Ver Gráfico</a>
            </div>
        @endforeach
    @else
        <p>Nenhum experimento encontrado.</p>
    @endif
</body>
</html>