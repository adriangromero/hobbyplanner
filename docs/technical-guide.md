# HobbyPlanner — Guia Tecnica

## 1. Arquitectura General

HobbyPlanner sigue una arquitectura **DDD (Domain-Driven Design)** con patron **hexagonal (Ports & Adapters)**, aplicando principios **SOLID** y **Clean Code**.

### Principios SOLID aplicados

| Principio | Aplicacion en el proyecto |
|-----------|--------------------------|
| **S** — Single Responsibility | Cada Use Case tiene una unica razon de cambio. Los controllers solo traducen HTTP <-> Use Case. |
| **O** — Open/Closed | Nuevas excepciones extienden `DomainException` sin modificar `ApiExceptionListener`. Nuevos estados de item/proyecto se añaden al enum sin tocar logica existente. |
| **L** — Liskov Substitution | `DoctrineProjectRepository` es intercambiable por un `InMemoryProjectRepository` en tests sin cambiar ningun Use Case. |
| **I** — Interface Segregation | Cada repositorio expone solo los metodos que su agregado necesita. No hay una interfaz "god repository". |
| **D** — Dependency Inversion | Los Use Cases dependen de `ProjectRepositoryInterface` (abstraccion del dominio), no de `DoctrineProjectRepository` (detalle de infraestructura). |

### Capas del backend

```
┌─────────────────────────────────────────────────┐
│                 Infrastructure                   │
│  Controllers, Doctrine Repos, Types, Listeners   │
├─────────────────────────────────────────────────┤
│                  Application                     │
│         Use Cases (Request -> Response)           │
│                    DTOs                           │
├─────────────────────────────────────────────────┤
│                    Domain                        │
│  Entities, Value Objects, Enums, Repo Interfaces │
│         Exceptions, Domain Services              │
└─────────────────────────────────────────────────┘
```

**Regla de dependencia**: las capas internas NUNCA dependen de las externas.
- Domain no importa nada de Application ni Infrastructure
- Application solo importa Domain
- Infrastructure importa todo (implementa los ports del dominio)

### Frontend

```
frontend/src/
  api/          <- Capa de servicios API (axios.ts + projectApi, itemApi, sessionApi, inventoryApi, authApi)
  auth/         <- TokenIntrospector (decode JWT, detectar expiracion client-side)
  types/        <- Tipos centralizados (models.ts: Project, Item, Session, Estimation, InventoryItem)
  components/   <- Componentes Vue organizados por feature
  composables/  <- Logica reutilizable (useBlockingAction, useToast)
  layout/       <- AppLayout, NavBar
  stores/       <- Pinia stores (auth, project, timer)
  utils/        <- Funciones puras (formatDate, formatHours)
  views/        <- Paginas (Login, Projects, ProjectDetail, Inventory)
  router/       <- Vue Router con rutas protegidas
```

**Patrón hexagonal en frontend**: los componentes y stores NUNCA importan `axios` directamente — usan los servicios API tipados (`projectApi`, `itemApi`, etc.). El unico archivo que importa `axios` es `api/axios.ts`.

### Diagrama de flujo de una peticion completa

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              FRONTEND (Vue 3)                               │
│                                                                             │
│  1. Usuario pulsa boton                                                     │
│  2. Componente llama al Store (Pinia)                                       │
│  3. Store llama a Axios                                                     │
│  4. Axios interceptor añade JWT en Authorization header                     │
│  5. Axios envia HTTP request al backend                                     │
└────────────────────────────────────┬────────────────────────────────────────┘
                                     │ HTTP Request + Bearer Token
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         INFRASTRUCTURE LAYER                                │
│                                                                             │
│  ┌──────────────────┐    ┌────────────────────┐                             │
│  │ Symfony Security  │    │ Controller         │                             │
│  │                  │    │                    │                             │
│  │ 1. Valida JWT    │───→│ 1. Extrae datos    │                             │
│  │ 2. Carga User    │    │    del Request     │                             │
│  │ 3. Verifica ROLE │    │ 2. getCurrentUser()│                             │
│  └──────────────────┘    │ 3. Crea XXXRequest │                             │
│                          │ 4. Llama UseCase   │                             │
│                          │ 5. DTO → JSON      │                             │
│                          └─────────┬──────────┘                             │
└────────────────────────────────────┼────────────────────────────────────────┘
                                     │ XXXRequest (objeto tipado)
                                     ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          APPLICATION LAYER                                  │
│                                                                             │
│  ┌──────────────────────────────────────────────┐                           │
│  │ UseCase                                       │                          │
│  │                                               │                          │
│  │ 1. Busca entidad en Repository (interfaz)     │                          │
│  │ 2. OwnershipGuard.ensureOwnership()           │                          │
│  │    └→ CurrentUserProvider.currentUserId()     │                          │
│  │       (Port → obtiene userId del JWT)         │                          │
│  │ 3. Ejecuta logica de negocio                  │                          │
│  │ 4. Llama Repository.save/delete (interfaz)    │                          │
│  │ 5. Devuelve XXXResponse con DTO               │                          │
│  └──────────────────────────────────────────────┘                           │
│                                                                             │
└────────────────────────────┬───────────┬────────────────────────────────────┘
                             │           │
              Usa interfaces │           │ Usa Value Objects
                             ▼           ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                            DOMAIN LAYER                                     │
