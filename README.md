# test-manager-library-3413974-tool — Sembrador de Demostración

Herramienta de aprovisionamiento de datos para la feria de presentación de **Manager Library** (Ficha ADSO 3413974 — T-21).

## Propósito

Poblar el catálogo y operaciones del sistema a través de la API REST o comandos CLI de contenedor con datos verosímiles:
- **$\ge$ 40 libros** distribuidos en las 5 categorías oficiales (`Novela`, `Ciencia`, `Tecnología`, `Historia`, `Infantil`), con ISBN-13 válidos y existencias controladas.
- **10 socios** con nombres y documentos de identidad.
- **15 préstamos** con líneas asociadas, incluyendo **3 préstamos con líneas vencidas** para permitir la demostración inmediata del endpoint `/loans/overdue` y el reporte `/reports/most-borrowed`.

## Formas de Ejecución

### Opción 1: Mediante la API con Docker (Recomendado)
Desde el repositorio de la API o la infraestructura:
```bash
docker compose run --rm test php artisan library:demo-seed
```

### Opción 2: Script independiente mediante HTTP (`seed.php`)
Requiere que el servicio `api` esté corriendo en el puerto 8000:
```bash
# Ejecución directa con PHP CLI
API_URL=http://localhost:8000/api/v1 php seed.php

# O mediante contenedor efímero
docker run --rm --network host -v "${PWD}:/app" -w /app php:8.2-cli php seed.php
```

## Verificación de Criterios de Aceptación (T-21)
Tras la ejecución, verifique con la API:
1. `GET /api/v1/loans/overdue` devuelve al menos 3 líneas de libros vencidos con sus días de retraso.
2. `GET /api/v1/reports/most-borrowed?from=2026-01-01&to=2026-12-31` devuelve filas consolidadas por libro y categoría congelada.
