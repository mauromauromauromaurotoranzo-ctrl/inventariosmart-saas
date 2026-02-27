#!/bin/bash

# Script de despliegue para InventarioSmart SaaS
# Soporta entornos: desarrollo, produccion

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

ENV=${1:-desarrollo}

if [ "$ENV" != "desarrollo" ] && [ "$ENV" != "produccion" ]; then
    echo -e "${RED}Error: Entorno no válido${NC}"
    echo "Uso: $0 [desarrollo|produccion]"
    exit 1
fi

echo -e "${BLUE}🚀 Desplegando InventarioSmart SaaS - $ENV${NC}"
echo ""

# Verificar archivos necesarios
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️  Archivo .env no encontrado${NC}"
    echo "Creando desde .env.example..."
    cp .env.example .env
    echo -e "${RED}❗ IMPORTANTE: Edita el archivo .env con tus configuraciones${NC}"
    exit 1
fi

# Determinar archivos docker-compose
if [ "$ENV" = "produccion" ]; then
    COMPOSE_FILES="-f docker-compose.yml -f docker-compose.prod.yml"
    echo -e "${YELLOW}🔒 Modo Producción${NC}"
else
    COMPOSE_FILES="-f docker-compose.yml -f docker-compose.override.yml"
    echo -e "${GREEN}🔧 Modo Desarrollo${NC}"
fi

# Función para ejecutar comandos docker
docker_exec() {
    docker-compose $COMPOSE_FILES exec -T app "$@"
}

echo ""
echo -e "${BLUE}📦 Paso 1/8: Construyendo contenedores...${NC}"
docker-compose $COMPOSE_FILES build --no-cache

echo ""
echo -e "${BLUE}🚀 Paso 2/8: Iniciando servicios...${NC}"
docker-compose $COMPOSE_FILES up -d

# Esperar a que la base de datos esté lista
echo ""
echo -e "${BLUE}⏳ Paso 3/8: Esperando base de datos...${NC}"
sleep 10

# Verificar conexión a BD
MAX_RETRIES=30
RETRY_COUNT=0
while ! docker-compose $COMPOSE_FILES exec -T db mysqladmin ping -h localhost -u root -p"${DB_PASSWORD:-root}" --silent 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo -e "${RED}❌ Timeout esperando base de datos${NC}"
        exit 1
    fi
    echo "Intento $RETRY_COUNT/$MAX_RETRIES..."
    sleep 2
done
echo -e "${GREEN}✅ Base de datos lista${NC}"

echo ""
echo -e "${BLUE}📥 Paso 4/8: Instalando dependencias...${NC}"
docker_exec composer install --no-interaction --prefer-dist --optimize-autoloader

echo ""
echo -e "${BLUE}🔑 Paso 5/8: Configurando aplicación...${NC}"

# Generar key si no existe
if ! grep -q "APP_KEY=base64" .env; then
    docker_exec php artisan key:generate --force
    echo -e "${GREEN}✅ APP_KEY generado${NC}"
fi

# Crear base de datos landlord si no existe
docker-compose $COMPOSE_FILES exec -T db mysql -u root -p"${DB_PASSWORD:-root}" -e "CREATE DATABASE IF NOT EXISTS ${DB_DATABASE:-inventario_landlord} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

echo ""
echo -e "${BLUE}🗄️  Paso 6/8: Ejecutando migraciones...${NC}"
echo "   → Migraciones del sistema (landlord)..."
docker_exec php artisan migrate --path=database/migrations/landlord --force
echo "   → Migraciones de tenants..."
docker_exec php artisan migrate --force

echo ""
echo -e "${BLUE}🌱 Paso 7/8: Ejecutando seeders...${NC}"
docker_exec php artisan db:seed --force --class=LandlordSeeder 2>/dev/null || echo "Seeders opcionales no encontrados"

echo ""
echo -e "${BLUE}⚡ Paso 8/8: Optimizando...${NC}"
docker_exec php artisan config:cache
docker_exec php artisan route:cache
docker_exec php artisan view:cache

# Permisos
docker-compose $COMPOSE_FILES exec -T app chown -R www:www storage bootstrap/cache 2>/dev/null || true
docker-compose $COMPOSE_FILES exec -T app chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo -e "${GREEN}✅ Despliegue completado!${NC}"
echo ""

if [ "$ENV" = "produccion" ]; then
    echo "📋 Información de acceso:"
    echo "   Landing: http://localhost:8000"
    echo ""
    echo "🎯 Próximos pasos:"
    echo "   1. Configurar DNS wildcard (*.tudominio.com)"
    echo "   2. Configurar SSL/TLS"
    echo "   3. Crear tu primer tenant:"
    echo "      ./crear-tenant.sh miempresa 'Mi Empresa' admin@miempresa.com password123"
    echo ""
    echo "📖 Documentación completa en README.md"
else
    echo "📋 Información de acceso:"
    echo "   Landing: http://localhost:8000"
    echo "   Mailpit: http://localhost:8025"
    echo ""
    echo "🎯 Para crear un tenant de prueba:"
    echo "   ./crear-tenant.sh demo 'Demo Company' admin@demo.com password123"
fi

echo ""
echo -e "${BLUE}🚀 InventarioSmart SaaS está listo!${NC}"
