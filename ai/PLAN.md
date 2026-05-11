# PLAN
## Antes de empezar
Intenté averiguar como funciona bien un carrito de la compra, pregunté a la IA por diagramas de flujo, y flujo de funcionamiento, pues habia trabajado en carritos pero no desarrollado uno desde 0, para poder diseñar
la arquitectura correctamente

## Fase 1 — Diseño de dominio (sin IA)
Diseño los bounded contexts, agregados y value objects antes
de escribir una línea de código. Identifiqué Cart como agregado
raíz, ProductSnapshot como VO inmutable y Payment como contexto
separado de Order.

## Fase 2 — Código base (yo solo)
He escrito a mano Money, CartId, Quantity, ProductSnapshot, CartItem
y Cart con sus tests. Estos ficheros sirvieron como referencia
de patrón para la IA.

## Fase 3 — Contexto Catalog (IA como pair)
Con el patrón establecido, usé la IA para generar los tests de
Catalog siguiendo mis ejemplos. Revisé y corregí el output.

## Uso de IA
- CLAUDE.md: generado con IA, revisado y corregido por mí
- Tests de Catalog: generados con IA siguiendo mis patrones
- Código de dominio de Cart y Shared: escrito por mí