<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento de Experimentos</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" />

    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.11.5/sorting/datetime-moment.js"></script>
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

        /* filtros inline */
        .filter-date { @apply w-32 px-2 py-1 border border-gray-300 rounded placeholder-gray-400; }
        .dataTables_filter input { @apply w-48 px-2 py-1 border border-gray-300 rounded placeholder-gray-400; }

        /* modal com borda uniforme */
        .modal { @apply rounded-2xl !important; }
        /* zebra striping já aplicada, agora ações só aparecem no hover */
        tbody tr .actions { opacity: 0; transition: opacity .2s; }
        tbody tr:hover .actions { opacity: 1; }
        /* td typography */
        .table-container table td { @apply text-gray-700 font-normal; }

        /* 1) ícones de ação sempre visíveis */
        tbody tr .actions {
            opacity: 1 !important;
        }

        /* 2) centraliza todas as células */
        #tabelaExperimentos th,
        #tabelaExperimentos td {
            text-align: center;
        }

        /* 3) mantém a coluna de Ações alinhada à direita */
        #tabelaExperimentos th:last-child,
        #tabelaExperimentos td:last-child {
            text-align: right;
        }
    </style>
</head>
<body class="bg-[#303a51]">
    <!-- cabeçalho + estatísticas rápidas -->
    <header class="w-full bg-[#26557d] py-4 shadow-lg ">
        <div class="container mx-auto px-6 py-4 flex flex-wrap justify-between items-center">
            <h1 class="text-3xl font-semibold text-white">
            <i class="fas fa-flask mr-2"></i> Monitoramento de Experimentos
            </h1>
            <div class="flex space-x-4 text-white font-medium">
                <div class="flex items-center">
                    <i class="fas fa-database mr-1"></i>
                    Total de experimentos: <span class="font-medium ml-1">{{ count($experimentos) }}</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-clock mr-1"></i>
                    Último: <span class="font-medium ml-1">{{ !empty($experimentos) ? $experimentos[count($experimentos) - 1]['inicio'] : '-' }}</span>
                </div>
                <a href="{{ route('users.index') }}"
                    class="px-4 py-2 text-white hover:underline">
                    Usuários
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
        @include('partials.delete_modal')
        @if (!empty($experimentos))
            <!-- action bar fixa -->
            <div class="flex flex-wrap gap-2 items-center mb-4 ">
                <button
                    id="bulkDeleteBtn"
                    class="flex items-center  px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition shadow-lg"
                    title="Excluir em Massa"
                >
                    <i class="fas fa-trash-alt mr-2"></i> Excluir em Massa
                </button>
                <button
                    id="limparFiltros"
                    class="flex items-center  px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition shadow-lg"
                    title="Limpar filtros"
                >
                    <i class="fas fa-eraser mr-2"></i> Limpar filtros
                </button>
            </div>
            <div class="bg-gray-100 rounded-2xl shadow-lg p-6 space-y-4">
               <!-- tabela responsiva com zebra + hover -->
               <div class="overflow-x-auto -mx-6 px-6">
                   <table id="tabelaExperimentos" class="min-w-full divide-y divide-gray-200 table-auto ">
                        <thead class="bg-gray-50">
                            <tr>
                                <th>Experimento</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Medições</th>
                                <th>Temp. Máx (°C)</th>
                                <th>Temp. Média (°C)</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 odd:bg-white even:bg-gray-50 hover:shadow-sm transition">
                            @foreach ($experimentos as $experimento)
                            @php
                                $temperaturas = array_column($experimento['dados'], 'temperatura');
                                $temperaturas = array_filter($temperaturas, 'is_numeric');
                                $maxTemp = count($temperaturas) > 0 ? max($temperaturas) : '-';
                                $avgTemp = count($temperaturas) > 0 ? number_format(array_sum($temperaturas)/count($temperaturas), 2) : '-';
                                
                                // Converter datas para formato ISO para ordenação
                                $inicioISO = $experimento['inicio'] ? date('Y-m-d H:i:s', strtotime(str_replace(['_', '/'], [' ', '-'], $experimento['inicio']))) : '';
                                $fimISO = $experimento['fim'] ? date('Y-m-d H:i:s', strtotime(str_replace(['_', '/'], [' ', '-'], $experimento['fim']))) : '';
                            @endphp
                            <tr class="hover:bg-indigo-100/50 transition-colors duration-150">
                                <td>{{ $experimento['nome'] }}</td>
                                <td data-order="{{ $inicioISO }}">{{ $experimento['inicio'] }}</td>
                                <td data-order="{{ $fimISO }}">
                                    {{ $experimento['fim'] ?? 'Em andamento' }}
                                </td>
                                <td>{{ count($experimento['dados']) }}</td>
                                <td class="{{ $maxTemp > 30 ? 'text-red-600 font-medium' : 'text-green-600 font-medium' }}">
                                    {{ $maxTemp }}
                                </td>
                                <td>{{ $avgTemp }}</td>
                                <td class="text-right actions space-x-2">
                                    <!-- ícones + tooltip -->
                                    <a href="{{ route('experimentos.grafico', $experimento['id']) }}"
                                    class="p-2 rounded-full hover:bg-indigo-100" title="Ver Gráfico">
                                    <i class="fas fa-chart-line text-indigo-600"></i>
                                    </a>
                                    <form action="{{ route('experimentos.destroy', $experimento['id']) }}" method="POST" onsubmit="return confirm('Confirma?');" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 rounded-full hover:bg-red-100" title="Excluir">
                                            <i class="fas fa-trash-alt text-red-600"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                   </table>
               </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.11.5/sorting/datetime-moment.js"></script>

    <script>
        $(document).ready(function() {
            // Registrar o formato de data para ordenação
            $.fn.dataTable.moment('YYYY-MM-DD HH:mm:ss');
            
            var table = $('#tabelaExperimentos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                dom: `
                    <"flex justify-between items-center mb-4 mx-4"
                        <"flex-1"l>
                        <"flex-1 text-right"f>
                    >
                    rt
                    <"flex justify-between items-center mt-4 mx-4"ip>
                `,
                columnDefs: [
                    { 
                        orderable: false, 
                        targets: 6 
                    },
                    { 
                        className: "dt-left", 
                        targets: [0,1,2,3,4,5] 
                    },
                    { 
                        className: "dt-right", 
                        targets: 6 
                    },
                    { 
                        type: 'moment-date',
                        targets: [1, 2],
                        render: function(data, type, row) {
                            // Para exibição, mostra o valor original
                            if (type === 'display') {
                                return data.includes('_') ? 
                                    data.replace('_', ' ') : 
                                    (data || 'Em andamento');
                            }
                            // Para ordenação, usa o valor de data-order (ISO)
                            return data;
                        }
                    }
                ],
                initComplete: function() {
                    // Adicionar filtros individuais para cada coluna (exceto ações)
                    this.api().columns().every(function(index) {
                        if (index !== 6) { // Ignorar coluna de ações
                            var column = this;
                            var header = $(column.header());
                            
                            // Remove qualquer input existente antes de adicionar novo
                            $('input', header).remove();
                            
                            // Adiciona o input de filtro
                            var input = $('<input type="text" class="w-full px-2 py-1 border rounded mt-2" placeholder="Filtrar..." />')
                                .appendTo(header)
                                .on('keyup change', function() {
                                    if (column.search() !== this.value) {
                                        column.search(this.value).draw();
                                    }
                                });
                            
                            // Se for coluna de data, adiciona classe específica
                            if (index === 1 || index === 2) {
                                input.addClass('filter-date');
                            }
                        }
                    });
                }
            });

            $('#limparFiltros').click(function() {
                table.search('').columns().search('').draw();
                $('input[type="text"]').val('');
            });

            // Ordena pela coluna de Início (descendente) por padrão
            table.order([1, 'desc']).draw();
        });
    </script>
    
    @stack('scripts')
</body>
</html>