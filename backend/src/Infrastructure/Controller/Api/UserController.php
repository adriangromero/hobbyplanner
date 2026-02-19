<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\User\CreateUser\CreateUserRequest;
use App\Application\UseCase\User\CreateUser\CreateUserUseCase;
use App\Domain\Exception\InvalidEmailException;
use App\Domain\Exception\UserAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private CreateUserUseCase $createUserUseCase
    ) {}

    #[Route('', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar que vengan los campos
        if (!isset($data['email'], $data['password'], $data['name'])) {
            return new JsonResponse([
                'error' => 'Faltan campos requeridos: email, password, name'
            ], Response::HTTP_BAD_REQUEST);
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
                'id' => $response->id,
                'email' => $response->email,
                'name' => $response->name,
                'createdAt' => $response->createdAt
            ], Response::HTTP_CREATED);

        } catch (InvalidEmailException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (UserAlreadyExistsException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }
}