│                                                                             │
│  Entities: User, Project, Item, WorkSession                                 │
│  Value Objects: ProjectId, ItemId, UserId, Email, ItemStatus, ProjectStatus │
│  Repository Interfaces: ProjectRepositoryInterface, etc.                    │
│  Exceptions: NotFoundException, AccessDeniedException, etc.                 │
│  Services: ProjectEstimator                                                 │
│  Security: OwnableResource (interfaz)                                       │
│                                                                             │
│  ★ NO depende de NADA externo. Ni Symfony, ni Doctrine, ni HTTP.            │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Ejemplo concreto: Crear un Item

```
Vue: CreateItemModal.handleCreate()
  │
  ▼
Pinia: projectStore.addItem(name, projectId, hours)
  │
  ▼
Axios: POST /api/items {name, projectId, estimatedHours}
  │  + Header: Authorization: Bearer eyJhbGciOi...
  │
  ▼
Symfony Security: valida JWT → OK, es ROLE_USER
  │
  ▼
ItemController.create(Request $request)
  │  $data = $this->jsonBody($request, ['name', 'projectId', 'estimatedHours'])
  │  $userId = $this->currentUserId()
  │
  ▼
CreateItemUseCase.execute(CreateItemRequest)
  │  → Crea Item entity con Value Objects (ItemId, ProjectId, UserId)
  │  → itemRepository->save($item)     ← interfaz Domain
  │  → return CreateItemResponse(ItemDTO::fromEntity($item))
  │
  ▼
DoctrineItemRepository.save($item)   ← implementacion Infrastructure
  │  → $this->getEntityManager()->persist($item)
  │  → $this->getEntityManager()->flush()
  │
  ▼
MySQL: INSERT INTO items (id, project_id, user_id, name, estimated_hours, status, ...)
  │
  ▼
Controller: return new JsonResponse({id, name, status, ...}, 201)
  │
  ▼
Axios: response.data → projectStore actualiza estado local
  │
  ▼
Vue: reactivamente muestra el nuevo item en la tabla
```

---

## 2. Domain Layer

### 2.1 Entidades

| Entidad       | Responsabilidad                                        |
|---------------|--------------------------------------------------------|
| `User`        | Usuario con email, password, roles. 100% dominio puro — sin dependencias de Symfony |
| `Project`     | Proyecto de hobby con nombre, descripcion y estado (active/completed) |
| `Item`        | Item dentro de un proyecto con horas estimadas y estado (pending/completed) |
| `WorkSession` | Sesion de trabajo con inicio/fin, calcula duracion     |

Cada entidad:
- Valida sus invariantes en el constructor y metodos de mutacion
- Lanza `ValidationException` si los datos son invalidos
- Gestiona sus propios timestamps (`createdAt`, `updatedAt`)
- Expone metodos semanticos de dominio (`markAsCompleted()`, `markAsPending()`, `finish()`)

### 2.2 Value Objects

| Value Object        | Proposito                              |
|---------------------|----------------------------------------|
| `UserId`            | UUID v4 tipado para usuarios           |
| `ProjectId`         | UUID v4 tipado para proyectos          |
| `ItemId`            | UUID v4 tipado para items              |
| `WorkSessionId`     | UUID v4 tipado para sesiones           |
| `Email`             | Email validado con `filter_var`. Constructor privado, factory `fromString()` |
| `ItemStatus`        | Enum: PENDING, IN_PROGRESS, COMPLETED  |
| `ProjectStatus`     | Enum: ACTIVE, COMPLETED                |
| `ProjectEstimation` | VO inmutable con datos de estimacion. Constructor privado, factory `create()` con validacion de rangos |

Todos los IDs:
- Se crean con `::create()` (genera UUID v4)
- Se reconstruyen con `::fromString()` (valida formato UUID)
- Tienen `equals()` para comparacion por valor
- Implementan `__toString()`

### 2.3 Enums como Value Objects (no tablas SQL)

Los estados de item y proyecto son **PHP 8.2 backed enums**, no tablas en base de datos:

```php
enum ItemStatus: string
{
    case PENDING   = 'pending';
    case COMPLETED = 'completed';

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}
```

**Por que enums y no tablas?**
En DDD, un conjunto fijo de estados que no necesita CRUD ni metadata adicional (nombre traducido, icono, orden) se modela como un Value Object. Los enums de PHP 8.2 son perfectos: inmutables, tipados, con `from()` y `tryFrom()` nativos, y se almacenan como string en la BD. Si mañana se necesita metadata (ej: traduccion, color), se puede migrar a una tabla sin cambiar el dominio — el enum seguiria siendo la "fuente de verdad" de los valores permitidos.

### 2.4 Repository Interfaces (Ports)

```php
interface ProjectRepositoryInterface
{
    public function save(Project $project): void;
    public function findById(ProjectId $id): ?Project;
    public function findByUser(UserId $userId): array;
    public function delete(Project $project): void;
}

interface ItemRepositoryInterface
{
    public function save(Item $item): void;
    public function findById(ItemId $id): ?Item;
    public function findByProject(ProjectId $projectId): array;
    public function findByUser(UserId $userId): array;
    public function getTotalEstimatedHoursByProject(ProjectId $projectId): float;
    public function countByProject(ProjectId $projectId): int;
    public function delete(Item $item): void;
    public function deleteByProject(ProjectId $projectId): void;
}

interface WorkSessionRepositoryInterface
{
    public function save(WorkSession $session): void;
    public function findById(WorkSessionId $id): ?WorkSession;
    public function findByItem(ItemId $itemId): array;
    public function findByProject(ProjectId $projectId): array;
    public function findGroupedByItem(ProjectId $projectId): array;
    public function findActiveByUser(UserId $userId): ?WorkSession;
    public function delete(WorkSession $session): void;
    public function deleteByItem(ItemId $itemId): void;
    public function deleteByProject(ProjectId $projectId): void;
}
```

