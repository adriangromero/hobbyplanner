# ===========================================
# BASE - Común para dev y prod
# ===========================================
FROM node:20-alpine AS base

# Instalar dependencias básicas
RUN apk add --no-cache git

WORKDIR /app

# ===========================================
# DEVELOPMENT
# ===========================================
FROM base AS development

# Copiar package.json para cachear dependencias
COPY frontend/package*.json ./

# Instalar todas las dependencias (incluye devDependencies)
RUN npm ci

# Exponer puerto de Vite
EXPOSE 5173

# Comando para desarrollo con hot-reload
CMD ["npm", "run", "dev", "--", "--host"]

# ===========================================
# BUILD - Para generar los assets de producción
# ===========================================
FROM base AS build

COPY frontend/package*.json ./

RUN npm ci

COPY frontend/ .

RUN npm run build

# ===========================================
# PRODUCTION - Servir con Nginx
# ===========================================
FROM nginx:alpine AS production

# Copiar configuración de Nginx para SPA
RUN echo 'server { \
    listen 80; \
    root /usr/share/nginx/html; \
    index index.html; \
    location / { \
        try_files $uri $uri/ /index.html; \
    } \
    location /assets { \
        expires 1y; \
        add_header Cache-Control "public, immutable"; \
    } \
}' > /etc/nginx/conf.d/default.conf

# Copiar assets generados
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]