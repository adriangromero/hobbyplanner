# HobbyPlanner — Guia Tecnica

## 1. Arquitectura General

HobbyPlanner sigue una arquitectura **DDD (Domain-Driven Design)** con patron **hexagonal (Ports & Adapters)**, aplicando principios **SOLID** y **Clean Code** (Robert C. Martin).

### Principios SOLID aplicados

| Principio | Aplicacion en el proyecto |
|-----------|--------------------------|
| **S** — Single Responsibility | Cada Use Case tiene una unica razon de cambio. Los controllers solo traducen HTTP ↔ Use Case. |
| **O** — Open/Closed | Nuevas excepciones extienden `DomainException` sin modificar `ApiExceptionListener`. Nuevos repositorios implementan interfaces existentes. |
| **L** — Liskov Substitution | `DoctrineProjectRepository` es intercambiable por un `InMemoryProjectRepository` en tests sin cambiar ningun Use Case. |
| **I** — Interface Segregation | Cada repositorio expone solo los metodos que su agregado necesita. No hay una interfaz "god repository". |
| **D** — Dependency Inversion | Los Use Cases dependen de `ProjectRepositoryInterface` (abstraccion del dominio), no de `DoctrineProjectRepository` (detalle de infraestructura). |

### Capas del backend

```
┌─────────────────────────────────────────────────┐
│                 Infrastructure                   │
│  Controllers, Doctrine Repos, Event Listeners    │
├─────────────────────────────────────────────────┤
│                  Application                     │
│         Use Cases (Request → Response)           │
│                    DTOs                           │
├─────────────────────────────────────────────────┤
│                    Domain                        │
│  Entities, Value Objects, Repository Interfaces  │
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
  api/          ← Axios instance con interceptor JWT
  components/   ← Componentes Vue organizados por feature
  composables/  ← Logica reutilizable (useBlockingAction)
  layout/       ← AppLayout, NavBar, UserMenu
  stores/       ← Pinia stores (project, timer, auth)
  utils/        ← Funciones puras (formatDate, formatHours)
  views/        ← Paginas (Login, ProjectsList, ProjectDetail)
  router/       ← Vue Router con rutas protegidas
```

---

## 2. Domain Layer

### 2.1 Entidades

| Entidad       | Responsabilidad                                        |
|---------------|--------------------------------------------------------|
| `User`        | Usuario con email, password, roles. Implementa `UserInterface` de Symfony |
| `Project`     | Proyecto de hobby con nombre y descripcion             |
| `Item`        | Item dentro de un proyecto con horas estimadas          |
| `WorkSession` | Sesion de trabajo con inicio/fin, calcula duracion      |

Cada entidad:
- Valida sus invariantes en el constructor y metodos de mutacion
- Lanza `ValidationException` si los datos son invalidos
- Gestiona sus propios timestamps (`createdAt`, `updatedAt`)

### 2.2 Value Objects

| Value Object      | Proposito                              |
|-------------------|----------------------------------------|
| `UserId`          | UUID v4 tipado para usuarios           |
| `ProjectId`       | UUID v4 tipado para proyectos          |
| `ItemId`          | UUID v4 tipado para items              |
| `WorkSessionId`   | UUID v4 tipado para sesiones           |
| `Email`           | Email validado con `filter_var`        |
| `ProjectEstimation` | VO inmutable con datos de estimacion |

Todos los IDs:
- Se crean con `::create()` (genera UUID v4)
- Se reconstruyen con `::fromString()` (valida formato UUID)
- Tienen `equals()` para comparacion por valor
- Implementan `__toString()`

### 2.3 Repository Interfaces (Ports)

```php
interface ProjectRepositoryInterface
{
    public function save(Project $project): void;
    public function findById(ProjectId $id): ?Project;
    public function findByUser(UserId $userId): array;
    public function delete(Project $project): void;
}
```

El dominio define QUE se necesita (interfaz). La infraestructura decide COMO (Doctrine, en memoria, etc.).

### 2.4 Excepciones de Dominio

```
DomainException (abstracta, code 400)
  ├── NotFoundException (code 404)
  │     ├── ItemNotFoundException
  │     ├── ProjectNotFoundException
  │     └── WorkSessionNotFoundException
  ├── ValidationException (code 422)
  │     ├── InvalidEmailException
  │     └── UserAlreadyExistsException
  ├── InvalidCredentialsException (code 401)
  └── ActiveSessionExistsException (code 409)
```

`ApiExceptionListener` captura cualquier `DomainException` y devuelve JSON con el HTTP code correspondiente.

### 2.5 Domain Service: ProjectEstimator

