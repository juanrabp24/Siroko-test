# Prompts clave usados con Claude Code

Este fichero documenta los prompts más relevantes de la sesión de pair programming
con IA, como referencia del proceso y para mostrar cómo se dirigió la colaboración.

---

## 1. Revisión del diseño de dominio (Claude como reviewer)

```
Revisa estos Value Objects y dime si hay alguna violación de inmutabilidad,
algún setter que no debería estar, o algún import de infraestructura que haya
colado en el dominio. No generes código, solo revisa y señala problemas.

[adjunto: Money.php, CartId.php, ProductSnapshot.php, CartItem.php, Cart.php]
```

**Resultado**: detectó que `CartItem` tenía un método que mutaba el estado
internamente. Corregido antes de continuar.
**Criterio aplicado**: se usó como revisor, no como diseñador. El diseño ya
estaba hecho — Claude solo buscó errores.

---

## 2. Generación de tests de Catalog

```
Genera los tests para el contexto de Catalog siguiendo exactamente el mismo
patrón que los tests que ya existen en tests/Cart/Domain/. Las clases a testear
son Product, ProductId, ProductName y Stock. Convenciones: declare(strict_types=1),
namespace App\Tests\Catalog\Domain\Model, métodos en camelCase, testear
comportamiento no getters, helpers privados para evitar repetición.
```

**Resultado**: 4 ficheros de test en tests/Catalog/Domain/.
**Revisión**: estructura y casos de prueba validados manualmente antes de aceptar.

---

## 3. Generación de tests de Order y Payment

```
Genera los tests para el contexto Order siguiendo exactamente el mismo patrón
que los tests en tests/Cart/Domain/.

Las clases a testear son: OrderId, OrderStatus, ShippingAddress, OrderItem, Order.

Convenciones:
- declare(strict_types=1) en todos los ficheros
- Namespace: App\Tests\Order\Domain\Model
- Nombres de métodos en camelCase: testCreacionValida()
- Helpers privados para evitar repetición
- Testear comportamiento y eventos de dominio, no getters
- Para Order: testear pay(), cancel(), total() y pullDomainEvents()
```

**Resultado**: 5 ficheros para Order, 3 para Payment. 64 tests en total, todos verdes.
**Corrección detectada**: `fail()` en Payment no emitía eventos — comportamiento
correcto, documentado con `testFailNoEmiteEventos()`.

---

## 4. Repositorios Doctrine

```
Revisa, mejora y simplifica los repositorios Doctrine para los cuatro contextos (Cart, Catalog, Order,
Payment). Cada repositorio implementa su interfaz de Domain/Repository/. Métodos:
findById, save y los específicos de cada interfaz. Sin lógica de negocio en los
repositorios. La traducción dominio-entidad va en métodos privados toDomain() y
serializeItems(). Los agregados exponen reconstitute() para rehidratarse desde
persistencia sin disparar eventos.
```

**Resultado**: 4 repositorios + `ProductRepositoryInterface` revisados.
**Bug detectado**: `Cart.php` importaba `App\Cart\Domain\Money` en lugar de
`App\Shared\Domain\Money`. Corregido manualmente.
**Bug detectado**: el repositorio llamaba a `Payment::reconstitute()` que no
existía. Se añadió el método con criterio propio (ver DECISIONS.md entrada 9).

---

## 5. Controllers Symfony

```
En base a los controllers para Cart, Order, genera Controllers delgados en Payment y catalog.
Valida input HTTP → instanciar Command/Query → despachar via handler →
devolver JsonResponse. Sin instanciar objetos de dominio directamente en el
controller. Sin lógica de negocio. Los handlers se inyectan por constructor.
Añade también los bindings necesarios en services.yaml.
```

**Resultado**: 2 controllers. Donde los endpoints quedaron registrados y funcionales.
**Revisión**: se comprobó que ningún controller accedía directamente a repositorios
ni instanciaba agregados — toda la lógica pasa por los handlers.

---

## Notas sobre el proceso

- Todos los prompts incluían ejemplos existentes como referencia de patrón.
- La IA nunca tomó decisiones de diseño: los bounded contexts, los agregados y
  las invariantes fueron definidos antes de abrir Claude Code.
- Cuando la IA sugirió `float` para Money o `ramsey/uuid` para IDs, fue rechazado
  explícitamente y documentado en DECISIONS.md.
- Los tests se ejecutaron tras cada generación para validar el output antes de
  continuar con la siguiente fase.
- El ratio aproximado: ~60% código escrito a mano (dominio Cart completo,
  correcciones de bugs, decisiones de persistencia), ~40% generado por IA y
  revisado.