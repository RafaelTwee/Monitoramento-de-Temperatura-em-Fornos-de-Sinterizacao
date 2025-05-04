<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-md w-full text-center">
        <h1 class="text-2xl font-bold text-gray-700 mb-4">Bem-vindo ao Sistema de Monitoramento</h1>
        <p class="text-gray-600 mb-6">Escolha uma das opções abaixo para continuar:</p>
        <div class="flex flex-col gap-4">
            <a href="/planilha" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">
                Ver Planilha
            </a>
            <a href="{{ route('experimentos.index') }}" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition">
                Ver Índice de Experimentos
            </a>
        </div>
    </div>
</body>
</html>