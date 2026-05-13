# PLAN — Siroko Cart API

## Antes de empezar
Analicé el flujo de un carrito de compra e identifiqué cuatro bounded contexts
aislados: Catalog, Cart, Order y Payment. Diseñé los agregados, VOs y eventos de
dominio antes de escribir una sola línea de código o abrir Claude Code.

La pregunta de diseño más importante fue la frontera entre Cart y Order: ¿cuándo
termina uno y empieza el otro? Decisión: Cart es responsable de la selección de
productos y el checkout. Order nace del evento `CartCheckedOut` y gestiona el
ciclo de vida del pedido. Payment es un tercer contexto independiente que solo
conoce el `orderId` y el importe — no sabe nada de productos ni de carrito.

---

## Fase 1 — Diseño de dominio (manual, sin IA)

Escribí a mano los primeros agregados y VOs del contexto Cart:
`Money`, `CartId`, `Quantity`, `ProductSnapshot`, `CartItem` y `Cart` con sus tests.

Estos ficheros sirvieron como ejemplo canónico para todo lo que vino después.
Decisiones tomadas en esta fase:

- `Cart` es el agregado raíz del contexto Cart. Controla la invariante
  "no se puede modificar un carrito cerrado".
- `ProductSnapshot` (VO inmutable) desacopla Cart de Catalog: el carrito guarda
  una copia del producto en el momento de añadirlo.
- `Payment` es un contexto separado de `Order` para respetar SRP y permitir
  que el sistema de pagos evolucione independientemente.
- `Money` siempre en céntimos como `int` (nunca `float`).

Usé Claude Code puntualmente como reviewer en esta fase: le pasé los VOs ya
escritos y le pedí que detectara violaciones de inmutabilidad o imports de
infraestructura en el dominio. No generó código, solo revisó.

---

## Fase 2 — Contextos Catalog, Order y Payment (IA como pair programmer)

Con el patrón establecido en Fase 1, usé Claude Code para generar los contextos
restantes siguiendo mis ejemplos como referencia.

Claude generó:
- Tests de Catalog, Order y Payment siguiendo el patrón de tests/Cart/Domain.
- Repositorios Doctrine (interfaces + implementaciones) para los cuatro contextos.
- Controllers Symfony para Cart, Order y Payment.
- Capa Application de Catalog (Commands, Queries y Handlers).

Cada output fue revisado antes de aceptarse. Dos correcciones relevantes:
- Se detectó que `Cart.php` importaba `App\Cart\Domain\Money` en lugar de
  `App\Shared\Domain\Money`. Corregido manualmente.
- Se detectó que el repositorio llamaba a `Payment::reconstitute()` que no
  existía. Se añadió el método al agregado diferenciándolo de `create()` —
  `reconstitute()` no dispara eventos de dominio (ver DECISIONS.md entrada 9).

---

## Fase 3 — Infraestructura y configuración

- Instalación de Doctrine ORM 3.x + DoctrineBundle 2.x.
- Clases de entidad Doctrine (`*Entity`) en `Infrastructure/Persistence/` para
  cada agregado, con atributos `#[ORM\Entity]` y `#[ORM\Column]`.
- Los repositorios hacen la traducción manual dominio ↔ entidad Doctrine mediante
  métodos privados `toDomain()` y `serializeItems()`.
- Items de Cart y Order se persisten como columna JSON (`Types::JSON`) dentro de
  la entidad padre — sin tablas secundarias (ver DECISIONS.md entrada 6).
- Mapeo en `doctrine.yaml` con `type: attribute` apuntando a los directorios
  `Infrastructure/Persistence/` de cada contexto.
- Configuración de `.env` y `services.yaml`.

---

## Fase 4 — Capa de presentación (IA como autor, desarrollador como revisor)

El frontend fue generado por Claude Code en su totalidad. El desarrollador
definió los requisitos funcionales y validó el resultado; no escribió CSS ni JS.

Lo que se construyó en esta fase:

- `templates/store/index.html.twig` — página principal: catálogo de productos,
  panel de carrito lateral con animaciones, gestión de items y navegación al
  checkout.
- `templates/checkout/index.html.twig` — página de resumen de pedido: listado
  de items, formulario de dirección de envío y flujo de tres llamadas API
  (checkout cart → crear order → procesar payment) con pantalla de confirmación.
- `StoreController` — dos rutas `GET /` y `GET /checkout` que sirven los templates.
- `GetAllProductsHandler` + endpoint `GET /products` — listado del catálogo
  consumido por el JS del frontend.
- Instalación de TwigBundle — única dependencia nueva añadida en esta fase.

Criterio de aceptación aplicado por el desarrollador:
- El flujo completo (añadir producto → ver carrito → ir a checkout → confirmar
  pedido) funciona end-to-end contra la API real.
- Los controllers no contienen lógica de negocio y los templates no acceden
  directamente a Doctrine.
- No se introdujeron dependencias de Node, bundlers ni frameworks JS.

---

## Dónde ignoré a la IA

- Diseño de bounded contexts y fronteras de agregado: decisión previa, no negociable.
- `Money` como int en céntimos: la IA sugirió float, rechazado.
- `reconstitute()` vs `create()`: la distinción fue criterio propio al detectar
  el bug en el repositorio generado.
- Persistencia de items como JSON: la IA propuso OneToMany, rechazado por romper
  la pureza del dominio (ver DECISIONS.md entrada 6).
