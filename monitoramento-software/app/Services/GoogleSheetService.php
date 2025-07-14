<?php
  
namespace App\Services;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange as ValueRange;


class GoogleSheetService
{
    protected Google_Service_Sheets $sheetsService;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets');
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');

        $googleCredentials = env('GOOGLE_CREDENTIALS_JSON');

        if ($googleCredentials) {
            $credentials = json_decode($googleCredentials, true);
            $client->setAuthConfig($credentials);
        } else {
            $client->setAuthConfig(base_path('google-credentials.json'));
        }

        $this->sheetsService = new Google_Service_Sheets($client);
    }

    public function getSheetValues($fileId, $range)
    {
        $response = $this->sheetsService->spreadsheets_values->get($fileId, $range);
        return $response->getValues();  
    }

    public function updateSheetValues(string $fileId, string $range, array $values)
    {
        $body = new ValueRange(['values' => $values]);
        return $this->sheetsService
                    ->spreadsheets_values
                    ->update($fileId, $range, $body, [
                        'valueInputOption' => 'USER_ENTERED'
                    ]);
    }

}
