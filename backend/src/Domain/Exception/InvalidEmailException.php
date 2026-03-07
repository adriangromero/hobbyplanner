<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidEmailException extends ValidationException
{
    public function __construct(string $email)
    {
        parent::__construct(
            sprintf('El email "%s" no es válido', $email)
        );
    }
}
