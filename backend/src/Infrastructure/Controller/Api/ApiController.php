<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    protected function validateRequired(array $data, array $fields): ?JsonResponse
    {
        $missing = array_filter($fields, fn($field) => !isset($data[$field]));

        if (!empty($missing)) {
            return new JsonResponse(
                ['error' => 'Faltan campos requeridos: ' . implode(', ', $missing)],
                Response::HTTP_BAD_REQUEST
            );
        }

        return null;
    }
}