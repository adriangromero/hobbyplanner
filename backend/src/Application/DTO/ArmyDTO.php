<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Army;

final class ArmyDTO
{
    private function __construct(private string $id, private string $name, private string $createdAt) {}
    public static function fromEntity(Army $army): self
    {
        return new self($army->id()->value(), $army->name(), $army->createdAt()->format(DATE_ATOM));
    }
    public function toArray(): array { return ['id' => $this->id, 'name' => $this->name, 'createdAt' => $this->createdAt]; }
}
