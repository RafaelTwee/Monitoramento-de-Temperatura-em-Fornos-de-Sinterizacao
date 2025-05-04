<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SheetsController;
use Illuminate\Http\Request;

class GraficoController extends Controller
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
    
        return view('experimentos.show', [
            'experimento' => $experimentoEncontrado,
            'dadosGrafico' => $dadosGrafico
        ]);
    }

    private function prepararDadosGrafico($dados)
    {
    // Verifica se existem dados
        if (empty($dados)) {
            return [
                'labels' => [],
                'temperaturas' => []
            ];
        }

        // Extrai os valores diretamente
        return [
            'labels' => array_column($dados, 'tempo'),
            'temperaturas' => array_column($dados, 'temperatura')
        ];
    }
}