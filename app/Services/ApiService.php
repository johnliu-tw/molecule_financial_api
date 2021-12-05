<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiService
{
    private string $latestPath = '/v1/latest';
    public function latest(array $requestData): array
    {
        $response = Http::get(config('exchangeratesapi.api_url').$this->latestPath, [
          'access_key' => config('exchangeratesapi.api_key'),
          'base'       => $requestData['from'],
          'symbols'    => $requestData['to'],
        ]);
        return json_decode($response->body(), true);
    }

    public function handleError(array $responseJsonData)
    {
        if (isset($responseJsonData['error'])) {
            throw new BadRequestHttpException($responseJsonData['error']['code']);
        }

        if (!isset($responseJsonData['success'])) {
            throw new Exception('Unknown API source error');
        }
    }
}