El dominio define QUE se necesita (interfaz). La infraestructura decide COMO (Doctrine, en memoria, etc.).

### 2.5 Excepciones de Dominio

```
DomainException (abstracta, code 400)
  ├── NotFoundException (code 404)
  │     ├── ItemNotFoundException
  │     ├── ProjectNotFoundException
  │     └── WorkSessionNotFoundException
  ├── ValidationException (code 422)
  │     ├── InvalidEmailException
  │     └── UserAlreadyExistsException
  ├── AuthenticationException (code 401)
  ├── AccessDeniedException (code 403)
  ├── InvalidCredentialsException (code 401)
  └── ActiveSessionExistsException (code 409)
```

`ApiExceptionListener` captura cualquier `DomainException` y devuelve JSON con el HTTP code correspondiente. Esto desacopla el dominio de HTTP — las entidades y use cases no saben nada de codigos HTTP.

### 2.6 Domain Service: ProjectEstimator

Calcula estimaciones basadas en la velocidad real del usuario:

```
velocityPerActiveDay = totalWorkedHours / activeDays
frequencyDaysPerWeek = activeDays / weeksSinceStart
remainingHours       = pendingEstimatedHours - pendingWorkedHours
activeDaysRemaining  = remainingHours / velocityPerActiveDay
daysRemaining        = activeDaysRemaining * 7 / frequencyDaysPerWeek
```

Principios clave:
- Solo cuenta sesiones cerradas (`closedSessions()`)
- Cuenta dias unicos con actividad (`workedDay()`)
- **Remaining correcto**: solo suma horas estimadas y trabajadas de items PENDIENTES. Las horas trabajadas en items completados no se descuentan del restante de otros items (evita doble conteo)
- **Velocity global**: usa TODAS las horas trabajadas (completed + pending) porque refleja el ritmo real del usuario
- Devuelve `null` para fecha estimada si no hay datos suficientes

#### Metodos privados (Single Responsibility)

| Metodo | Responsabilidad |
|--------|----------------|
| `sumEstimated(items)` | Suma horas estimadas de un array de items |
| `sumDuration(sessions)` | Suma duracion de sesiones cerradas |
| `workedHoursForItems(sessions, items)` | Filtra sesiones por itemId y suma sus horas |
| `closedSessions(sessions)` | Filtra sesiones con endedAt != null |
| `countActiveDays(sessions)` | Cuenta dias unicos de trabajo |
| `projectCompletion(...)` | Calcula fecha estimada, devuelve tuple `[?int, ?int, ?DateTimeImmutable]` |

---

## 3. Application Layer

### 3.1 Use Cases

Cada Use Case sigue el patron `Request -> UseCase -> Response`:

```php
final class ToggleItemStatusUseCase
{
    public function __construct(
        private readonly ItemRepositoryInterface $itemRepository,
    ) {}

    public function execute(ToggleItemStatusRequest $request): ToggleItemStatusResponse
    {
        $item = $this->itemRepository->findById($request->itemId());

        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        if ($item->status() === ItemStatus::COMPLETED) {
            $item->markAsPending();
        } else {
            $item->markAsCompleted();
        }

        $this->itemRepository->save($item);

        return new ToggleItemStatusResponse(ItemDTO::fromEntity($item));
    }
}
```

### 3.2 Lista completa de Use Cases

| Modulo | Use Case | Descripcion |
|--------|----------|-------------|
| Auth | `LoginUseCase` | Autenticacion con email/password, devuelve JWT |
| User | `CreateUserUseCase` | Registro con validacion de email unico |
| Project | `CreateProjectUseCase` | Crea proyecto asociado al usuario |
| Project | `UpdateProjectUseCase` | Actualiza nombre/descripcion |
| Project | `DeleteProjectUseCase` | Elimina con cascade en dominio |
| Project | `ListProjectsUseCase` | Lista proyectos del usuario |
| Project | `GetProjectWithItemsUseCase` | Detalle con items y sesiones |
| Project | `GetProjectEstimationUseCase` | Calcula estimacion via ProjectEstimator |
| Project | `ToggleProjectStatusUseCase` | Alterna active <-> completed |
| Item | `CreateItemUseCase` | Crea item en un proyecto |
| Item | `UpdateItemUseCase` | Actualiza nombre/horas estimadas |
| Item | `DeleteItemUseCase` | Elimina con cascade en dominio |
| Item | `GetItemSessionsUseCase` | Lista sesiones de un item |
| Item | `ToggleItemStatusUseCase` | Alterna pending <-> completed |
| WorkSession | `StartWorkSessionUseCase` | Inicia sesion (valida sesion unica) |
| WorkSession | `FinishWorkSessionUseCase` | Finaliza sesion activa |
| WorkSession | `UpdateWorkSessionUseCase` | Edita timestamps manualmente |
| WorkSession | `DeleteWorkSessionUseCase` | Elimina sesion |
| Inventory | `ListInventoryUseCase` | Todos los items del usuario con datos de proyecto |

### 3.3 Cascade de borrado en dominio

**Por que no usar ON DELETE CASCADE en BD?**

