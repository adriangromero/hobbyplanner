<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ActiveSessionExistsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Ya tienes una sesión activa. Finalízala antes de iniciar otra.');
    }
}
