# HobbyPlanner

Aplicación web para gestionar proyectos de pintura de miniaturas del hobby.

## Funcionalidades

- Tracking de tiempo con cronómetro integrado
- Gestión de miniaturas con seguimiento de progreso
- Organización por proyectos
- Estadísticas de productividad

## Stack

**Backend:** Symfony 7.1, PHP 8.2+, Doctrine ORM, MySQL 8.0

**Frontend:** Vue.js 3, TypeScript, Pinia, Tailwind CSS

**Testing:** PHPUnit, Vitest

## Instalación
```bash
git clone https://github.com/TU-USUARIO/hobbyplanner.git
cd hobbyplanner
docker-compose up -d

# Backend
docker-compose exec backend composer install
docker-compose exec backend php bin/console doctrine:migrations:migrate

# Frontend
docker-compose exec frontend npm install
```

- Backend: http://localhost:8000
- Frontend: http://localhost:5173

## Tests
```bash
docker-compose exec backend php bin/phpunit
docker-compose exec frontend npm run test
```

## Arquitectura

El proyecto sigue Arquitectura Hexagonal con separación en capas: Domain, Application, Infrastructure y Presentation.

## Licencia

MIT
