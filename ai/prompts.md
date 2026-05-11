# Prompts clave usados con Claude

Este fichero documenta los prompts más relevantes de la sesión de pair programming
con IA, como referencia del proceso y para mostrar cómo se dirigió la colaboración.

---

## 1. Generación de tests de Catalog

```
Genera los tests para el contexto de Catalog siguiendo exactamente el mismo patrón
que los tests que ya existen en tests/Cart/Domain. Las clases a testear son [lista].
```

**Resultado**: 4 ficheros de test en tests/Catalog/Domain/.
**Revisión**: estructura y casos de prueba validados manualmente.

---

## 2. Generación de tests de Order

```
Genera los tests para el contexto Order siguiendo exactamente el mismo patrón que
los tests en tests/Cart/Domain/.

Las clases a testear son: OrderId, OrderStatus, ShippingAddress, OrderItem, Order.

Convenciones:
- declare(strict_types=1) en todos los ficheros
- Namespace: App\Tests\Order\Domain\Model
- Nombres de métodos en camelCase: testCreacionValida()
- Sin "LanzaExcepcion" en los nombres
- Helpers privados para evitar repetición
- Testear comportamiento y eventos de dominio, no getters
- Para Order: testear pay(), cancel(), total() y pullDomainEvents()
```

**Resultado**: 5 ficheros de test, 40 tests, todos verdes.

---

## 3. Generación de tests de Payment

```
Genera los tests para el contexto Payment siguiendo exactamente el mismo patrón
[...] Para Payment: testear confirm(), fail() y pullDomainEvents()
```

**Resultado**: 3 ficheros, 24 tests. Se detectó que fail() no emite eventos
(testFailNoEmiteEventos), comportamiento correcto documentado.

---

## 4. Repositorios Doctrine

```
Genera los repositorios Doctrine para los cuatro contextos [...] Métodos: findById,
save y los específicos de cada interfaz. Sin lógica de negocio.
```

**Resultado**: 4 repositorios + interfaz ProductRepositoryInterface creada (faltaba).
Se detectó y corrigió un bug preexistente: Cart.php importaba App\Cart\Domain\Money
en lugar de App\Shared\Domain\Money.

**Revisión**: Se detectó que el agente usaba `Payment::reconstitute()`
que no existía. Se añadió el método al agregado diferenciándolo de
`create()` — reconstitute() no dispara eventos de dominio.

---

## 5. Controllers Symfony

```
Genera los controllers Symfony [...] Controllers delgados: validar input HTTP →
instanciar Command/Query → devolver JsonResponse. Sin instanciar objetos de dominio
directamente.
```

**Resultado**: 4 controllers + capa Application de Catalog generada. Se añadieron
los bindings en services.yaml y los 10 endpoints quedaron registrados.

---

## Notas sobre el proceso

- Todos los prompts incluían ejemplos existentes como referencia de patrón.
- La IA nunca tomó decisiones de diseño por su cuenta: los bounded contexts,
  los agregados y las invariantes fueron definidos previamente.
- Cuando la IA sugirió float para Money o UUID para IDs, fue rechazado
  explícitamente y documentado en DECISIONS.md.
- Los tests se ejecutaron tras cada generación para validar el output.
