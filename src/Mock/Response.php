<?php

namespace ItkDev\GetOrganized\Mock;

use GuzzleHttp\Psr7\Response as BaseResponse;

class Response extends BaseResponse
{
    public function __construct(array $spec)
    {
        $response = $spec['response'];

        parent::__construct(
            $response['status'] ?? 200,
            $response['headers'] ?? [],
            $response['body'] ?? null
        );
    }
}
