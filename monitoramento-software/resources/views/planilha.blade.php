<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados da Planilha</title>
</head>
<body>
    <h1>Linhas da Planilha</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Coluna 1</th>
                <th>Coluna 2</th>
                <th>Coluna 3</th>
                <!-- Adicione mais cabeçalhos conforme necessário -->
            </tr>
        </thead>
        <tbody>
            @if (!empty($rows))
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3">Nenhum dado encontrado.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>