<div align="center">

# 🎨 HobbyPlanner

**Gestión profesional de proyectos de pintura de miniaturas Warhammer**

[![Backend Tests](https://github.com/TU-USUARIO/hobbyplanner/workflows/Backend%20CI/badge.svg)](https://github.com/TU-USUARIO/hobbyplanner/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Symfony](https://img.shields.io/badge/Symfony-7.1-000000.svg?logo=symfony)](https://symfony.com/)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D.svg?logo=vue.js)](https://vuejs.org/)

[🚀 Demo](#) • [📖 Docs](docs/) • [🐛 Issues](issues/)

> **Proyecto Portfolio** - Full-stack con Arquitectura Hexagonal, SOLID y TDD

</div>

---

## 🎯 Sobre el Proyecto

Aplicación web para gestionar proyectos de pintura de miniaturas Warhammer con:

- ⏱️ **Tracking de tiempo** con cronómetro integrado
- 🎨 **Gestión de miniaturas** con progreso automático
- 📊 **Proyectos** con estimación inteligente
- 📈 **Estadísticas** y gráficos de productividad

---

## 🏗️ Stack Tecnológico

| Backend | Frontend | DevOps |
|---------|----------|--------|
| Symfony 7.1 | Vue.js 3 | Docker |
| PHP 8.2+ | TypeScript | GitHub Actions |
| Doctrine ORM | Pinia | MySQL 8.0 |
| JWT Auth | Tailwind CSS | |
| PHPUnit | Vitest | |

---

## 🚀 Quick Start
```bash
# Clonar
git clone https://github.com/TU-USUARIO/hobbyplanner.git
cd hobbyplanner

# Levantar con Docker
docker-compose up -d

# Backend
docker-compose exec backend composer install
docker-compose exec backend php bin/console doctrine:migrations:migrate

# Frontend
docker-compose exec frontend npm install
```

**Acceder:**
- Backend: http://localhost:8000
- Frontend: http://localhost:5173

---

## 🏗️ Arquitectura

Este proyecto implementa **Arquitectura Hexagonal**:
```
Domain Layer (Núcleo - Sin dependencias)
    ↓
Application Layer (Casos de Uso)
    ↓
Infrastructure Layer (Doctrine, APIs externas)
    ↓
Presentation Layer (Controllers)
```

---

## ✨ Características

- **Miniaturas:** CRUD completo, estados, niveles de detalle
- **Proyectos:** Agrupación, estimación, dashboard
- **Sesiones:** Timer integrado, historial
- **Estadísticas:** Gráficos de progreso

---

## 🧪 Testing
```bash
# Backend
docker-compose exec backend php bin/phpunit

# Frontend
docker-compose exec frontend npm run test
```

---

## 📚 Documentación

- [Arquitectura Hexagonal](docs/architecture/hexagonal-architecture.md)
- [Principios SOLID](docs/architecture/solid-principles.md)
- [API Docs](docs/api/README.md)

---

## 🗺️ Roadmap

- [x] Setup inicial
- [ ] Autenticación JWT
- [ ] CRUD Miniaturas
- [ ] CRUD Proyectos
- [ ] Timer de sesiones
- [ ] Estadísticas

---

## 📄 Licencia

MIT License - ver [LICENSE](LICENSE)

---

## 👤 Autor

**Tu Nombre**
- GitHub: [@TU-USUARIO](https://github.com/TU-USUARIO)
- LinkedIn: [Tu Perfil](https://linkedin.com/in/tu-perfil)

---

<div align="center">

⭐ **Dale una estrella si te gusta el proyecto** ⭐

</div>
