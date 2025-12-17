FROM nginx:alpine

# Copiar configuración personalizada
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Crear directorio para logs
RUN mkdir -p /var/log/nginx

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://localhost/health || exit 1

WORKDIR /srv/app

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]