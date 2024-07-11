<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class Response extends JsonResponse
{
    public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        $data = $json ? json_decode($data) : $data;
        $wrapped = [
            'status' => StatusThesaurus::Success,
            'code' => $status,
            'data' => $data
        ];
        parent::__construct($wrapped, $status, $headers);
    }
}