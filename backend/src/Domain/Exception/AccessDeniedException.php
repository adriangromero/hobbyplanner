<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class AccessDeniedException extends DomainException
{
    public function __construct(string $message = 'No tienes permiso para acceder a este recurso')
    {
        parent::__construct($message);
    }
}