En DDD, la logica de negocio debe ser explicita y vivir en el dominio, no escondida en constraints de base de datos. Si mañana cambias MySQL por MongoDB, los cascades de BD desaparecen, pero los Use Cases siguen funcionando.

```
DeleteProjectUseCase.execute(projectId)
  1. sessionRepository->deleteByProject()   <- Borra TODAS las work sessions
  2. itemRepository->deleteByProject()       <- Borra TODOS los items
  3. projectRepository->delete()             <- Borra el proyecto

DeleteItemUseCase.execute(itemId)
  1. sessionRepository->deleteByItem()       <- Borra work sessions del item
  2. itemRepository->delete()                <- Borra el item
```

El orden es critico: siempre se borran primero las entidades hijas.

Las operaciones de borrado se ejecutan dentro de una **transaccion** via `TransactionPort`:

```php
$this->transaction->transactional(function () use ($request, $project): void {
    $this->sessionRepository->deleteByProject($request->projectId());
    $this->itemRepository->deleteByProject($request->projectId());
    $this->projectRepository->delete($project);
});
```

### 3.4 Ports (Application Layer)

Los ports son interfaces que la capa de Application define para desacoplarse de la infraestructura. La infraestructura los implementa como adapters.

| Port | Responsabilidad | Adapter (Infrastructure) |
|------|----------------|--------------------------|
| `CurrentUserProvider` | Obtener el UserId del usuario autenticado | `SymfonyCurrentUserProvider` (lee JWT via Symfony Security) |
| `PasswordHasherPort` | Hashear y verificar passwords | `SymfonyPasswordHasher` (usa `UserPasswordHasherInterface` de Symfony) |
| `TokenGeneratorPort` | Generar tokens JWT | `LexikTokenGenerator` (usa `JWTTokenManagerInterface` de Lexik) |
| `TransactionPort` | Ejecutar operaciones en transaccion | `DoctrineTransactionManager` (usa `EntityManager::wrapInTransaction()`) |

Esto permite que `LoginUseCase` y `CreateUserUseCase` no importen nada de Symfony ni Lexik:

```php
final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherPort      $passwordHasher,  // Port
        private readonly TokenGeneratorPort      $tokenGenerator,  // Port
    ) {}
}
```

### 3.5 SecurityUser — Adapter Pattern

La entidad `User` del dominio NO implementa `UserInterface` de Symfony. En su lugar, un adapter en infraestructura la envuelve:

```php
// Infrastructure/Security/SecurityUser.php
final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(private readonly User $domainUser) {}

    public function domainUser(): User               { return $this->domainUser; }
    public function getUserIdentifier(): string       { return $this->domainUser->email()->value(); }
    public function getRoles(): array                 { return $this->domainUser->roles(); }
    public function getPassword(): string             { return $this->domainUser->password(); }
}
```

`SecurityUserProvider` carga el usuario de BD y lo envuelve:

```php
final class SecurityUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findByEmail(Email::fromString($identifier));
        if ($user === null) { throw new UserNotFoundException(); }
        return new SecurityUser($user);
    }
}
```

Esto mantiene el dominio 100% libre de Symfony.

### 3.6 DTOs

Los DTOs convierten entidades de dominio a datos planos para la respuesta HTTP. Usan factory methods estaticos:

```php
final class ItemDTO
{
    public static function fromEntity(
        Item $item, int $totalSessions = 0, float $totalHours = 0.0,
        ?WorkSessionDTO $openSession = null, ?string $projectName = null,
    ): self { ... }
}
```

---

## 4. Infrastructure Layer

### 4.1 Controllers

Los controllers son delgados — solo:
1. Extraen datos del Request HTTP
2. Construyen el objeto Request del Use Case
3. Ejecutan el Use Case
4. Devuelven el Response como JSON

```php
#[Route('/{id}/toggle-status', methods: ['PUT'])]
public function toggleStatus(string $id, ToggleItemStatusUseCase $useCase): JsonResponse
{
    $response = $useCase->execute(new ToggleItemStatusRequest($id));

    return new JsonResponse($response->item()->toArray());
}
```

Los DTOs encapsulan su propia serializacion via `toArray()` — los controllers nunca acceden a propiedades individuales del DTO.

### 4.2 Doctrine Repositories (Adapters)

Implementan las interfaces del dominio usando Doctrine ORM:

```php
final class DoctrineItemRepository extends ServiceEntityRepository
    implements ItemRepositoryInterface
{
    public function findByUser(UserId $userId): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.userId = :userId')
            ->setParameter('userId', $userId->value())
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
```

### 4.3 Doctrine Custom Types

Convierten Value Objects a/desde la base de datos. Los tipos de ID heredan de `AbstractIdType` (DRY):

| Type | VO | Columna BD | Base |
|------|-----|------------|------|
| `UserIdType` | `UserId` | `VARCHAR(36)` | `AbstractIdType` |
| `ProjectIdType` | `ProjectId` | `VARCHAR(36)` | `AbstractIdType` |
| `ItemIdType` | `ItemId` | `VARCHAR(36)` | `AbstractIdType` |
| `WorkSessionIdType` | `WorkSessionId` | `VARCHAR(36)` | `AbstractIdType` |
| `EmailType` | `Email` | `VARCHAR(255)` | `Type` (directo) |
| `ItemStatusType` | `ItemStatus` | `VARCHAR(20)` | `Type` (enum) |
| `ProjectStatusType` | `ProjectStatus` | `VARCHAR(20)` | `Type` (enum) |

