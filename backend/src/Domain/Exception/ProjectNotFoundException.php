<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ProjectNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Project '{$id}' not found");
    }
}