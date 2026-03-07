<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Auth\Login\LoginRequest;
use App\Application\UseCase\Auth\Login\LoginUseCase;
use App\Application\UseCase\User\CreateUser\CreateUserRequest;
use App\Application\UseCase\User\CreateUser\CreateUserUseCase;
use App\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth')]
final class AuthController
{
    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly LoginUseCase      $loginUseCase,
    ) {}

    #[Route('/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->validateFields($data, ['email', 'password', 'name']);

        $response = $this->createUserUseCase->execute(
            new CreateUserRequest(
                $data['email'],
                $data['password'],
                $data['name']
            )
        );

        return new JsonResponse([
            'id'        => $response->id,
            'email'     => $response->email,
            'name'      => $response->name,
            'createdAt' => $response->createdAt,
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->validateFields($data, ['email', 'password']);

        $response = $this->loginUseCase->execute(
            new LoginRequest(
                $data['email'],
                $data['password']
            )
        );

        return new JsonResponse([
            'token' => $response->token,
            'user'  => [
                'id'    => $response->id,
                'email' => $response->email,
                'name'  => $response->name,
            ],
        ]);
    }

    private function validateFields(?array $data, array $fields): void
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
