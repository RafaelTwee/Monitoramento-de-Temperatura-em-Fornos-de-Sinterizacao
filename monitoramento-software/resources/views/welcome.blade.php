<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento de Experimentos</title>
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #tabelaExperimentos {
            width: 100% !important;
            border-collapse: collapse;
            border: none !important;
        }

        #tabelaExperimentos thead th {
            padding: 12px 15px;
            text-align: left;
            background-color: #f8fafc;
            position: sticky;
            top: 0;
            border-bottom: none !important;
        }
        #tabelaExperimentos tbody td {
            padding: 12px 15px;
            border-top: 1px solid #e5e7eb !important;
            border-bottom: none !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0.5em;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
        }
        .dataTables_filter label {
            display: flex;
            align-items: center;
        }
        .dataTables_wrapper .dataTables_scrollHeadInner {
            border-bottom: none !important;
        }
        /* Remove bordas extras do container */
        .bg-white.rounded-xl.shadow-md {
            border: none !important;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem; /* Espaço abaixo da caixa de pesquisa */
        }

        .dataTables_wrapper .dataTables_info {
        margin-left: 1rem; /* Afasta do canto esquerdo */
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.5rem !important; /* Alinha com o texto */
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 0.375rem;
            background-color: #e5e7eb;
            color: #374151;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #d1d5db;
        }


    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-flask mr-2"></i> Monitoramento de Experimentos
            </h1>
            <div>
                <button id="limparFiltros" class="flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition mr-4">
                    <i class="fas fa-eraser mr-2"></i> Limpar Filtros
                </button>
                <a href="/planilha" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-table mr-2"></i> Ver Planilha Completa
                </a>
            </div>
        </div>

        @if (!empty($experimentos))
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table id="tabelaExperimentos" class="w-full">
                    <thead>
                        <tr>
                            <th>Experimento</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Medições</th>
                            <th>Temp. Máx (°C)</th>
                            <th>Temp. Média (°C)</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($experimentos as $experimento)
                        @php
                            $temperaturas = array_column($experimento['dados'], 'temperatura');
                            $temperaturas = array_filter($temperaturas, 'is_numeric');
                            $maxTemp = count($temperaturas) > 0 ? max($temperaturas) : '-';
                            $avgTemp = count($temperaturas) > 0 ? number_format(array_sum($temperaturas)/count($temperaturas), 2) : '-';
                        @endphp
                        <tr>
                            <td>{{ $experimento['nome'] }}</td>
                            <td data-order="{{ strtotime($experimento['inicio']) }}">{{ $experimento['inicio'] }}</td>
                            <td data-order="{{ $experimento['fim'] ? strtotime($experimento['fim']) : 0 }}">
                                {{ $experimento['fim'] ?? 'Em andamento' }}
                            </td>
                            <td>{{ count($experimento['dados']) }}</td>
                            <td class="{{ $maxTemp > 30 ? 'text-red-600 font-medium' : 'text-green-600 font-medium' }}">
                                {{ $maxTemp }}
                            </td>
                            <td>{{ $avgTemp }}</td>
                            <td class="text-right">
                                <a href="{{ route('experimentos.show', $experimento['id']) }}" 
                                   class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <i class="fas fa-chart-line mr-1"></i> Gráfico
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md p-8 text-center">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900">Nenhum experimento encontrado</h3>
                <p class="mt-2 text-sm text-gray-500">Não há experimentos registrados na planilha.</p>
            </div>
        @endif
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#tabelaExperimentos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                dom: `
                    <"flex justify-between items-center mb-4"<"flex"lf>B>
                    rt
                    <"flex justify-between items-center mt-4"ip>
                `,
                buttons: [],
                columnDefs: [
                    { orderable: false, targets: 6 },
                    { className: "dt-left", targets: [0,1,2,3,4,5] },
                    { className: "dt-right", targets: 6 }
                ],
                initComplete: function() {
                    this.api().columns().every(function(index) {
                        if (index !== 6) {
                            var column = this;
                            var header = $(column.header());
                            header.append('<div class="mt-2"><input type="text" class="w-full px-2 py-1 border rounded" placeholder="Filtrar..." data-column="' + index + '"/></div>');
                            
                            $('input', header).on('keyup change', function() {
                                if (column.search() !== this.value) {
                                    column.search(this.value).draw();
                                }
                            });
                        }
                    });
                }
            });

            $('#limparFiltros').click(function() {
                table.search('').columns().search('').draw();
                $('input[type="text"]').val('');
            });

            table.order([1, 'desc']).draw();
        });
    </script>
</body>
</html>