# Diagrama de Entidades - HobbyPlanner

## Modelo de Dominio
```mermaid
erDiagram
    USUARIO ||--o{ PROYECTO : "posee"
    USUARIO ||--o{ MINIATURA : "posee"
    USUARIO ||--o{ UNIDAD : "posee"
    
    PROYECTO }|--|{ MINIATURA : "contiene"
    PROYECTO }|--|{ UNIDAD : "contiene"
    UNIDAD }|--|{ MINIATURA : "agrupa"

    USUARIO {
        int id PK
        string email UK
        string password
        string nombre
        datetime fecha_registro
    }

    MINIATURA {
        int id PK
        int usuario_id FK
        string nombre
        string faccion
        string estado
        string nivel_detalle
        float horas_estimadas
        float horas_reales
        datetime fecha_creacion
    }

    UNIDAD {
        int id PK
        int usuario_id FK
        string nombre
        string faccion
        int cantidad
        string estado
        float horas_estimadas
        float horas_reales
        datetime fecha_creacion
    }

    PROYECTO {
        int id PK
        int usuario_id FK
        string nombre
        string descripcion
        string estado
        string prioridad
        float horas_estimadas
        float horas_reales
        datetime fecha_creacion
        datetime fecha_deadline
    }
```

## Relaciones

| Relación | Tipo | Descripción |
|----------|------|-------------|
| Usuario → Proyecto | 1:N | Un usuario tiene muchos proyectos |
| Usuario → Miniatura | 1:N | Un usuario tiene muchas miniaturas |
| Usuario → Unidad | 1:N | Un usuario tiene muchas unidades |
| Proyecto ↔ Miniatura | N:M | Un proyecto puede tener varias minis y viceversa |
| Proyecto ↔ Unidad | N:M | Un proyecto puede tener varias unidades y viceversa |
| Unidad ↔ Miniatura | N:M | Una unidad agrupa varias minis y una mini puede estar en varias unidades |

## Estados posibles

**Miniatura / Unidad:**
- `PENDIENTE` - Sin empezar
- `EN_PROGRESO` - Pintando
- `COMPLETADA` - Terminada

**Proyecto:**
- `PENDIENTE` - Sin empezar
- `EN_PROGRESO` - Trabajando
- `COMPLETADO` - Finalizado

## Niveles de detalle (Miniatura)

- `BASICO` - Imprimación + colores base
- `TABLETOP` - Listo para jugar
- `EXPOSICION` - Máximo detalle