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
    
    PROYECTO ||--o{ PLANIFICACION : "tiene"
    PLANIFICACION }o--o| MINIATURA : "planifica"
    PLANIFICACION }o--o| UNIDAD : "planifica"

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
        string nivel_detalle
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

    PLANIFICACION {
        int id PK
        int proyecto_id FK
        int miniatura_id FK
        int unidad_id FK
        string nombre
        datetime fecha_inicio
        datetime fecha_fin
        int orden
    }
```

## Relaciones

| Relación | Tipo | Descripción |
|----------|------|-------------|
| Usuario → Proyecto | 1:N | Un usuario tiene muchos proyectos |
| Usuario → Miniatura | 1:N | Un usuario tiene muchas miniaturas |
| Usuario → Unidad | 1:N | Un usuario tiene muchas unidades |
| Proyecto ↔ Miniatura | N:M | Proyectos contienen miniaturas |
| Proyecto ↔ Unidad | N:M | Proyectos contienen unidades |
| Unidad ↔ Miniatura | N:M | Unidades agrupan miniaturas |
| Proyecto → Planificación | 1:N | Un proyecto tiene varias planificaciones |
| Planificación → Miniatura | N:1 | Opcional |
| Planificación → Unidad | N:1 | Opcional |

## Estados

**Miniatura / Unidad:**
- `PENDIENTE` - Sin empezar
- `EN_PROGRESO` - Pintando
- `COMPLETADA` - Terminada

**Proyecto:**
- `PENDIENTE` - Sin empezar
- `EN_PROGRESO` - Trabajando
- `COMPLETADO` - Finalizado

## Niveles de detalle

- `BASICO` - Imprimación + colores base
- `TABLETOP` - Listo para jugar
- `EXPOSICION` - Máximo detalle

## Campos opcionales

| Entidad | Campo | ¿Opcional? |
|---------|-------|------------|
| Planificación | miniatura_id | ✅ Sí |
| Planificación | unidad_id | ✅ Sí |
| Planificación | fecha_inicio | ✅ Sí |
| Planificación | fecha_fin | ✅ Sí |
| Proyecto | fecha_deadline | ✅ Sí |
