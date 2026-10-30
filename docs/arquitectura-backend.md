# Arquitectura del backend

## Propósito

Esta aplicación administrará propiedades, unidades, inquilinos, contratos y pagos de alquiler mediante la arquitectura:

`Controller → Service → Repository`

## Flujo general

```text
Request HTTP
    ↓
Controller
    ↓
Service
    ↓
Repository
    ↓
Base de datos
```

## Controller

Los controladores serán adaptadores HTTP y deberán:

- Recibir `Request`.
- Obtener datos básicos, como el usuario autenticado.
- Extraer identificadores y datos mínimos enviados por el frontend.
- Invocar servicios mediante inyección de dependencias.
- Devolver JSON para operaciones AJAX/API.
- Mantener vistas solo para las páginas iniciales existentes.
- Traducir excepciones a respuestas JSON.

Los controladores no deben:

- Acceder directamente a `DB`.
- Ejecutar consultas SQL.
- Manipular datos persistentes.
- Administrar transacciones.
- Contener reglas de negocio.
- Realizar validaciones funcionales.

## Service

Los servicios contienen todas las validaciones y reglas de negocio. Además:

- Validan datos obligatorios, formatos, montos y fechas.
- Verifican ownership de propiedades, unidades, inquilinos y contratos.
- Coordinan operaciones entre uno o varios repositorios.
- Ejecutan operaciones dependientes dentro de transacciones.
- Producen errores funcionales mediante `InvalidArgumentException` u excepciones específicas futuras.
- Contienen cálculos de alquiler, contratos, cronogramas y saldos.

Uso esperado de transacciones:

```php
DB::transaction(function () {
    // Operaciones de cabecera y detalle
});
```

El servicio puede llamar repositorios, pero no debe declarar SQL directo.

## Repository

Los repositorios serán responsables de manipular datos y realizar consultas técnicas:

- `select`, `insert`, `update` y `delete`.
- Consultas SQL con filtros por usuario.
- Obtención de registros relacionados.
- Operaciones simples de persistencia.
- Consultas de reportes utilizadas por servicios.

Los repositorios no deben:

- Validar reglas de negocio.
- Calcular estados o importes funcionales.
- Administrar transacciones.
- Acceder al usuario de sesión.
- Decidir si una operación debe aprobarse.

## Ownership y seguridad

Todos los registros funcionales pertenecen al usuario autenticado.

- Una propiedad pertenece al usuario mediante `propietario_id`.
- Un inquilino pertenece al usuario mediante `usuario_id`.
- Las unidades deben pertenecer a una propiedad del usuario.
- Los contratos deben estar relacionados con unidades y propiedades del usuario.
- Los pagos deben pertenecer únicamente a contratos y unidades del usuario.

Los repositorios deben aplicar filtros por usuario en lecturas, modificaciones y eliminaciones. Los servicios deben volver a validar las relaciones cuando una operación combine entidades.

## Transacciones

Las operaciones que modifican dos o más tablas o generan registros relacionados deben ejecutarse dentro de una transacción.

Casos incluidos:

- Propiedad con alta, modificación o eliminación de unidades.
- Eliminación de una propiedad con sus unidades.
- Contrato con generación, reemplazo o eliminación de cronograma de pagos.
- Eliminación de un contrato con sus pagos.
- Operaciones futuras de pagos, cuando sean implementadas.

## Validaciones

Las validaciones estarán siempre en los servicios, no en controladores ni repositorios.

Ejemplos:

- Identificador válido.
- Campos obligatorios presentes.
- Fecha en formato `dd/mm/yyyy`.
- Fecha de inicio anterior o igual a la fecha de fin.
- Duración mayor que cero.
- Importes no negativos.
- Unidad perteneciente al usuario.
- Inquilino perteneciente al usuario.
- Contrato perteneciente al usuario.
- Detalle de unidades completo y coherente.

## Respuestas HTTP

Las operaciones API/Listado/Alta/Modificación/Eliminación devolverán JSON.

Éxito:

```json
{
  "success": true,
  "data": 1
}
```

Error funcional:

```json
{
  "success": false,
  "message": "La unidad seleccionada no pertenece al usuario",
  "error": "La unidad seleccionada no pertenece al usuario"
}
```

Los errores funcionales usan estado HTTP 422. Los errores internos usan 500.

Las rutas que cargan una pantalla HTML continuarán conservando su vista; los datos dinámicos posteriores deberán devolver JSON.

## Convenciones

### Namespaces

```text
App\Http\Controllers
App\Services
App\Repositories
```

### Nombres de clases

- Un controlador por recurso HTTP existente.
- Un servicio por dominio funcional.
- Un repositorio por agregado o tabla principal.

Ejemplos:

```text
PropiedadService
PropiedadRepository
InquilinoService
InquilinoRepository
ContratoService
ContratoRepository
```

### Inyección de dependencias

Los controladores recibirán servicios mediante el constructor. Los servicios recibirán repositorios mediante el constructor. No se usarán llamadas estáticas para repositorios.

## Compatibilidad de APIs

Las rutas existentes se conservarán.

- Se mantienen los endpoints de listado, procesamiento y eliminación.
- Las respuestas exitosas existentes mantienen `success` y `data` cuando corresponde.
- Los nombres e identificadores enviados por el frontend no deben cambiar sin migración coordinada.
- Las consultas existentes deben preservar sus columnas y formatos visibles.

## Generación documental

La generación de PDF se considera una integración de terceros. Se utilizará una biblioteca externa, actualmente basada en DomPDF, sin implementar lógica PDF propia.

La lógica funcional del contrato puede generar datos y contenido, pero la representación PDF quedará delegada al proveedor externo.

## Orden de implementación

1. Propiedades y unidades.
2. Inquilinos.
3. Contratos y cronogramas.
4. Pagos.
5. Módulos restantes con la misma arquitectura.

## Próximas extensiones

- Interfaces para repositorios cuando sean necesarias.
- Excepciones de dominio específicas.
- Form Requests para metadata HTTP solamente, si se decide validar estructura básica fuera del servicio.
- Registro de paginación, auditoría y soft deletes si el proyecto lo requiere.
