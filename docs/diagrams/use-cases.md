# Casos de Uso - HobbyPlanner

## Diagrama General
```mermaid
graph LR
    subgraph Usuario
        U((Usuario))
    end

    subgraph Autenticación
        UC1[Registrarse]
        UC2[Iniciar sesión]
        UC3[Cerrar sesión]
        UC4[Ver perfil]
        UC5[Editar perfil]
    end

    subgraph Miniaturas
        UC6[Listar mis miniaturas]
        UC7[Crear miniatura]
        UC8[Ver miniatura]
        UC9[Editar miniatura]
        UC10[Eliminar miniatura]
        UC11[Marcar como completada]
    end

    subgraph Unidades
        UC12[Listar mis unidades]
        UC13[Crear unidad]
        UC14[Ver unidad]
        UC15[Editar unidad]
        UC16[Eliminar unidad]
        UC17[Añadir minis a unidad]
        UC18[Quitar minis de unidad]
    end

    subgraph Proyectos
        UC19[Listar mis proyectos]
        UC20[Crear proyecto]
        UC21[Ver proyecto]
        UC22[Editar proyecto]
        UC23[Eliminar proyecto]
        UC24[Añadir minis a proyecto]
        UC25[Añadir unidades a proyecto]
        UC26[Marcar proyecto completado]
    end

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5

    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9
    U --> UC10
    U --> UC11

    U --> UC12
    U --> UC13
    U --> UC14
    U --> UC15
    U --> UC16
    U --> UC17
    U --> UC18

    U --> UC19
    U --> UC20
    U --> UC21
    U --> UC22
    U --> UC23
    U --> UC24
    U --> UC25
    U --> UC26
```

## Detalle por módulo

### Autenticación
```mermaid
flowchart LR
    U((Usuario)) --> R[Registrarse]
    U --> L[Login]
    U --> LO[Logout]
    U --> P[Ver perfil]
    U --> EP[Editar perfil]

    R -->|email, password, nombre| V{Validar}
    V -->|OK| C[Crear cuenta]
    V -->|Error| E[Mostrar error]

    L -->|email, password| A{Autenticar}
    A -->|OK| T[Devolver JWT]
    A -->|Error| E
```

### Miniaturas
```mermaid
flowchart TB
    U((Usuario))
    
    U --> CREAR[Crear miniatura]
    U --> LISTAR[Listar miniaturas]
    U --> VER[Ver detalle]
    U --> EDITAR[Editar]
    U --> ELIMINAR[Eliminar]
    U --> COMPLETAR[Marcar completada]

    CREAR -->|nombre, facción, nivel| M[Nueva Miniatura]
    M --> EST[Calcular estimación]
    
    COMPLETAR --> ACT[Actualizar estado]
    ACT --> HORAS[Registrar horas reales]
```

### Unidades
```mermaid
flowchart TB
    U((Usuario))
    
    U --> CREAR[Crear unidad]
    U --> LISTAR[Listar unidades]
    U --> VER[Ver detalle]
    U --> EDITAR[Editar]
    U --> ELIMINAR[Eliminar]
    U --> ADD[Añadir minis]
    U --> REMOVE[Quitar minis]

    CREAR -->|nombre, cantidad, facción| UN[Nueva Unidad]
    UN --> EST[Calcular estimación]
    
    ADD --> SEL[Seleccionar minis]
    SEL --> ASOC[Asociar a unidad]
    ASOC --> RECALC[Recalcular estimación]
```

### Proyectos
```mermaid
flowchart TB
    U((Usuario))
    
    U --> CREAR[Crear proyecto]
    U --> LISTAR[Listar proyectos]
    U --> VER[Ver detalle]
    U --> EDITAR[Editar]
    U --> ELIMINAR[Eliminar]
    U --> ADD_M[Añadir minis]
    U --> ADD_U[Añadir unidades]
    U --> COMPLETAR[Completar proyecto]

    CREAR -->|nombre, descripción, deadline| P[Nuevo Proyecto]
    
    ADD_M --> ASOC_M[Asociar miniaturas]
    ADD_U --> ASOC_U[Asociar unidades]
    
    ASOC_M --> CALC[Calcular estimación total]
    ASOC_U --> CALC
    
    COMPLETAR --> CHECK{¿Todo completado?}
    CHECK -->|Sí| FIN[Marcar completado]
    CHECK -->|No| WARN[Avisar pendientes]
```

## Flujo: Crear proyecto completo
```mermaid
sequenceDiagram
    actor U as Usuario
    participant S as Sistema

    U->>S: Crear proyecto "Ejército Orkos"
    S-->>U: Proyecto creado

    U->>S: Crear miniatura "Warboss"
    S-->>U: Miniatura creada (estimación: 5h)

    U->>S: Crear unidad "Boyz x10"
    S-->>U: Unidad creada (estimación: 15h)

    U->>S: Añadir Warboss al proyecto
    S-->>U: OK - Estimación proyecto: 5h

    U->>S: Añadir Boyz al proyecto
    S-->>U: OK - Estimación proyecto: 20h

    U->>S: Marcar Warboss completada (4h reales)
    S-->>U: Progreso: 25% - Horas reales: 4h

    U->>S: Marcar Boyz completada (18h reales)
    S-->>U: Progreso: 100% - Horas reales: 22h

    U->>S: Completar proyecto
    S-->>U: Proyecto completado 🎉
```

## Tabla resumen

| Módulo | Casos de uso |
|--------|--------------|
| **Auth** | Registro, Login, Logout, Ver perfil, Editar perfil |
| **Miniaturas** | CRUD + Completar |
| **Unidades** | CRUD + Añadir/Quitar minis |
| **Proyectos** | CRUD + Añadir minis/unidades + Completar |