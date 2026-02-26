<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class WorkSessionNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("WorkSession '{$id}' not found");
    }
}