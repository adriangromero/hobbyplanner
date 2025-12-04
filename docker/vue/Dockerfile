FROM node:20-alpine

# Instalar dependencias básicas
RUN apk add --no-cache git

# Crear directorio de trabajo
WORKDIR /app

# Copiar package.json si existe (para cachear dependencias)
COPY frontend/package*.json ./

# Instalar dependencias
RUN npm install

# Exponer puerto de Vite
EXPOSE 5173

# Comando por defecto
CMD ["npm", "run", "dev", "--", "--host"]