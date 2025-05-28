<?php
  
namespace App\Services;

use Google_Client;
use Google_Service_Sheets;

class GoogleSheetService
{
    protected Google_Service_Sheets $sheetsService;

    public function __construct()
    {
        $client = new Google_Client();

        $googleCredentials = env('GOOGLE_CREDENTIALS_JSON');
        $decodedCredentials = json_decode($googleCredentials, true);
        
        $client->setApplicationName('Google Sheets');
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($decodedCredentials);
        $this->sheetsService = new Google_Service_Sheets($client);
    }

    public function getSheetValues($fileId, $range)
    {
        $response = $this->sheetsService->spreadsheets_values->get($fileId, $range);
        return $response->getValues();  
    }
}