`AbstractIdType` elimina la duplicacion — cada tipo de ID solo define el nombre y la clase VO correspondiente.

### 4.4 ApiExceptionListener

Captura excepciones de dominio en rutas `/api` y las convierte en respuestas HTTP con el codigo correcto:

```php
public function onKernelException(ExceptionEvent $event): void
{
    $exception = $event->getThrowable();

    if (!$exception instanceof DomainException) {
        return; // Symfony maneja las demas (500)
    }

    $statusCode = match (true) {
        $exception instanceof AuthenticationException    => 401,
        $exception instanceof AccessDeniedException      => 403,
        $exception instanceof NotFoundException          => 404,
        $exception instanceof InvalidCredentialsException => 401,
        $exception instanceof UserAlreadyExistsException => 409,
        $exception instanceof ActiveSessionExistsException => 409,
        $exception instanceof ValidationException        => 422,
        default                                          => 400,
    };

    $event->setResponse(new JsonResponse(
        ['error' => $exception->getMessage()],
        $statusCode,
    ));
}
```

### 4.5 Doctrine Mappings (XML)

Las entidades no tienen anotaciones de Doctrine — se usa XML mapping para mantener el dominio limpio de dependencias de infraestructura:

```
backend/config/doctrine/mapping/
  User.orm.xml
  Project.orm.xml
  Item.orm.xml
  WorkSession.orm.xml
```

---

## 5. Frontend

### 5.1 API Services Layer

Cada modulo del backend tiene su servicio API correspondiente en el frontend:

| Servicio | Metodos | Tipos |
|----------|---------|-------|
| `projectApi` | `list()`, `detail(id)`, `estimation(id)`, `create()`, `update()`, `toggleStatus()`, `remove()` | `Project`, `Estimation` |
| `itemApi` | `create()`, `update()`, `toggleStatus()`, `remove()`, `sessions(id)` | `Item`, `Session` |
| `sessionApi` | `start()`, `finish()`, `update()`, `remove()` | `Session` |
| `inventoryApi` | `list()` | `InventoryItem` |
| `authApi` | `login()`, `register()` | `LoginResponse`, `RegisterResponse` |

Todos importan desde `@/api/axios` (instancia configurada) y devuelven tipos propios o de `@/types/models`.

### 5.2 TokenIntrospector

Utilidad que decodifica el JWT client-side para detectar expiracion proactivamente:

```typescript
export const TokenIntrospector = {
  decode(token: string): JwtPayload | null,
  isExpired(token: string, marginSeconds = 30): boolean,
  secondsUntilExpiry(token: string): number,
}
```

Se usa en:
- `authStore.isAuthenticated` — getter puro que verifica token + expiracion
- `authStore.loadStoredToken()` — limpia tokens expirados al arrancar
- `api/axios.ts` — interceptor de request rechaza peticiones con token expirado

### 5.3 Stores (Pinia)

| Store          | Responsabilidad                                           |
|----------------|-----------------------------------------------------------|
| `authStore`    | Login, registro, JWT en localStorage, logout, `isAuthenticated` via TokenIntrospector |
| `projectStore` | CRUD proyectos/items via API services, estimacion, inventario, restaurar timer |
| `timerStore`   | Timer en tiempo real via `sessionApi`, start/stop/restore, formato elapsed |

### 5.4 Patron: Blocking Overlay

Las operaciones destructivas bloquean toda la pantalla con un overlay:

```typescript
const { loading, loadingMessage, run } = useBlockingAction()

await run('Eliminando item...', async () => {
  await itemApi.remove(id)
  projectStore.removeItem(id)
})
```

### 5.5 Timer Global

El timer se muestra en la NavBar y persiste entre navegaciones:
- `timerStore.start()` llama al backend y arranca un `setInterval`
- `timerStore.restore()` reconstruye el timer desde una sesion abierta en BD
- `projectStore.restoreTimer()` busca un item con `openSession` al cargar un proyecto

### 5.6 Confirmaciones Inline

Las acciones criticas (completar item, iniciar/parar timer, eliminar) muestran confirmacion inline:

```
[ ] checkbox  ->  "¿Completar? [Si] [No]"  ->  API call + refresh estimation
```

### 5.7 Inventario

Vista global de todos los items del usuario con:
- Filtros por estado (Todos, Pendientes, En progreso, Completados)
- Toggle de estado con confirmacion
- Link directo a cada proyecto
- Fecha de creacion y horas trabajadas

---

## 6. Flujos completos

### 6.1 Iniciar sesion de trabajo

```
1. Usuario hace click en "Play" en un item
2. ItemRow.vue muestra confirmacion "¿Iniciar? [Si] [No]"
3. timerStore.start(itemId, itemName, projectId)
4. POST /api/work-sessions { itemId, projectId }
5. StartWorkSessionUseCase:
   a. Verifica que no hay sesion activa -> ActiveSessionExistsException
   b. Verifica que el item existe -> ItemNotFoundException
   c. Verifica que el proyecto existe -> ProjectNotFoundException
   d. Crea WorkSession y guarda via repositorio
6. Controller devuelve JSON con los datos de la sesion
7. timerStore arranca el intervalo de 1 segundo
8. NavBar muestra el timer en tiempo real
```

### 6.2 Completar un item

