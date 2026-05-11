# CLAUDE.md

Este fichero proporciona contexto y reglas a Claude Code cuando trabaja con este repositorio.

---

## Stack

- **PHP 8.3** + **Symfony 7.4** (MicroKernelTrait skeleton)
- **MySQL 8.4** — credenciales: `symfony/symfony`, base de datos: `symfony`
- **Nginx 1.27** como reverse proxy en el puerto 8080
- Todos los servicios corren con Docker Compose

---

## Levantar el proyecto

```bash
# Arrancar todos los servicios
docker compose up -d

# La app está disponible en http://localhost:8080

# Abrir shell en el contenedor PHP (ejecutar aquí todos los comandos PHP/Symfony/Composer)
docker compose exec php bash

# Consola de Symfony
docker compose exec php bin/console <comando>

# Composer
docker compose exec php composer <comando>

# Ejecutar tests
docker compose exec php vendor/bin/phpunit
```

---

## Arquitectura

La app Symfony se aloja en `symfony-app/`. El volumen Docker la monta en `/var/www/html`.

Este proyecto sigue **Arquitectura Hexagonal (Puertos y Adaptadores)** y **Domain-Driven Design**.
El dominio debe permanecer completamente desacoplado de Symfony. Sin imports de Symfony ni Doctrine dentro de `Domain/`.

### Bounded contexts

```
src/
├── Catalog/          # Gestión de productos
├── Cart/             # Carrito de compra
├── Order/            # Ciclo de vida del pedido
├── Payment/          # Procesamiento de pagos (aislado de Order)
└── Shared/           # SharedKernel: Money y otros VOs transversales
```

Cada contexto sigue esta estructura interna:

```
<Contexto>/
├── Domain/
│   ├── Model/        # Agregados, Entidades, Value Objects
│   ├── Repository/   # Interfaces de repositorio (puertos) — sin Doctrine aquí
│   └── Event/        # Eventos de dominio
├── Application/
│   ├── Command/      # Pares Command + CommandHandler
│   └── Query/        # Pares Query + QueryHandler
└── Infrastructure/
    ├── Persistence/  # Implementaciones Doctrine de los repositorios
    └── Http/         # Controllers de Symfony
```

---

## Reglas de dominio — leer antes de escribir cualquier código

Estas reglas son innegociables. Si una sugerencia las viola, recházala.

### Value Objects
- Siempre `final` e inmutables — sin setters, nunca.
- Constructor `private`. Instanciación solo mediante factory estática con nombre: `NombreClase::create(...)`.
- Deben implementar `equals(self $other): bool`.
- Sin campo `id` — los Value Objects no tienen identidad.
- Se persisten como columnas embebidas en la tabla del agregado padre, nunca como tabla separada.


### Agregados
- Siempre `final`.
- Constructor privado, instanciación mediante factory estática: `NombreClase::create(...)`.
- Todos los cambios de estado pasan por métodos de dominio públicos (`addItem`, `checkout`, etc.).
- Los métodos de dominio lanzan eventos de dominio — se almacenan internamente y se exponen con `pullDomainEvents()`.
- Sin lógica de negocio en los handlers de Application. Los handlers solo orquestan: obtener → llamar método de dominio → persistir → publicar eventos.



### Interfaces de repositorio
- Viven en `Domain/Repository/`, nunca en Infrastructure.
- Sin Doctrine, sin detalles de persistencia — interfaces PHP puras.
- Los nombres de métodos reflejan intención de dominio, no SQL: `findById`, `findActiveByUserId`, `save`.


### CQRS
- Una clase por Command, una por Query, un handler por command/query.
- Los Commands devuelven `void`. Las Queries devuelven un DTO o escalar — nunca un objeto de dominio.
- Los handlers viven en `Application/Command/` o `Application/Query/`.
- Sin lógica de dominio en handlers. Sin llamadas a repositorios desde controllers.

### Eventos de dominio
- Nombrados en pasado: `CartCheckedOut`, `OrderCreated`, `PaymentConfirmed`.
- Inmutables, solo contienen valores primitivos o IDs.
- Flujo: `Cart::checkout()` lanza `CartCheckedOut` → el handler lo publica → el contexto `Order` escucha y crea el pedido.

### Money
- **Siempre almacenado y pasado como enteros en céntimos** (ej: 12,50€ = `1250`).
- **Nunca usar `float` para dinero** — será rechazado en revisión de código.
- Moneda como string ISO 4217: `'EUR'`, `'USD'`.
- `Money` vive en `Shared/Domain/ValueObject/Money.php` y se comparte entre contextos.

---

## Reglas de integración con Symfony

- Los controllers son adaptadores delgados: validar input HTTP → despachar Command/Query → devolver respuesta JSON.
- Los controllers no deben instanciar objetos de dominio directamente.
- Sin lógica de negocio en controllers, nunca.
- Usar atributos `#[Route]`. Los controllers viven en `Infrastructure/Http/`.
- Los servicios se autowirean. Las implementaciones de repositorios se vinculan a sus interfaces en `config/services.yaml`.



---

## Reglas de Doctrine

- Mapeo mediante XML o atributos PHP — cualquiera está bien, pero coherente dentro de un contexto.
- Los agregados mapean a su propia tabla. Los Value Objects mapean como `@Embedded` (sin tabla separada, sin id).
- Sin anotaciones ni imports de Doctrine dentro de `Domain/`. Los ficheros de mapeo van en `Infrastructure/Persistence/`.

---

## Reglas de testing

- Los tests unitarios cubren lógica de dominio: agregados, value objects, servicios de dominio.
- Una clase de test por clase de dominio. El fichero de test refleja la ruta de src bajo `tests/`.
- Los tests deben ser significativos. Un test que solo comprueba que un getter devuelve lo que recibió el constructor no es aceptable.
- Testear el comportamiento, no la implementación: llamar métodos de dominio, verificar estado y eventos lanzados.
- Sin mocks de objetos de dominio — solo mockear infraestructura (repositorios, servicios externos).


---

## Comandos útiles de Symfony

```bash
# Listar todas las rutas
docker compose exec php bin/console debug:router

# Limpiar caché
docker compose exec php bin/console cache:clear

# Listar todos los servicios
docker compose exec php bin/console debug:container

# Ejecutar tests
docker compose exec php vendor/bin/phpunit --testdox
```

---

## Qué hacer cuando haya dudas

- Si una decisión de diseño no está cubierta aquí, aplicar principios DDD: mantener el dominio puro, empujar los detalles de infraestructura a la capa Infrastructure.
- Si la IA sugiere usar `float` para dinero, un setter en un VO, o Doctrine en el dominio — rechazarlo y documentar la decisión en `/ai/DECISIONS.md`.
- Ante dudas sobre límites de agregado, preguntarse: "¿rompería una invariante de negocio hacerlo fuera del agregado?" Si la respuesta es sí, pertenece dentro.