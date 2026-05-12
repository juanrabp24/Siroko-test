# Siroko Cart — Prueba Técnica

API REST de carrito de compra construida con **PHP 8.3 + Symfony 7.4** siguiendo
**Arquitectura Hexagonal** y **Domain-Driven Design**.

---

## Stack
Esta estructura esta hecha con IA para una mejor visualizacion**

| Componente | Versión |
|---|---|
| PHP | 8.3 |
| Symfony | 7.4 |
| Doctrine ORM | 3.6 |
| MySQL | 8.4 |
| Nginx | 1.27 |
| Docker Compose | v2 |

---

## Levantar el proyecto

```bash
# 1. Clonar el repositorio
git clone <repo>
cd <repo>

# 2. Crear el fichero de entorno
cp symfony-app/.env.example symfony-app/.env

# 3. Levantar los contenedores
docker compose up -d

# 4. Crear el esquema de base de datos
docker compose exec php bin/console doctrine:schema:create

# 5. Abrir la tienda en el navegador
# http://localhost:8080
```

---

## Ejecutar tests

```bash
docker compose exec php vendor/bin/phpunit --testdox
```

---

## Modelado del dominio

### Bounded contexts

| Contexto | Agregado raíz | Value Objects |
|---|---|---|
| Catalog | Product | ProductId, ProductName, Stock |
| Cart | Cart | CartId, ProductSnapshot, Quantity |
| Order | Order | OrderId, OrderStatus, ShippingAddress |
| Payment | Payment | PaymentId, PaymentStatus |
| Shared | — | Money |

### Decisiones clave
- `ProductSnapshot` congela el precio en el momento de añadir al carrito
- `Payment` es un contexto separado de `Order` — la orden sabe si está pagada pero no cómo
- `Money` usa enteros en céntimos, moneda fija EUR. **Nota**: He establecido el euro como moneda fija para suavizar el alcance de la prueba, 
entiendo que en una tienda internacional se cambie el tipo de moneda en base al pais desde donde navegas via web.
- Los eventos de dominio conectan los contextos: `CartCheckedOut` → crea `Order`, `PaymentConfirmed` → actualiza `Order`

---

## Arquitectura

Esta estructura esta hecha con IA para una mejor visualizacion**
```
src/
├── Catalog/     # Gestión de productos
├── Cart/        # Carrito de compra
├── Order/       # Ciclo de vida del pedido
├── Payment/     # Procesamiento de pagos (desacoplado de Order)
└── Shared/      # SharedKernel: Money y otros VOs transversales
```

Cada contexto sigue `Domain / Application / Infrastructure`.
El dominio no importa nada de Symfony ni Doctrine.
Los repositorios Doctrine usan entidades de infraestructura separadas
en `Infrastructure/Persistence/` que mapean manualmente hacia y desde
los agregados de dominio.

---

## NOTA
Los commits subidos como "fix" son de cosecha propia.