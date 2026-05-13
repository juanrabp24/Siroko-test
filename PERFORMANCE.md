# Performance — Siroko Cart API

Mediciones realizadas en entorno local con Docker Compose (PHP-FPM + Nginx + MySQL).  
Herramienta: `curl` con `-w "%{time_total}\n"`. Sin caché de OPcache calentada entre reinicios.

---

## Resultados

### GET /products — Listado de productos

| Petición | Tiempo (s) |
|----------|-----------|
| 1 (cold) | 0.0199 |
| 2        | 0.0089 |
| 3        | 0.0080 |
| 4        | 0.0072 |
| 5        | 0.0084 |
| 6        | 0.0090 |
| 7        | 0.0085 |
| 8        | 0.0077 |
| 9        | 0.0075 |
| 10       | 0.0072 |

**Media (sin cold start):** ~8.0 ms  
**Cold start:** 19.9 ms (primera petición tras arranque del contenedor, OPcache aún fría)

---

### POST /cart/{cartId}/items — Añadir ítem al carrito

| Petición | Tiempo (s) |
|----------|-----------|
| 1        | 0.0063 |
| 2        | 0.0053 |
| 3        | 0.0035 |
| 4        | 0.0033 |
| 5        | 0.0035 |
| 6        | 0.0033 |
| 7        | 0.0033 |
| 8        | 0.0038 |
| 9        | 0.0029 |
| 10       | 0.0030 |

**Media:** ~3.5 ms

---

### GET /cart/{cartId} — Obtener contenido del carrito

| Petición | Tiempo (s) |
|----------|-----------|
| 1        | 0.0061 |
| 2        | 0.0051 |
| 3        | 0.0047 |
| 4        | 0.0051 |
| 5        | 0.0051 |
| 6        | 0.0051 |
| 7        | 0.0046 |
| 8        | 0.0069 |
| 9        | 0.0046 |
| 10       | 0.0047 |

**Media:** ~5.2 ms

---

### POST /cart/{cartId}/checkout — Proceso de checkout

| Petición | Tiempo (s) |
|----------|-----------|
| 1        | 0.0057 |
| 2        | 0.0055 |
| 3        | 0.0051 |
| 4        | 0.0055 |
| 5        | 0.0056 |

**Media:** ~5.5 ms

---

## Resumen

| Endpoint                  | Media (ms) | Observación |
|---------------------------|-----------|-------------|
| GET /products             | ~8.0 ms   | Cold start a 19.9 ms por OPcache |
| POST /cart/.../items      | ~3.5 ms   | Escritura más rápida que lectura agregada |
| GET /cart/{cartId}        | ~5.2 ms   | Hidratación del agregado Cart desde MySQL |
| POST /cart/.../checkout   | ~5.5 ms   | Escritura + evento de dominio + creación de Order |

Todos los endpoints responden **por debajo de 20 ms** en caliente, lo que es consistente con una carga de trabajo de e-commerce de bajo-medio volumen sobre un stack containerizado en local.

---

## Decisiones que afectan al rendimiento

### 1. ProductSnapshot en lugar de JOIN a productos

Al añadir un ítem al carrito, se congela un `ProductSnapshot` con nombre y precio en el momento de la operación. Esto elimina el JOIN entre `cart_items` y `products` en cada lectura del carrito, a cambio de denormalización controlada.  
**Impacto:** La lectura de `GET /cart/{cartId}` no depende del contexto Catalog en absoluto.

### 2. Enteros en céntimos para Money

`Money` almacena el importe como `INT` en base de datos (céntimos). Evita columnas `DECIMAL` y la aritmética de punto flotante, con mejor rendimiento en sumas y comparaciones a nivel de MySQL.

### 3. Mapeo manual Domain ↔ Infrastructure

Los repositorios Doctrine usan entidades de infraestructura separadas que mapean manualmente hacia y desde los agregados de dominio. Esto añade una pequeña sobrecarga de conversión (~0.1-0.3 ms estimado) pero mantiene el dominio completamente desacoplado de Doctrine, evitando proxies lazy que podrían disparar N+1 queries inadvertidamente.

### 4. Cold start de OPcache

La primera petición tras arrancar el contenedor es ~2.5x más lenta (19.9 ms vs 8 ms). En producción esto se mitigaría con `opcache.preload` apuntando a los archivos críticos del dominio. En este entorno de prueba se ha dejado sin configurar para mantener la honestidad de las métricas.

---

## Entorno de medición

- **CPU:** Local (Docker Desktop)
- **Herramienta:** `curl -w "%{time_total}"`
- **Peticiones por endpoint:** 10 (5 para checkout)
- **Concurrencia:** 1 (secuencial)
- **Base de datos:** MySQL 8.4 en contenedor, sin datos precargados masivos
- **PHP:** 8.3 con PHP-FPM, OPcache activo tras warm-up