Calcula estimaciones basadas en la velocidad real del usuario:

```
velocityPerActiveDay = workedHours / activeDays
frequencyDaysPerWeek = activeDays / weeksSinceStart
activeDaysRemaining  = remainingHours / velocityPerActiveDay
daysRemaining        = activeDaysRemaining * 7 / frequencyDaysPerWeek
```

- Solo cuenta sesiones finalizadas (ignora sesiones abiertas)
- Cuenta dias unicos con actividad (`workedDay()`)
- Devuelve `null` para la fecha estimada si no hay suficientes datos

---

## 3. Application Layer

### 3.1 Use Cases

Cada Use Case sigue el patron `Request → UseCase → Response`:

```php
final class DeleteItemUseCase
{
    public function __construct(
        private readonly ItemRepositoryInterface        $itemRepository,
        private readonly WorkSessionRepositoryInterface $sessionRepository,
    ) {}

    public function execute(DeleteItemRequest $request): void
    {
        $item = $this->itemRepository->findById($request->itemId());
        if ($item === null) {
            throw new ItemNotFoundException($request->itemId()->value());
        }

        // Cascade en dominio: primero sesiones, luego item
        $this->sessionRepository->deleteByItem($request->itemId());
        $this->itemRepository->delete($item);
    }
}
```

### 3.2 Cascade de borrado en dominio

**Por que no usar ON DELETE CASCADE en BD?**

En DDD, la logica de negocio debe ser explicita y vivir en el dominio, no escondida en constraints de base de datos. Si mañana cambias MySQL por MongoDB, los cascades de BD desaparecen, pero los Use Cases siguen funcionando.

**Flujo de DeleteProjectUseCase:**

```
DeleteProjectUseCase.execute(projectId)
       │
       ▼
  findById(projectId) ──null──→ throw ProjectNotFoundException
       │
     exists
       │
       ▼
  sessionRepository.deleteByProject(projectId)   ← 1. Borra TODAS las work sessions
       │
       ▼
  itemRepository.deleteByProject(projectId)       ← 2. Borra TODOS los items
       │
       ▼
  projectRepository.delete(project)               ← 3. Borra el proyecto
```

**Flujo de DeleteItemUseCase:**

```
DeleteItemUseCase.execute(itemId)
       │
       ▼
  findById(itemId) ──null──→ throw ItemNotFoundException
       │
     exists
       │
       ▼
  sessionRepository.deleteByItem(itemId)          ← 1. Borra work sessions del item
       │
       ▼
  itemRepository.delete(item)                     ← 2. Borra el item
```

El **orden es critico**: siempre se borran primero las entidades hijas (sessions), luego las intermedias (items), y por ultimo la raiz (project). Los tests de integracion verifican este orden exacto con mocks.

### 3.3 DTOs

Los DTOs convierten entidades de dominio a arrays planos para la respuesta HTTP:

```php
final class ProjectDTO
{
    public static function fromEntity(Project $project): array
    {
        return [
            'id'          => $project->id()->value(),
            'name'        => $project->name(),
            'description' => $project->description(),
            'createdAt'   => $project->createdAt()->format('c'),
        ];
    }
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
#[Route('/api/projects/{id}', methods: ['DELETE'])]
public function delete(string $id, DeleteProjectUseCase $useCase): JsonResponse
{
    $useCase->execute(new DeleteProjectRequest($id));
    return $this->json(null, Response::HTTP_NO_CONTENT);
}
```

### 4.2 Doctrine Repositories

Implementan las interfaces del dominio usando Doctrine ORM:

```php
final class DoctrineProjectRepository extends ServiceEntityRepository
    implements ProjectRepositoryInterface
{
    public function findByUser(UserId $userId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.userId = :userId')
            ->setParameter('userId', $userId->value())
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

### 4.3 ApiExceptionListener

Captura excepciones de dominio y las convierte en respuestas HTTP:

```php
public function onKernelException(ExceptionEvent $event): void
{
    $exception = $event->getThrowable();

    if ($exception instanceof DomainException) {
        $response = new JsonResponse(
            ['error' => $exception->getMessage()],
            $exception->getCode(),
        );
        $event->setResponse($response);
    }
}
```

### 4.4 Doctrine Mappings (XML)

Las entidades no tienen anotaciones de Doctrine — se usa XML mapping:

```
backend/config/doctrine/mapping/
  User.orm.xml
  Project.orm.xml
  Item.orm.xml
  WorkSession.orm.xml
