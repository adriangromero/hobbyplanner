<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\Port\CurrentUserProvider;
use App\Domain\Exception\ValidationException;
use App\Domain\ValueObject\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class ApiController extends AbstractController
{
    public function __construct(
        private readonly CurrentUserProvider $currentUserProvider,
    ) {}

    protected function currentUserId(): UserId
    {
        return $this->currentUserProvider->currentUserId();
    }

    protected function jsonBody(Request $request, array $requiredFields = []): array
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new ValidationException('El cuerpo de la petición debe ser JSON válido');
        }

        $missing = array_filter($requiredFields, fn(string $field) => !isset($data[$field]));

        if (!empty($missing)) {
            throw new ValidationException(
                'Faltan campos requeridos: ' . implode(', ', $missing)
            );
        }

        return $data;
    }
}
