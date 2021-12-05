<?php

namespace Tests\Feature;

use App\Services\ApiService;
use Tests\TestCase;

class ApiServiceTest extends TestCase
{
    protected function setUp():void
    {
        parent::setUp();
        $this->apiService = new ApiService;
    }

    public function test_handle_with_api_known_error()
    {
        $this->expectExceptionMessage('something error');
        $this->apiService->handleError([
            'error' =>[
                'code' => 'something error'
            ]
        ]);
    }

    public function test_handle_with_api_unknown_error()
    {
        $this->expectExceptionMessage('Unknown API source error');
        $this->apiService->handleError([]);
    }
}