```

---

## 5. Frontend

### 5.1 Stores (Pinia)

| Store          | Responsabilidad                                           |
|----------------|-----------------------------------------------------------|
| `authStore`    | Login, registro, JWT en localStorage                      |
| `projectStore` | CRUD proyectos/items, estimacion, restaurar timer         |
| `timerStore`   | Timer en tiempo real, start/stop/restore, formato elapsed |

### 5.2 Patron: Blocking Overlay

Las operaciones destructivas (borrar, editar) bloquean toda la pantalla con un overlay:

```typescript
// Composable reutilizable
const { loading, loadingMessage, run } = useBlockingAction()

await run('Eliminando item...', async () => {
  await api.delete(`/items/${id}`)
  projectStore.removeItem(id)
})
```

```html
<!-- Overlay global via Teleport -->
<Teleport to="body">
  <BlockingOverlay :active="loading" :message="loadingMessage" />
</Teleport>
```

### 5.3 Timer Global

El timer se muestra en la NavBar y persiste entre navegaciones:
- `timerStore.start()` llama al backend y arranca un `setInterval`
- `timerStore.restore()` reconstruye el timer desde una sesion abierta en BD
- `projectStore.restoreTimer()` busca un item con `openSession` al cargar un proyecto

### 5.4 Lazy Loading de Sesiones

Las sesiones NO se cargan con el proyecto. Se cargan bajo demanda:
- `GET /api/projects/{id}` devuelve items con `totalSessions`, `totalHours`, `openSession`
- `GET /api/items/{id}/sessions` se llama solo al abrir el `SessionsModal`

---

## 6. Flujo completo de un Request

Ejemplo: **Iniciar sesion de trabajo**

```
1. Usuario hace click en "Play" en un item
2. ItemRow.vue llama timerStore.start(itemId, itemName, projectId)
3. timerStore hace POST /api/work-sessions con { itemId, projectId }
4. WorkSessionController recibe el request
5. Controller extrae el usuario del JWT y construye StartWorkSessionRequest
6. StartWorkSessionUseCase.execute():
   a. Verifica que no hay sesion activa → ActiveSessionExistsException si la hay
   b. Verifica que el item existe → ItemNotFoundException si no
   c. Verifica que el proyecto existe → ProjectNotFoundException si no
   d. Crea WorkSession entity y la guarda via repositorio
7. Controller devuelve JSON con los datos de la sesion
8. timerStore arranca el intervalo de 1 segundo
9. NavBar muestra el timer en tiempo real
```

---

## 7. Flujo de Excepciones

```
Domain Exception thrown
       │
       ▼
ApiExceptionListener::onKernelException()
       │
       ▼
DomainException? ──No──→ Symfony default handler (500)
       │
      Yes
       │
       ▼
JsonResponse { error: message } con HTTP code del exception
       │
       ▼
Axios interceptor en frontend
       │
       ├── 401 → Redirect a /login
       └── Otros → error.response.data.error mostrado al usuario
```

---

## 8. Testing

### Backend (PHPUnit)

```
tests/Unit/
  Domain/
    Entity/          ← User, Project, Item, WorkSession
    ValueObject/     ← Email, UserId
    Service/         ← ProjectEstimator
  Application/
    UseCase/         ← DeleteItem, DeleteProject, StartWorkSession
```

**Estrategia**:
- Entidades y VOs: test directo sin mocks
- Use Cases: mocks de repositorios, verifican orden de llamadas y excepciones
- ProjectEstimator: test con entidades reales, sin mocks

### Frontend (Vitest)

```
src/__tests__/
  utils/           ← formatHours
  composables/     ← useBlockingAction
  stores/          ← timerStore, projectStore
```

**Estrategia**:
- Utils: funciones puras, input → output
- Composables: test de reactive state y side effects
- Stores: Pinia con `createPinia()`, test de mutations sin API calls

---

## 9. Seguridad

- **JWT**: token en `localStorage`, enviado via `Authorization: Bearer` header
- **401 interceptor**: redirige a `/login` automaticamente
- **Filtrado por usuario**: `findByUser()` en vez de `findAll()` — un usuario nunca ve datos de otro
- **Sesion unica**: solo una sesion activa por usuario (validado en backend)
- **Validacion**: todos los inputs validados en entidades de dominio

---

## 10. Comandos utiles

```bash
# Backend
php bin/phpunit                          # Ejecutar tests
php bin/phpunit --testdox                # Tests con descripcion
php bin/console doctrine:migrations:diff # Generar migracion
php bin/console doctrine:migrations:migrate # Ejecutar migraciones

# Frontend
npm run dev                              # Dev server
npm run build                            # Build produccion
npm test                                 # Ejecutar tests
npm run test:coverage                    # Tests con cobertura
```
