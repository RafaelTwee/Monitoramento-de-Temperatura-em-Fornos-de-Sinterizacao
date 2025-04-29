<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetService;

class FileController
{
    protected $googleSheetService;

    public function __construct()
    {
        $this->googleSheetService = new GoogleSheetService();
    }

    public function extractSheetData()
    {
        //Nome da folha localizado no canto inferior esquedo da folha
        //+ range que deseja percorrer
        $range = "Folha!A2:L";

        //O id da folha fica localizado na url logo após o caminho,
        //https://docs.google.com/spreadsheets/d/spreadsheetId/edit#gid=0
        $spreadsheetId = "hash-file-id";
        
        $values = $this->googleSheetService->getSheetValues($spreadsheetId, $range);
        foreach ($values as $row) {
            foreach ($row as $cellValue) {
                echo $cellValue . "\n";
            }
        }
        return $values;
    }
}