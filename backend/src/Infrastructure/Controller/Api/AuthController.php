<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\Auth\Login\LoginRequest;
use App\Application\UseCase\Auth\Login\LoginUseCase;
use App\Application\UseCase\User\CreateUser\CreateUserRequest;
use App\Application\UseCase\User\CreateUser\CreateUserUseCase;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Exception\InvalidEmailException;
use App\Domain\Exception\UserAlreadyExistsException;
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

        if (!isset($data['email'], $data['password'], $data['name'])) {
            return new JsonResponse(
                ['error' => 'Faltan campos requeridos: email, password, name'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
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

        } catch (InvalidEmailException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (UserAlreadyExistsException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        }
    }

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(
                ['error' => 'Faltan campos requeridos: email, password'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
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

        } catch (InvalidCredentialsException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}