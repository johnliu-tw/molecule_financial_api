<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Http\Requests\ConvertRequest;
use App\Services\ApiService;

class ConvertController extends Controller
{
    protected ApiService $apiService;
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    public function convert(ConvertRequest $request)
    {
        $requestData = $request->all();
        try {
            $responseJsonData = $this->apiService->latest($requestData);
            $this->apiService->handleError($responseJsonData);
        } catch (BadRequestHttpException $th) {
            return response($this->generateErrorStructure($th->getMessage()), 400);
        } catch (\Throwable $th) {
            return response($this->generateErrorStructure($th->getMessage()), 500);
        }

        $resultArray =$this->generateResultArray($requestData, $responseJsonData);
        $response = $this->generateResponseStructure($requestData, $resultArray);

        return response($response);
    }

    private function generateResultArray(array $requestData, array $responseJsonData): array
    {
        $rate = $responseJsonData['rates'][$requestData['to']] ?? 0;
        $result = $rate * (int)$requestData['amount'];
        return [
            'timestamp' => $responseJsonData['timestamp'],
            'date'      => $responseJsonData['date'],
            'rate'      => $rate,
            'result'    => $result
        ];
    }

    private function generateResponseStructure(array $requestData, array $apiObject): array
    {
        return [
            "success" => true,
            "query" => $requestData,
            "info" => [
                "timestamp" => $apiObject['timestamp'],
                "rate" => $apiObject['rate'],
            ],
            "date" => $apiObject['date'],
            "result" => $apiObject['result'],
        ];
    }

    private function generateErrorStructure(string $message): array
    {
        return [
            'errors' => [
                'third_party' => $message
            ]
        ];
    }
}
