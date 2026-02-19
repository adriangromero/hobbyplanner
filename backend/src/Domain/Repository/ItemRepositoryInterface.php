<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Item;
use App\Domain\ValueObject\ItemId;
use App\Domain\Entity\Project;
use App\Domain\ValueObject\ProjectId;

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

interface ItemRepositoryInterface
{
    public function save(Item $item): void;
    public function findById(ItemId $id): ?Item;
    public function findByProject(ProjectId $projectId): array;

    // ⭐ Agregaciones (SOURCE OF TRUTH)
    public function getTotalEstimatedHoursByProject(ProjectId $projectId): float;
    public function countByProject(ProjectId $projectId): int;
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