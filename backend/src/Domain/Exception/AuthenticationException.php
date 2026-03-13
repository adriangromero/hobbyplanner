<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class AuthenticationException extends DomainException
{
    public function __construct(string $message = 'Usuario no autenticado')
    {
        parent::__construct($message);
    }
}
