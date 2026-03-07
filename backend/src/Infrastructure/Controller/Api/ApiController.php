<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;

abstract class ApiController extends SymfonyAbstractController
{
    public function __construct(
        protected readonly UserRepositoryInterface $userRepository,
    ) {}

    protected function getCurrentUser(): User
    {
        $user = $this->userRepository->findByEmail(
            Email::fromString($this->getUser()->getUserIdentifier())
        );

        if ($user === null) {
            throw new \RuntimeException('Authenticated user not found in database');
        }

        return $user;
    }

    protected function validateRequired(?array $data, array $fields): void
    {
        if ($data === null) {
            throw new ValidationException('El cuerpo de la petición debe ser JSON válido');
        }

        $missing = array_filter($fields, fn($field) => !isset($data[$field]));

        if (!empty($missing)) {
            throw new ValidationException(
                'Faltan campos requeridos: ' . implode(', ', $missing)
            );
        }
    }
}
