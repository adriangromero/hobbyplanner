<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Email;

/**
 * Interfaz del repositorio de usuarios.
 * 
 * ESTO ES UN PUERTO (hexagonal).
 * 
 * El Domain define QUÉ necesita, no CÓMO se hace.
 * La implementación (Doctrine, MySQL, API...) va en Infrastructure.
 * 
 * SOLID:
 * - Interface Segregation: Solo métodos necesarios para User
 * - Dependency Inversion: El dominio depende de abstracciones, no de Doctrine
 */
interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    public function delete(User $user): void;

    public function emailExists(Email $email): bool;
}

/*

**¿Por qué interfaz y no clase directa?**
```
❌ MAL (acoplado):
UseCase → DoctrineUserRepository → MySQL

✅ BIEN (desacoplado):
UseCase → UserRepositoryInterface ← DoctrineUserRepository
```

El UseCase solo conoce la interfaz. Le da igual si detrás hay MySQL, PostgreSQL, MongoDB o un archivo JSON.

---

**Esto es Dependency Inversion (la D de SOLID):**
- Módulos de alto nivel (UseCase) no dependen de módulos de bajo nivel (Doctrine)
- Ambos dependen de abstracciones (la interfaz)

---

Con esto **el Domain está completo**:
```
src/Domain/
├── Entity/
│   └── User.php ✅
├── Repository/
│   └── UserRepositoryInterface.php ✅
├── ValueObject/
│   ├── UserId.php ✅
│   └── Email.php ✅
└── Exception/
    └── InvalidEmailException.php ✅

    */