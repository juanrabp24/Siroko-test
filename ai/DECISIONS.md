# DECISIONS.md

## 1. Money — moneda fija EUR
La IA generó Money con currency como parámetro variable y validación ISO 4217.
Decidí hardcodear EUR porque la app solo opera en euros. Menos complejidad, más
claridad de intención.

## 2. Money — int en lugar de float
La IA sugirió float para el importe. Rechazado. Se usa int en céntimos para
evitar errores de punto flotante en cálculos financieros.

## 3. Sin UUID ni uniqid para IDs
La IA propuso ramsey/uuid y después uniqid(). Decidí prescindir de ambos para
evitar dependencias innecesarias en una prueba de este alcance. Los IDs son
strings libres que el cliente genera (patrón client-generated ID, válido en DDD).

## 4. CartRepositoryInterface dentro de Domain/Repository/
Pensaba ponerlo en la raíz del contexto pero lo mantuve en Domain/Repository/ por
coherencia con DDD estricto: las interfaces de repositorio son un contrato del
dominio, no de la aplicación.

## 5. ProductSnapshot como VO en lugar de referencia a Catalog
Cart no llama a Catalog para obtener el producto. El controller pasa el snapshot
del producto al command AddItemToCart. Esto desacopla Cart de Catalog en tiempo
de ejecución y permite que el precio quede "fijado" en el momento de añadir al
carrito, independientemente de cambios posteriores en Catalog.

## 6. Cart.items y Order.items persistidos como columna JSON
Consideré OneToMany con Doctrine para Cart.items y Order.items. La alternativa
requería añadir IDs sustitutos a CartItem y OrderItem, y/o referencias de vuelta
al agregado raíz — ambas violaciones de pureza de dominio.

**Decisión**: columna `Types::JSON` en la entidad Doctrine (`CartEntity`,
`OrderEntity`). El repositorio serializa los items a array primitivo en `save()`
y los reconstruye en `toDomain()`. Ventajas:
- El dominio no sabe nada de persistencia.
- Cart y Order como agregados compactos (una fila, sin joins).
- Suficiente para la escala de esta prueba.

Desventaja asumida: no se pueden hacer queries SQL directas sobre campos de items.
Para un sistema de producción se usaría un modelo de lectura separado (CQRS Query
side) que proyecte los eventos en tablas relacionales para consultas.

## 7. Repositorio Catalog — findAvailable() en lugar de findAll()
findAll() es un antipatrón en repositorios DDD (carga todo sin intención). El
método específico `findAvailable()` es explícito en la intención de dominio y usa
DQL sobre `p.stock > 0` (campo directo en `ProductEntity`, no embeddable).

## 8. Entidades Doctrine separadas en Infrastructure en lugar de mapear el dominio
La opción inicial era mapear los agregados de dominio directamente con XML o con
atributos Doctrine. Rechazado: mezclaría conceptos de persistencia en el dominio
o requeriría custom types para cada VO. Este cambio se ha realizado por que nunca he trabajado 
con el mapeo de XML de doctrine, desconozco su uso y su alcance

**Decisión**: clases `*Entity` en `Infrastructure/Persistence/` con atributos
`#[ORM\Entity]`/`#[ORM\Column]`. Los repositorios hacen la traducción explícita
dominio ↔ entidad mediante `toDomain()` y `serializeItems()`. Ventajas:
- El dominio queda completamente limpio — ningún import de Doctrine en `Domain/`.
- El contrato de traducción es explícito y testeable.
- Sin custom Doctrine types: los IDs y VOs se almacenan como primitivos
  (string/int/bool) y se reconstruyen con sus factories en `toDomain()`.

## 9. reconstitute() como factory de reconstrucción separada de create()
Los agregados tienen dos formas de instanciarse: `create()` para creación nueva
(lanza eventos de dominio, aplica invariantes de creación) y `reconstitute()` para
recreación desde persistencia (sin eventos, sin invariantes de creación, acepta
el estado tal como está guardado).

Esta separación evita que el repositorio tenga que falsear el estado del agregado
después de crearlo, y hace explícito en el código cuándo se está creando algo nuevo
versus cuándo se está leyendo algo ya existente.


**SPA-light con fetch**: la UI no usa ningún framework JS. Toda la interactividad
se resuelve con `fetch` nativo contra la propia API JSON de Symfony. Esto evita
añadir un bundler o dependencias de Node al stack.

**Checkout como página separada**: en lugar de un modal o paso adicional en el
panel del carrito, el checkout vive en `/checkout?cartId=...`. Razones:
- Permite mostrar el formulario de envío sin saturar el panel lateral.
- El flujo de tres pasos (checkout cart → crear order → procesar payment) queda
  claramente contenido en una sola página con su propia responsabilidad.
- Al volver a la tienda se genera un nuevo `CART_ID` aleatorio, vaciando el
  carrito de forma natural sin necesidad de mutación de estado en el servidor.

**CART_ID generado en cliente**: siguiendo el patrón client-generated ID ya
establecido en el dominio (decisión 3), el carrito se identifica con un ID
aleatorio generado en el navegador al cargar la página.

**TwigBundle instalado a posteriori**: el skeleton inicial no incluía Twig. Se
instaló con `composer require symfony/twig-bundle` cuando fue necesario para
servir los templates. Symfony Flex gestionó la configuración automáticamente.


