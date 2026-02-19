<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\ValueObject\Email;
use DomainException;

final class UserAlreadyExistsException extends DomainException
{
    public function __construct(Email $email)
    {
        parent::__construct(
            sprintf('El usuario con email "%s" ya existe.', $email->value())
        );
    }
}