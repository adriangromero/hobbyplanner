<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\ActiveSessionExistsException;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\UserAlreadyExistsException;
use App\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApiExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request   = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if (!$exception instanceof DomainException) {
            return;
        }

        $statusCode = $this->resolveStatusCode($exception);

        $event->setResponse(new JsonResponse(
            ['error' => $exception->getMessage()],
            $statusCode,
        ));
    }

    private function resolveStatusCode(DomainException $exception): int
    {
        return match (true) {
            $exception instanceof AuthenticationException        => Response::HTTP_UNAUTHORIZED,
            $exception instanceof AccessDeniedException         => Response::HTTP_FORBIDDEN,
            $exception instanceof NotFoundException            => Response::HTTP_NOT_FOUND,
            $exception instanceof InvalidCredentialsException   => Response::HTTP_UNAUTHORIZED,
            $exception instanceof UserAlreadyExistsException    => Response::HTTP_CONFLICT,
            $exception instanceof ActiveSessionExistsException  => Response::HTTP_CONFLICT,
            $exception instanceof ValidationException           => Response::HTTP_UNPROCESSABLE_ENTITY,
            default                                           => Response::HTTP_BAD_REQUEST,
        };
    }
}
