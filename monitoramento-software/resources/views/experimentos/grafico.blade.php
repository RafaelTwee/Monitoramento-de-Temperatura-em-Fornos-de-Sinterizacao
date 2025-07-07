{{-- resources/views/experimentos/grafico.blade.php --}}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento de Experimentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Tailwind + Font Awesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    {{-- Chart.js + Zoom plugin --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>

    {{-- noUiSlider --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.js"></script>

    <style>
        body { background: #4e6e5d; }
        .chart-container { position: relative; height: 300px; }
        .reset-zoom {
        position: absolute; top: 0.5rem; right: 0.5rem;
        background: #3b82f6; color: white;
        padding: 0.25rem 0.5rem; font-size: 0.75rem;
        border-radius: 0.25rem; cursor: pointer; opacity: .8;
        }
    </style>
</head>
<body class="">

    <header class="w-full bg-[#4da167] py-6 shadow-lg">
        <div class="container mx-auto px-6 flex flex-wrap justify-between items-center">
            <!-- Título -->
            <h1 class="text-3xl font-semibold text-white flex items-center">
            <i class="fas fa-chart-line mr-2"></i>Gráfico do Experimento
            </h1>

            <!-- Estatísticas e botão Voltar juntos -->
            <div class="flex items-center space-x-6">
            <!-- Estatísticas -->
            <div class="flex space-x-4 text-white">
                <div class="flex items-center">
                <i class="fas fa-database mr-1"></i>
                Total: <span class="font-medium ml-1">{{ count($experimento['dados']) }}</span>
                </div>
                <div class="flex items-center">
                <i class="fas fa-clock mr-1"></i>
                Fim: <span class="font-medium ml-1">{{ $experimento['fim'] ?? 'Em andamento' }}</span>
                </div>
            </div>

            <!-- Botão Voltar -->
            <a href="{{ route('welcome') }}"
                class="inline-flex items-center px-4 py-2 text-white rounded hover:bg-green-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-10">
        <div class="flex justify-between items-center mb-6">
            <!-- bloco da esquerda: nome / editar / editar-form -->
            <div class="flex items-center space-x-2">
                <span
                id="nomeTexto"
                class="text-3xl font-semibold text-white px-3 py-1 rounded "
                >{{ $experimento['nome'] }}</span>
                <button id="btnEditar" class="text-white">
                <i class="fas fa-pencil-alt"></i>
                </button>

                <!-- ficou aqui, com flex pra alinhar inline -->
                <div
                id="areaEdicao"
                class="hidden flex items-center space-x-2"
                >
                <input
                    id="inputNome"
                    type="text"
                    class="border border-gray-300 rounded px-2 py-1"
                    value="{{ $experimento['nome'] }}"
                    maxlength="255"
                >
                <button
                    id="btnSalvarNome"
                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                >Salvar</button>
                <button
                    id="btnCancelarEdicao"
                    class="px-3 py-1 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
                >Cancelar</button>

                <p id="erroNome" class="text-red-600 text-sm mt-1 hidden"></p>
                </div>
            </div>

            <!-- botão de baixar, fica sempre do lado direito -->
            <button
                id="btnDownloadPNGs"
                class="px-4 py-2 bg-[#4DA167] text-white rounded hover:bg-green-700 transition shadow-lg"
            >
                <i class="bi bi-download mr-2"></i>
                Baixar Gráficos
            </button>
        </div>

        {{-- Mensagem de erro --}}


        {{-- Gráficos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 ">

            {{-- Temperatura --}}
            <div>
                <div class="chart-container bg-white rounded-lg shadow p-4 h-[400px]">
                    <canvas id="temperaturaChart"></canvas>
                    <button id="resetTemp" class="reset-zoom text-xl">Resetar</button>
                </div>
                
            </div>
                {{-- Derivada --}}
            <div>    
                <div class="chart-container bg-white rounded-lg shadow p-4 h-[400px]" >
                    <canvas id="diferencasChart"></canvas>
                    <button id="resetDeriv" class="reset-zoom text-xl">Resetar</button>
                </div>
                
            </div>
            
        </div>
        <div id="rangeSlider" class="mt-4"></div>
        <div id="rangeValues" class="mt-2 text-xl text-gray-200 text-sm"></div>

        {{-- Tabela de dados --}}
        <div class="bg-white  shadow-md p-6 mt-6 overflow-y-auto rounded-lg">

            <!-- Botões de download -->
            <div class="flex justify-between items-center mb-4">

                <h2 class="text-xl font-bold mb-4">Dados do Experimento</h2>
                <!-- Excel -->
                
                <a
                href="{{ route('experimentos.downloadExcel', $experimento['id']) }}"
                class="px-4 py-2 bg-[#4DA167] text-white rounded hover:bg-green-700 transition mb-4 shadow-lg"
                >
                <i class="bi bi-download px-1"></i>
                Exportar Dados
                </a>
                
            </div>

            <div class="bg-white rounded-lg shadow-md mt-6 overflow-hidden">
                <!-- container externo para manter os cantos arredondados -->
                <div class="overflow-y-auto max-h-[400px]">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                        <th class="px-4 py-2 text-center text-xl text-sm font-medium text-gray-700">Tempo (s)</th>
                        <th class="px-4 py-2 text-center text-xl text-sm font-medium text-gray-700">Temperatura (°C)</th>
                        <th class="px-4 py-2 text-center text-xl text-sm font-medium text-gray-700">Derivada (dT/dt)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $prev = null; @endphp
                        @foreach($experimento['dados'] as $linha)
                        @php
                            if (is_null($prev)) {
                            $deriv = 0;
                            } else {
                            $dT    = $linha['temperatura'] - $prev['temperatura'];
                            $dt    = $linha['tempo']       - $prev['tempo'];
                            $deriv = $dt ? $dT/$dt : 0;
                            }
                            $prev = $linha;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center text-xl text-sm">{{ $linha['tempo'] ?? '–' }}</td>
                            <td class="px-4 py-2 text-center text-xl text-sm font-medium {{ $linha['temperatura'] > 500 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $linha['temperatura'] ?? '–' }}
                            </td>
                            <td class="px-4 py-2 text-center text-xl text-sm font-medium {{ $deriv < 0 ? 'text-red-600' : 'text-blue-600' }}">
                            {{ number_format($deriv, 2, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

  {{-- Scripts --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // In-place edit
      const nomeTexto = document.getElementById('nomeTexto'),
            btnEditar = document.getElementById('btnEditar'),
            areaEd = document.getElementById('areaEdicao'),
            inp = document.getElementById('inputNome'),
            btnSalvar = document.getElementById('btnSalvarNome'),
            btnCanc = document.getElementById('btnCancelarEdicao'),
            err = document.getElementById('erroNome'),
            expId = "{{ $experimento['id'] }}";

      btnEditar.onclick = () => {
        nomeTexto.classList.add('hidden');
        btnEditar.classList.add('hidden');
        areaEd.classList.remove('hidden');
        inp.focus();
      };
      btnCanc.onclick = () => {
        inp.value = nomeTexto.textContent.trim();
        err.classList.add('hidden');
        areaEd.classList.add('hidden');
        nomeTexto.classList.remove('hidden');
        btnEditar.classList.remove('hidden');
      };
      btnSalvar.onclick = async () => {
        const novo = inp.value.trim();
        if (!novo) {
          err.textContent = 'O nome não pode ficar vazio.';
          err.classList.remove('hidden');
          return;
        }
        try {
          const res = await fetch(`{{ url('/experimentos') }}/${expId}/nome`, {
            method: 'PATCH',
            headers: {'Content-Type':'application/json','Accept':'application/json'},
            body: JSON.stringify({nome:novo,_token:'{{ csrf_token() }}'})
          });
          if (!res.ok) throw await res.json();
          const json = await res.json();
          nomeTexto.textContent = json.nome;
          areaEd.classList.add('hidden');
          nomeTexto.classList.remove('hidden');
          btnEditar.classList.remove('hidden');
        } catch(e) {
          err.textContent = e.message || 'Erro ao salvar.';
          err.classList.remove('hidden');
        }
      };
      inp.addEventListener('keyup', e => {
        if (e.key === 'Enter') btnSalvar.click();
        if (e.key === 'Escape') btnCanc.click();
      });

      // Chart.js + Zoom
      Chart.register(ChartZoom);
      const zoomOptions = {
        pan: {enabled:true, mode:'xy', modifierKey:'shift'},
        zoom:{wheel:{enabled:true}, pinch:{enabled:true}, mode:'xy'}
      };
      const commonOpts = {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{zoom:zoomOptions, legend:{onClick:()=>{}}}
      };

      const labels = @json($dadosGrafico['labels']).map(Number),
            dataT = @json($dadosGrafico['temperaturas']).map(Number);

      // Temperatura Chart
      const tempChart = new Chart(document.getElementById('temperaturaChart'), {
        type:'line',
        data:{datasets:[{
          label:'Temperatura (°C)',
          data: labels.map((t,i)=>({x:t,y:dataT[i]})),
          parsing:false, borderWidth:2, tension:0.1, fill:true,
          borderColor:'rgba(75,192,192,1)', backgroundColor:'rgba(75,192,192,0.2)'
        }]},
        options:{
          ...commonOpts,
          scales:{x:{type:'linear',title:{display:true,text:'Tempo (s)'}}, y:{title:{display:true,text:'Temp (°C)'}}}
        }
      });

      // Derivada Chart
      const deriv = labels.map((_,i)=> i===0?0:((dataT[i]-dataT[i-1])/(labels[i]-labels[i-1])));
      const derivChart = new Chart(document.getElementById('diferencasChart'), {
        type:'line',
        data:{datasets:[{
          label:'dT/dt',
          data: labels.map((t,i)=>({x:t,y:deriv[i]})),
          parsing:false, borderWidth:1, pointRadius:3, pointHoverRadius:5,
          borderColor:ctx=>ctx.raw>=0?'rgba(16,185,129,1)':'rgba(239,68,68,1)',
          backgroundColor:ctx=>ctx.raw>=0?'rgba(16,185,129,0.6)':'rgba(239,68,68,0.6)'
        }]},
        options:{
          ...commonOpts,
          scales:{x:{type:'linear',title:{display:true,text:'Tempo (s)'}}, y:{title:{display:true,text:'Taxa (°C/s)'}}}
        }
      });

      // Reset Zoom
      document.getElementById('resetTemp').onclick  = ()=> tempChart.resetZoom();
      document.getElementById('resetDeriv').onclick = ()=> derivChart.resetZoom();

      // Download PNGs
      document.getElementById('btnDownloadPNGs').onclick = () => {
        [tempChart, derivChart].forEach((ch, idx) => {
          const link = document.createElement('a');
          link.href = ch.toBase64Image();
          link.download = `grafico_${idx+1}.png`;
          link.click();
        });
      };

      // Slider
      const slider = document.getElementById('rangeSlider'),
            rangeVal = document.getElementById('rangeValues');
      noUiSlider.create(slider, {
        start: [labels[0], labels.at(-1)],
        connect: true,
        range: {min:labels[0], max:labels.at(-1)},
        step: 1,
        tooltips: [true, true]
      });
      slider.noUiSlider.on('update', ([s,e])=>{
        const start=+s, end=+e;
        rangeVal.textContent = `De ${start} a ${end}`;
        document.querySelectorAll('tbody tr').forEach(r=>{
          const t = +r.children[0].textContent;
          r.style.display = (t>=start && t<=end) ? '' : 'none';
        });
        [tempChart, derivChart].forEach(c=>{
          c.options.scales.x.min=start;
          c.options.scales.x.max=end;
          c.update();
        });
      });
    });
  </script>
</body>
</html>