```
1. Usuario hace click en checkbox del item
2. Se muestra "¿Completar? [Si] [No]"
3. PUT /api/items/{id}/toggle-status
4. ToggleItemStatusUseCase:
   a. Busca el item -> ItemNotFoundException si no existe
   b. Si completed -> markAsPending(), si no -> markAsCompleted()
   c. Guarda el item
5. Frontend actualiza status del item en el store
6. Frontend llama refreshEstimation()
7. GET /api/projects/{id}/estimation
8. ProjectEstimator recalcula:
   - El item completado ya no suma a remainingHours
   - La velocidad global se mantiene
   - La fecha estimada se actualiza
9. ProjectEstimationCard se re-renderiza con los nuevos datos
```

### 6.3 Flujo de excepciones

```
Domain Exception thrown
       |
       v
ApiExceptionListener::onKernelException()
       |
       v
DomainException? --No--> Symfony default handler (500)
       |
      Yes
       |
       v
JsonResponse { error: message } con HTTP code del exception
       |
       v
Axios interceptor en frontend
       |
       ├── 401 -> authStore.logout() + redirect /login
       └── Otros -> toast.error(error.response.data.error)
```

---

## 7. Testing

### 7.1 Backend — Tests unitarios (PHPUnit)

```
tests/Unit/
  Domain/
    Entity/          <- Item (status transitions), Project, User, WorkSession
    ValueObject/     <- Email, UserId
    Service/         <- ProjectEstimator (completed items, velocity, remaining)
  Application/
    UseCase/         <- DeleteItem, DeleteProject, StartWorkSession
```

**Estrategia**:
- **Entidades y VOs**: test directo sin mocks, usando factories (`Item::create()`, `WorkSession::startNow()`). Verifican invariantes, validacion y transiciones de estado
- **Use Cases**: mocks de repositorios y ports (`TransactionPort`, `CurrentUserProvider`). Verifican orden de llamadas, ownership y excepciones
- **ProjectEstimator**: test con entidades reales creadas via factories, sesiones con `startNow()` + `update()` para controlar fechas

**Principio clave**: los tests NUNCA llaman constructores privados directamente — siempre usan las factory methods del dominio, respetando la encapsulacion.

Ejemplo — test del estimador con items completados:

```php
public function testCompletedItemsDoNotCountAsRemaining(): void
{
    $pendingItem   = $this->createItem(8.0);
    $completedItem = $this->createItem(5.0);
    $completedItem->markAsCompleted();

    $sessions = [
        $this->sessionForItem($pendingItem,   '2026-03-01 10:00', '2026-03-01 12:00'), // 2h
        $this->sessionForItem($completedItem, '2026-03-02 10:00', '2026-03-02 13:00'), // 3h
    ];

    $estimation = $this->estimator->estimate(
        new DateTimeImmutable('-14 days'),
        [$pendingItem, $completedItem],
        $sessions,
    );

    $this->assertSame(13.0, $estimation->estimatedHours());  // Total scope
    $this->assertSame(5.0, $estimation->workedHours());       // All sessions (velocity)
    $this->assertSame(6.0, $estimation->remainingHours());    // pending: 8 - 2 = 6
}
```

### 7.2 Backend — Tests funcionales / E2E (WebTestCase)

```
tests/Functional/
  Api/               <- Tests E2E que llaman a endpoints reales
```

Test E2E que verifica el flujo completo de toggle-status:

```php
public function testToggleItemStatusChangesEstimation(): void
{
    $client = static::createClient();

    // 1. Login -> get JWT
    // 2. POST /api/items -> create item (status: pending)
    // 3. PUT /api/items/{id}/toggle-status -> status: completed
    // 4. GET /api/projects/{id}/estimation -> verify remaining decreased
    // 5. PUT /api/items/{id}/toggle-status -> status: pending (reversible)
    // 6. DELETE /api/items/{id} -> cleanup
}
```

**Para ejecutar E2E** necesitas una BD de test configurada:

```bash
# Crear BD de test
docker compose exec php bin/console doctrine:database:create --env=test
docker compose exec php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Ejecutar tests funcionales
docker compose exec php bin/phpunit tests/Functional/
```

### 7.3 Frontend (Vitest)

```
src/__tests__/
  utils/           <- formatHours, formatDate
  composables/     <- useBlockingAction
  stores/          <- timerStore, projectStore
```

**Estrategia**:
- Utils: funciones puras, input -> output
- Composables: test de reactive state y side effects
- Stores: Pinia con `createPinia()`, test de mutations sin API calls

---

## 8. Seguridad — Autenticacion, Autorizacion y Ownership

### 8.1 Vision general — Tres capas de seguridad

El proyecto tiene 3 niveles de seguridad que trabajan juntos:

```
┌─────────────────────────────────────────────────────────────────────┐
│  NIVEL 1: AUTENTICACION (JWT)                                       │
│  "¿Quien eres?"                                                     │
│  Symfony Security + Lexik JWT Bundle                                │
│  → Si no tienes token valido: HTTP 401                              │
├─────────────────────────────────────────────────────────────────────┤
│  NIVEL 2: AUTORIZACION (Roles)                                      │
│  "¿Puedes acceder a esta ruta?"                                     │
│  security.yaml → access_control                                     │
│  → /api/auth/* y /api/health son PUBLIC_ACCESS, lo demas ROLE_USER  │
├─────────────────────────────────────────────────────────────────────┤
│  NIVEL 3: OWNERSHIP (OwnershipGuard)                                │
│  "¿Este recurso es tuyo?"                                           │
│  OwnershipGuard en los Use Cases                                    │
│  → Si el recurso no es tuyo: HTTP 403                               │
└─────────────────────────────────────────────────────────────────────┘
```

