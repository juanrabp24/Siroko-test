# PLAN — Siroko Cart API

## Antes de empezar
Investigué el flujo de un carrito de compra con diagramas de secuencia y casos de
uso para diseñar correctamente la arquitectura antes de escribir código.
Identifiqué cuatro bounded contexts aislados: Catalog, Cart, Order y Payment.

---

## Fase 1 — Diseño de dominio (manual, sin IA)
Diseñé los agregados, VOs y eventos de dominio.
Decisiones clave tomadas aquí:
- Cart es el agregado raíz del contexto Cart. Controla la invariante "no se puede
  modificar un carrito cerrado".
- ProductSnapshot (VO inmutable) desacopla Cart de Catalog: el carrito guarda una
  copia del producto en el momento de añadirlo.
- Payment es un contexto separado de Order para respetar el principio de
  responsabilidad única y permitir que el sistema de pagos evolucione
  independientemente.
- Money siempre en céntimos como int (nunca float).

Escribí a mano: Money, CartId, Quantity, ProductSnapshot, CartItem y Cart con sus
tests. Estos ficheros sirvieron como ejemplo canónico para el resto.

---

## Fase 2 — Contextos Catalog, Order y Payment (IA como pair programmer)

Con el patrón establecido, usé Claude para:
- Generar tests de Catalog, Order y Payment siguiendo mis ejemplos existentes.
- Generar los repositorios Doctrine (interfaces + implementaciones).
- Generar los controllers Symfony.
- Crear la capa Application de Catalog (faltaba).

Cada output de la IA fue revisado antes de aceptarse.

---

## Fase 3 — Infraestructura y configuración

- Instalación de Doctrine ORM 3.x + DoctrineBundle 2.x.
- Clases de entidad Doctrine (`*Entity`) en `Infrastructure/Persistence/` para cada
  agregado, con atributos `#[ORM\Entity]` y `#[ORM\Column]`.
- Los repositorios hacen la traducción manual dominio ↔ entidad Doctrine mediante
  métodos privados `toDomain()` y `serializeItems()`.
- Cada agregado expone `reconstitute()` como factory estática para rehidratarse
  desde persistencia sin pasar por las invariantes del constructor de creación.
- Items de Cart y Order se persisten como columna JSON (`Types::JSON`) dentro de
  la entidad padre — sin tablas secundarias.
- Mapeo en `doctrine.yaml` con `type: attribute` apuntando a los directorios
  `Infrastructure/Persistence/` de cada contexto.
- Configuración de `.env` y `services.yaml`.

---

## Fase 4 — Capa de presentación (IA como autor, desarrollador como revisor)

El front-end fue **generado por Claude Code** en su totalidad. El desarrollador
definió los requisitos funcionales y validó el resultado; no escribió CSS ni JS.

Lo que se construyó en esta fase:

- **`templates/store/index.html.twig`** — página principal: catálogo de productos,
  panel de carrito lateral con animaciones, gestión de items y navegación al
  checkout.
- **`templates/checkout/index.html.twig`** — página de resumen de pedido:
  listado de items del carrito, formulario de dirección de envío y flujo de tres
  llamadas API (checkout cart → crear order → procesar payment) con pantalla de
  confirmación.
- **`StoreController`** — dos rutas `GET /` y `GET /checkout` que sirven los
  templates Twig.
- **`GetAllProductsHandler` + endpoint `GET /products`** — endpoint de listado
  del catálogo consumido por el JS del front.
- **Instalación de TwigBundle** — única dependencia nueva añadida en esta fase.

Criterio de aceptación aplicado por el desarrollador:
- El flujo completo (añadir producto → ver carrito → ir a checkout → confirmar
  pedido) funciona end-to-end contra la API real.
- El código generado respeta la arquitectura hexagonal: los controllers no
  contienen lógica de negocio y los templates no acceden directamente a Doctrine.
- No se introdujeron dependencias de Node, bundlers ni frameworks JS.

---

Esta estructura esta hecha con IA para una mejor visualizacion**

## Estructura de ficheros

```
src/
├── Cart/
│   ├── Domain/Model/         Cart, CartItem, CartId, ProductSnapshot, Quantity, CartCheckedOut
│   ├── Domain/Repository/    CartRepositoryInterface
│   ├── Application/Command/  AddItemToCart(Handler), RemoveItemFromCart(Handler), CheckoutCart(Handler)
│   ├── Application/Query/    GetCart(Handler, Response)
│   └── Infrastructure/
│       ├── Http/             CartController
│       └── Persistence/      CartEntity, DoctrineCartRepository
├── Catalog/
│   └── Infrastructure/
│       └── Persistence/      ProductEntity, DoctrineProductRepository
├── Order/
│   └── Infrastructure/
│       └── Persistence/      OrderEntity, DoctrineOrderRepository
├── Payment/
│   └── Infrastructure/
│       └── Persistence/      PaymentEntity, DoctrinePaymentRepository
└── Shared/Domain/            Money
```
