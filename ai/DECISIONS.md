# DECISIONS.md

## 1. Money — moneda fija EUR
La IA generó Money con currency como parámetro variable y validación
ISO 4217. Decidí hardcodear EUR porque la app solo opera en euros.

## 2. Money — int en lugar de float
La IA sugirió float para el importe. Rechazado — se usa int en céntimos
para evitar problemas de punto flotante.

## 3. Sin UUID ni uniqid para IDs
La IA propuso ramsey/uuid y después uniqid(). Decidí prescindir de ambos
para evitar dependencias innecesarias en una prueba de este alcance.

## 4. CartRepositoryInterface — dentro de Domain/Repository/
Pensaba ponerlo en la raíz del contexto pero finalmente lo mantuve dentro
de Domain/Repository/ por coherencia con DDD estricto.