### 8.2 Flujo completo de LOGIN

```
┌──────────┐       POST /api/auth/login         ┌──────────────┐
│ Frontend │  ──────{email, password}──────────→ │ AuthController│
│ (Vue)    │                                     └──────┬───────┘
└──────────┘                                            │
                                                        ▼
                                                ┌───────────────┐
                                                │ LoginUseCase  │
                                                │               │
                                                │ 1. findByEmail│
                                                │ 2. verify pwd │
                                                │ 3. create JWT │
                                                └───────┬───────┘
                                                        │
                         {token, user}                  │
┌──────────┐  ◄─────────────────────────────────────────┘
│ Frontend │
│          │  authStore.login():
│          │    1. Guarda token en localStorage
│          │    2. Guarda user en Pinia state
│          │    3. Redirige a /projects
└──────────┘
```

**¿Que contiene el JWT?** El email del usuario. Lexik lo firma con la clave privada RSA (`config/jwt/private.pem`). TTL: 28800 segundos (8 horas).

### 8.3 Flujo de una PETICION AUTENTICADA

```
┌──────────┐  GET /api/projects/123              ┌─────────────────────┐
│ Frontend │  ─────────────────────────────────→ │ SYMFONY SECURITY    │
│          │  Header: Authorization: Bearer xxx   │ (automatico)        │
└──────────┘                                     │                     │
                                                 │ 1. Lee el header    │
      ┌──────────────────────────────────────────│ 2. Decodifica JWT   │
      │ Si token invalido/expirado → HTTP 401    │ 3. Verifica firma   │
      │                                          │ 4. Extrae email     │
      │                                          │ 5. Carga User       │
      │                                          │ 6. Verifica ROLE    │
      │                                          └─────────┬───────────┘
      │                                                    │ Token valido
      │                                                    ▼
      │                                          ┌─────────────────────┐
      │                                          │ ProjectController   │
      │                                          │                     │
      │                                          │ getCurrentUser():   │
      │                                          │   getUser()         │
      │                                          │   → email del JWT   │
      │                                          │   → findByEmail()   │
      │                                          │   → User entity     │
      │                                          └─────────┬───────────┘
      │                                                    │
      │                                                    ▼
      │                                          ┌─────────────────────┐
      │                                          │ GetProjectUseCase   │
      │                                          │                     │
      │                                          │ 1. findById(123)    │
      │                                          │ 2. ownershipGuard   │
      │                                          │    .ensureOwnership │
      │                                          │ 3. return DTO       │
      │                                          └─────────────────────┘
      │
      │  ¿Y como sabe Axios que tiene que enviar el token?
      │
      │  axios.ts → interceptor de REQUEST:
      │    const token = localStorage.getItem('jwt_token')
      │    config.headers.Authorization = `Bearer ${token}`
      │
      │  axios.ts → interceptor de RESPONSE:
      │    if (status === 401) → borrar token → redirect /login
      │
      └──────────────────────────────────────────────────────────────────
```

### 8.4 OwnershipGuard — ¿Como funciona?

```
┌──────────────────────────────────────────────────────────────────┐
│                     OwnershipGuard                                │
│                  (Application Layer)                              │
│                                                                  │
│  ensureOwnership(OwnableResource $resource):                     │
│    │                                                             │
│    ├── currentUserProvider.currentUserId()                        │
│    │     │                                                       │
│    │     └── SymfonyCurrentUserProvider (Infrastructure)          │
│    │           │                                                 │
│    │           ├── security.getUser() → SecurityUser (adapter)    │
│    │           └── securityUser.domainUser().id() → UserId        │
│    │                                                             │
│    ├── $resource.ownerId()                                        │
│    │     │                                                       │
│    │     └── La entidad (Project/Item/WorkSession)                │
│    │         devuelve su userId                                  │
│    │                                                             │
│    └── ¿Son iguales?                                              │
│          ├── SI → continua la ejecucion                           │
│          └── NO → throw AccessDeniedException (HTTP 403)          │
└──────────────────────────────────────────────────────────────────┘
```

**¿Donde se usa?** En TODO caso de uso que opera sobre un recurso existente:

| Use Case | ¿Usa OwnershipGuard? | ¿Por que? |
|----------|---------------------|-----------|
| CreateItem | NO | No hay recurso existente, se crea nuevo |
| CreateProject | NO | No hay recurso existente, se crea nuevo |
| UpdateItem | SI | Verificar que el item es tuyo |
| DeleteItem | SI | Verificar que el item es tuyo |
| ToggleItemStatus | SI | Verificar que el item es tuyo |
| GetItemSessions | SI | Verificar que el item es tuyo |
| UpdateProject | SI | Verificar que el proyecto es tuyo |
| DeleteProject | SI | Verificar que el proyecto es tuyo |
| GetProjectWithItems | SI | Verificar que el proyecto es tuyo |
| GetProjectEstimation | SI | Verificar que el proyecto es tuyo |
| ToggleProjectStatus | SI | Verificar que el proyecto es tuyo |
| StartWorkSession | SI | Verificar que el item es tuyo |
| FinishWorkSession | SI | Verificar que la sesion es tuya |
| UpdateWorkSession | SI | Verificar que la sesion es tuya |
| DeleteWorkSession | SI | Verificar que la sesion es tuya |
| ListProjects | NO | findByUser() ya filtra por usuario |
| ListInventory | NO | findByUser() ya filtra por usuario |

