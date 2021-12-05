<?php

namespace Tests\Feature\Services;

use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use App\Services\ApiService;
use Exception;

class ConvertControllerTest extends TestCase
{
    protected int $defaultAmount;
    protected float $defaultRate;
    protected function setUp():void
    {
        parent::setUp();
        $this->defaultAmount = 15;
        $this->defaultRate = 1.5;
    }

    public function test_convert_with_happy_path()
    {
        $this->normalResponseMock();
        $response = $this->callControllerAction('get', '/convert', $this->getDefaultParameter());
        $this->assertEquals($response['result'], 22.5);
    }

    public function test_convert_with_unvalid_request()
    {
        $this->normalResponseMock();
        $parameters = array_merge($this->getDefaultParameter(), [
            'from' => 'TWD'
        ]);
        $response = $this->callControllerAction('get', '/convert', $parameters);
        $this->assertEquals($response['errors']['from'][0], 'The selected from is invalid.');
    }

    public function test_convert_with_third_party_error()
    {
        $errorMessage = 'parameter error';
        $this->errorResponseMock($errorMessage);
        $response = $this->callControllerAction('get', '/convert', $this->getDefaultParameter());
        $this->assertEquals($response['errors']['third_party'], $errorMessage);
    }

    public function test_convert_with_unknown_third_party_error()
    {
        $errorMessage = 'unknown third party server error';
        $this->unknownErrorResponseMock($errorMessage);
        $response = $this->callControllerAction('get', '/convert', $this->getDefaultParameter());
        $this->assertEquals($response['errors']['third_party'], $errorMessage);
    }

    private function normalResponseMock(): void
    {
        $this->mockApiService(function (MockInterface $mock) {
            $mock->shouldReceive('latest')->andReturn([
                'success'   => true,
                'timestamp' => 1000,
                'base'      => 'EUR',
                'date'      => '2021-01-01',
                'rates'     => [
                    'USD' => $this->defaultRate
                ]
            ]);
        });
    }

    private function errorResponseMock($errorMessage): void
    {
        $this->mockApiService(function (MockInterface $mock) use ($errorMessage) {
            $mock->shouldReceive('latest')->andReturn([
                'error' => [
                    'code' => $errorMessage
                ]
            ]);
        });
    }

    private function unknownErrorResponseMock($errorMessage): void
    {
        $this->mockApiService(function (MockInterface $mock) use ($errorMessage) {
            $mock->shouldReceive('latest')->andThrow(new Exception($errorMessage));
        });
    }

    private function mockApiService($closure)
    {
        $mock = Mockery::mock(ApiService::class, $closure)->makePartial();
        $this->instance(ApiService::class, $mock);
    }

    private function getDefaultParameter(): array
    {
        return [
            'from'   => 'EUR',
            'to'     => 'USD',
            'amount' => $this->defaultAmount
        ];
    }

    private function callControllerAction(string $method, string $path, array $parameter): array
    {
        $response = $this->call($method, $path, $parameter);
        return json_decode($response->getContent(), true);
    }
}
