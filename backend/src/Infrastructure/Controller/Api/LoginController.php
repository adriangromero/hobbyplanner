<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\UseCase\User\LoginUser\LoginUserRequest;
use App\Application\UseCase\User\LoginUser\LoginUserUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/login')]
final class LoginController extends AbstractController
{
    public function __construct(
        private LoginUserUseCase $loginUserUseCase
    ) {}

    #[Route('', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(
                ['error' => 'Email y password son requeridos'],
                400
            );
        }
        
        try {
            $response = $this->loginUserUseCase->execute(
                new LoginUserRequest(
                    $data['email'],
                    $data['password']
                )
            );

            return new JsonResponse([
                'token' => $response->token,
                'user' => [
                    'id' => $response->id,
                    'email' => $response->email,
                    'name' => $response->name
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Credenciales inválidas'],
                401
            );
        }
    }
}
