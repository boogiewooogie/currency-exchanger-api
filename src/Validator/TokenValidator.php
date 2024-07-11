<?php

namespace App\Validator;

use App\Exception\ForbiddenException;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;

#[AsRoutingConditionService(alias: 'token_validator')]
readonly class TokenValidator
{
    public function __construct(
        private string $token
    ) {
    }

    /**
     * @return bool
     * @throws ForbiddenException
     */
    public function validate(): bool
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? "";
        preg_match('/Bearer\s(\S+)/', $header, $matches);
        $token = array_pop($matches);
        $token === $this->token ?: throw new ForbiddenException('Invalid token');
        return true;
    }
}