**Clave**: Los List no necesitan Guard porque el repositorio ya filtra por userId — imposible ver datos de otro usuario.

### 8.5 Interfaces que hacen posible la seguridad

```
┌── Domain ──────────────────────────────────────────────────────────┐
│                                                                    │
│  interface OwnableResource {                                       │
│      public function ownerId(): UserId;                            │
│  }                                                                 │
│                                                                    │
│  Project implements OwnableResource  → return $this->userId        │
│  Item implements OwnableResource     → return $this->userId        │
│  WorkSession implements OwnableResource → return $this->userId     │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘

┌── Application ─────────────────────────────────────────────────────┐
│                                                                    │
│  interface CurrentUserProvider {        (PORT)                      │
│      public function currentUserId(): UserId;                      │
│  }                                                                 │
│                                                                    │
│  class OwnershipGuard {                                            │
│      constructor(CurrentUserProvider $provider)                     │
│      ensureOwnership(OwnableResource $resource): void              │
│  }                                                                 │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘

┌── Infrastructure ──────────────────────────────────────────────────┐
│                                                                    │
│  class SecurityUser implements UserInterface (ADAPTER)              │
│      → Envuelve User del dominio para Symfony Security              │
│                                                                    │
│  class SecurityUserProvider implements UserProviderInterface        │
│      → Carga User de BD por email y lo envuelve en SecurityUser     │
│                                                                    │
│  class SymfonyCurrentUserProvider implements CurrentUserProvider    │
│      (ADAPTER)                                                     │
│      → security.getUser() → SecurityUser → domainUser().id()       │
│                                                                    │
│  class SymfonyPasswordHasher implements PasswordHasherPort         │
│      → Hashea/verifica via SecurityUser wrapper                     │
│                                                                    │
│  class LexikTokenGenerator implements TokenGeneratorPort           │
│      → Genera JWT via SecurityUser wrapper                          │
│                                                                    │
│  class DoctrineTransactionManager implements TransactionPort       │
│      → EntityManager::wrapInTransaction()                           │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

### 8.6 ¿Es este patron SIEMPRE asi en DDD/Hexagonal/SOLID?

**SI, esta es la forma canonica**. Los conceptos son reutilizables en cualquier proyecto:

| Concepto | En este proyecto | En cualquier proyecto DDD |
|----------|-----------------|--------------------------|
| Autenticacion separada de autorizacion | JWT (autenticacion) vs OwnershipGuard (autorizacion) | Siempre. Son responsabilidades distintas (S de SOLID) |
| Port & Adapter para el usuario actual | `CurrentUserProvider` (port) + `SymfonyCurrentUserProvider` (adapter) | Siempre. Permite cambiar de JWT a OAuth, SAML, etc. sin tocar los Use Cases (D de SOLID) |
| Interfaz OwnableResource | Entities implementan `ownerId()` | Comun. Permite un guard generico para cualquier entidad (O de SOLID) |
| Guard en Application, no en Controller | OwnershipGuard vive en Application | Siempre. La seguridad de negocio es logica de negocio, no de HTTP |
| Excepciones de dominio mapeadas a HTTP | `AccessDeniedException` → 403 via Listener | Siempre. El dominio no conoce HTTP, el listener traduce |
| Repositorios filtran por usuario | `findByUser(UserId)` | Siempre. Nunca `findAll()` sin filtro de seguridad |

**¿Que cambiaria en otro proyecto?**
- El adapter: en vez de JWT podria ser OAuth2, API Keys, sessions...
- Las entidades: otro dominio, otros recursos
- Los nombres: pero la estructura PORT → ADAPTER → GUARD es la misma

### 8.7 Resumen de seguridad

- **JWT**: token en `localStorage` (TTL 8 horas), enviado via `Authorization: Bearer` header
- **Interceptores Axios**: el de request añade el token automaticamente; el de response redirige a /login en 401
- **Firewall Symfony**: valida el JWT antes de que llegue al controller
- **Rutas publicas**: `/api/auth/login`, `/api/auth/register` y `/api/health`
- **OwnershipGuard**: verifica que el recurso pertenece al usuario autenticado
- **Filtrado por usuario**: `findByUser()` en vez de `findAll()` — un usuario nunca ve datos de otro
- **Sesion unica**: solo una sesion activa por usuario (validado en backend con `ActiveSessionExistsException`)
- **Validacion**: todos los inputs validados en entidades de dominio y Value Objects, no en controllers
- **Password**: hasheado con el hasher nativo de Symfony (bcrypt/argon2)
- **Excepciones**: `AuthenticationException` (401), `AccessDeniedException` (403), todas capturadas por `ApiExceptionListener`

---

## 9. Comandos utiles

```bash
# Backend
php bin/phpunit                              # Ejecutar tests
php bin/phpunit --testdox                    # Tests con descripcion legible
php bin/phpunit --filter=ProjectEstimatorTest # Un test especifico
php bin/console doctrine:migrations:migrate  # Ejecutar migraciones
php bin/console doctrine:schema:validate     # Validar schema vs mappings

# Frontend
npm run dev                                  # Dev server
npm run build                                # Build produccion
npm test                                     # Ejecutar tests
npm run test:coverage                        # Tests con cobertura

# Docker
docker compose up -d --build                 # Levantar todo
docker compose exec php bash                 # Shell en el contenedor PHP
docker compose logs -f php                   # Ver logs del backend
```
