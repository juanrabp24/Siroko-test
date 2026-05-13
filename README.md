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
## Documentación de IA

El proceso de colaboración con IA está documentado en la carpeta [`/ai`](./ai/):
- [`PLAN.md`](./ai/PLAN.md) — plan de trabajo y fases
- [`DECISIONS.md`](./ai/DECISIONS.md) — decisiones tomadas frente a la IA
- [`prompts.md`](./ai/prompts.md) — prompts clave del proceso

---


## OpenAPI Specification

```yaml
openapi: 3.0.3
info:
  title: Siroko Cart API
  version: 1.0.0
  description: API REST de carrito, pedidos, pagos y catálogo de productos.

servers:
  - url: http://localhost:8080
    description: Entorno local (Docker)

paths:

  # ── CATALOG ────────────────────────────────────────────────────────────────

  /products:
    post:
      summary: Crear producto
      tags: [Catalog]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [productId, name, price, stock]
              properties:
                productId: { type: string, example: prod-001 }
                name:      { type: string, example: Maillot Siroko M2 }
                price:     { type: integer, description: "Precio en céntimos (ej: 7990 = 79,90 €)", example: 7990 }
                stock:     { type: integer, example: 50 }
      responses:
        '201': { description: Producto creado }
        '422': { description: Datos inválidos o regla de negocio violada }

  /products/{productId}:
    get:
      summary: Obtener producto por ID
      tags: [Catalog]
      parameters:
        - { name: productId, in: path, required: true, schema: { type: string } }
      responses:
        '200':
          description: Producto encontrado
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ProductResponse'
        '404': { description: Producto no encontrado }

  # ── CART ───────────────────────────────────────────────────────────────────

  /cart/{cartId}:
    get:
      summary: Obtener carrito
      tags: [Cart]
      parameters:
        - { name: cartId, in: path, required: true, schema: { type: string } }
      responses:
        '200':
          description: Carrito encontrado
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CartResponse'
        '404': { description: Carrito no encontrado }

  /cart/{cartId}/items:
    post:
      summary: Añadir item al carrito
      tags: [Cart]
      parameters:
        - { name: cartId, in: path, required: true, schema: { type: string } }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [userId, productId, productName, productPrice, quantity]
              properties:
                userId:       { type: string,  example: user-42 }
                productId:    { type: string,  example: prod-001 }
                productName:  { type: string,  example: Maillot Siroko M2 }
                productPrice: { type: integer, example: 7990 }
                quantity:     { type: integer, example: 2 }
      responses:
        '201': { description: Item añadido }
        '422': { description: Carrito cerrado o datos inválidos }

  /cart/{cartId}/items/{productId}:
    delete:
      summary: Eliminar item del carrito
      tags: [Cart]
      parameters:
        - { name: cartId,    in: path, required: true, schema: { type: string } }
        - { name: productId, in: path, required: true, schema: { type: string } }
      responses:
        '200': { description: Item eliminado }
        '422': { description: Producto no encontrado en el carrito }

  /cart/{cartId}/checkout:
    post:
      summary: Cerrar carrito (checkout)
      tags: [Cart]
      parameters:
        - { name: cartId, in: path, required: true, schema: { type: string } }
      responses:
        '200': { description: Checkout completado }
        '422': { description: Carrito vacío o ya cerrado }

  # ── ORDER ──────────────────────────────────────────────────────────────────

  /orders:
    post:
      summary: Crear pedido desde carrito
      tags: [Order]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [orderId, cartId, userId, street, city, postalCode, country, recipientName]
              properties:
                orderId:       { type: string, example: order-001 }
                cartId:        { type: string, example: cart-001 }
                userId:        { type: string, example: user-42 }
                street:        { type: string, example: "Calle Mayor 1" }
                city:          { type: string, example: Madrid }
                postalCode:    { type: string, example: "28001" }
                country:       { type: string, example: ES }
                recipientName: { type: string, example: "Juan García" }
      responses:
        '201': { description: Pedido creado }
        '422': { description: Carrito no encontrado o sin checkout previo }

  /orders/{orderId}:
    get:
      summary: Obtener pedido
      tags: [Order]
      parameters:
        - { name: orderId, in: path, required: true, schema: { type: string } }
      responses:
        '200':
          description: Pedido encontrado
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/OrderResponse'
        '404': { description: Pedido no encontrado }

  # ── PAYMENT ────────────────────────────────────────────────────────────────

  /payments:
    post:
      summary: Procesar pago
      tags: [Payment]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [paymentId, orderId, amount]
              properties:
                paymentId: { type: string,  example: pay-001 }
                orderId:   { type: string,  example: order-001 }
                amount:    { type: integer, example: 15980 }
      responses:
        '201': { description: Pago procesado y confirmado }
        '422': { description: Datos inválidos }

  /payments/{paymentId}:
    get:
      summary: Obtener pago
      tags: [Payment]
      parameters:
        - { name: paymentId, in: path, required: true, schema: { type: string } }
      responses:
        '200':
          description: Pago encontrado
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/PaymentResponse'
        '404': { description: Pago no encontrado }

components:
  schemas:

    ProductResponse:
      type: object
      properties:
        productId: { type: string }
        name:      { type: string }
        price:     { type: integer, description: Céntimos }
        stock:     { type: integer }
        available: { type: boolean }

    CartResponse:
      type: object
      properties:
        cartId:    { type: string }
        userId:    { type: string }
        isChecked: { type: boolean }
        total:     { type: integer, description: Céntimos }
        items:
          type: array
          items:
            type: object
            properties:
              productId:   { type: string }
              productName: { type: string }
              unitPrice:   { type: integer }
              quantity:    { type: integer }
              total:       { type: integer }

    OrderResponse:
      type: object
      properties:
        orderId:       { type: string }
        userId:        { type: string }
        status:        { type: string, enum: [pending, paid, cancelled] }
        total:         { type: integer }
        street:        { type: string }
        city:          { type: string }
        postalCode:    { type: string }
        country:       { type: string }
        recipientName: { type: string }
        items:
          type: array
          items:
            type: object
            properties:
              productId:   { type: string }
              productName: { type: string }
              unitPrice:   { type: integer }
              quantity:    { type: integer }
              total:       { type: integer }

    PaymentResponse:
      type: object
      properties:
        paymentId: { type: string }
        orderId:   { type: string }
        amount:    { type: integer }
        status:    { type: string, enum: [pending, success, failed] }
```