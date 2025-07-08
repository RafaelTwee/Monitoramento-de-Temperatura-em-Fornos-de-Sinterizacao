<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SheetsController;
use Illuminate\Http\Request;
use App\Exports\ExperimentExport;
use Maatwebsite\Excel\Facades\Excel;

class GraficoController
{
    protected $sheetsController;

    public function __construct(SheetsController $sheetsController)
    {
        $this->sheetsController = $sheetsController;
    }

    public function index()
    {
        $experimentos = $this->sheetsController->getExperimentos();
        return view('experimentos.index', compact('experimentos'));
    }

    public function show($id)
    {
        $experimentos = $this->sheetsController->getExperimentos();
        
        // Encontra o experimento pelo ID
        $experimentoEncontrado = null;
        foreach ($experimentos as $experimento) {
            if ($experimento['id'] == $id) {
                $experimentoEncontrado = $experimento;
                break;
            }
        }
    
        if (!$experimentoEncontrado) {
            abort(404, 'Experimento não encontrado');
        }
    
        // Debug: Mostra os dados brutos antes do processamento
        // dd($experimentoEncontrado['dados']);
    
        $dadosGrafico = $this->prepararDadosGrafico($experimentoEncontrado['dados']);
    
        // Debug: Mostra os dados processados para o gráfico
        // dd($dadosGrafico);
    
        return view('experimentos.grafico', [
            'experimento' => $experimentoEncontrado,
            'dadosGrafico' => $dadosGrafico
        ]);
    }

    private function prepararDadosGrafico($dados)
    {
        if (empty($dados)) {
            return [
                'labels' => [],
                'temperaturas' => []
            ];
        }

        // Extrai os valores diretamente
        $labels = array_column($dados, 'tempo');
        $temperaturas = array_column($dados, 'temperatura');

        // Calcula diferenças entre medições consecutivas
        $diferencasTempo = [];
        $diferencasTemperatura = [];
        
        foreach ($labels as $i => $tempo) {
            if ($i === 0) {
                $diferencasTempo[] = 0;
                $diferencasTemperatura[] = 0;
            } else {
                $diferencasTempo[] = $labels[$i] - $labels[$i-1];
                $diferencasTemperatura[] = $temperaturas[$i] - $temperaturas[$i-1];
            }
        }

        return [
            'labels' => $labels,
            'temperaturas' => $temperaturas,
            'diferencas_tempo' => $diferencasTempo,
            'diferencas_temperatura' => $diferencasTemperatura
        ];
    }

    public function downloadExcel($id)
    {
        // Reaproveita a busca do experimento (igual ao show)
        $experimentos = $this->sheetsController->getExperimentos();
        $exp = collect($experimentos)
            ->first(fn($e) => $e['id'] == $id);
        if (!$exp) {
            abort(404);
        }

        // Monta as linhas com derivada (igual ao cálculo em JS)
        $prev = null;
        $rows = [];
        foreach ($exp['dados'] as $linha) {
            $tempo = $linha['tempo'];
            $temp  = $linha['temperatura'];
            if (is_null($prev)) {
                $deriv = 0;
            } else {
                $dT = $temp - $prev['temperatura'];
                $dt = $tempo - $prev['tempo'];
                $deriv = $dt != 0 ? $dT / $dt : 0;
            }
            $rows[] = [
                $tempo,
                $temp,
                round($deriv, 4)
            ];
            $prev = $linha;
        }

        return Excel::download(
            new ExperimentExport($rows),
            "experimento_{$id}.xlsx"
        );
    }
}