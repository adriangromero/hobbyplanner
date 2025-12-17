<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use InvalidArgumentException;

/**
 * Excepción de dominio para emails inválidos.
 * 
 * SOLID:
 * - Single Responsibility: Solo representa este error específico
 * 
 * ¿Por qué excepción propia?
 * - Puedes capturarla específicamente: catch (InvalidEmailException $e)
 * - El mensaje es claro y consistente
 * - Pertenece al Domain, no depende de nada externo
 */
final class InvalidEmailException extends InvalidArgumentException
{
    public function __construct(string $email)
    {
        parent::__construct(
            sprintf('El email "%s" no es válido.', $email)
        );
    }
}