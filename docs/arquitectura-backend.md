# Arquitectura del backend

## Propósito

Esta aplicación administrará propiedades, unidades, inquilinos, contratos y pagos de alquiler mediante la arquitectura:

`Controller → Service → Repository`

La aplicación se organiza por módulos. Actualmente existe el módulo `Alquileres`, ubicado bajo la ruta `/alquileres`.

La ruta raíz, el inicio de sesión, el registro, la recuperación de contraseña, el perfil y el cierre de sesión son recursos globales y no pertenecen al módulo de alquileres.

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
App\Services\Alquileres
App\Repositories\Alquileres
```

Los módulos funcionales deben vivir en carpetas propias dentro de `Services` y `Repositories`.
El prefijo HTTP de cada módulo debe coincidir con su nombre y tener un único controlador principal.

Para alquileres se utiliza:

```text
App\Http\Controllers\AlquileresController
App\Services\Alquileres\*
App\Repositories\Alquileres\*
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

## Organización modular

Las vistas específicas de alquileres estarán dentro de:

```text
resources/views/alquileres/
resources/views/alquileres/formatos/
```

Las rutas del módulo estarán protegidas por autenticación y agrupadas bajo:

```text
/alquileres
```

El módulo expone:

- `/alquileres/dashboard`
- `/alquileres/propiedades`
- `/alquileres/inquilinos`
- `/alquileres/contratos`
- `/alquileres/pagos`
- `/alquileres/formatos/*`

Todas las opciones funcionales se agrupan bajo una entrada `Alquileres` en el menú lateral. Las vistas, servicios, repositorios, controladores y rutas globales permanecerán fuera del módulo cuando correspondan a toda la aplicación.

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

## Dashboard principal

El dashboard principal utiliza el flujo completo `Controller → Service → Repository`.

### Métricas incluidas

- Cantidad de propiedades, unidades, inquilinos y contratos vigentes.
- Contratos próximos a vencer en los siguientes 30 días.
- Contratos vencidos pendientes de atención.
- Porcentaje y cantidad de unidades ocupadas.
- Cobranza programada, cobrada y pendiente del mes actual.
- Ganancia cobrada por propiedad.
- Evolución de ingresos cobrados durante los últimos 12 meses.
- Últimos pagos registrados.

La ruta `/dashboard` devuelve la vista principal. La ruta `/dashboard/datos` conserva la información disponible como JSON.

Todas las métricas se filtran por el usuario autenticado. Las ganancias se calculan a partir de los pagos parciales registrados, vinculados al contrato, unidad y propiedad del propietario.

## Generación de contratos

La generación de contratos sigue la arquitectura `Controller → Service → Repository`.

### Componentes

- `FormatoRepository`: consultas de contratos, datos relacionados, plantillas, formatos personalizados y pagos.
- `ContratoFormatoService`: ownership, validaciones, sustitución de variables, limpieza de HTML seguro, vista previa, transacciones y generación de datos PDF.
- `FormatosController`: recibe requests, obtiene la sesión y delega al servicio.

### Funciones disponibles

- Vista previa del contrato con valores reemplazados.
- Edición y guardado de formato personalizado por contrato.
- Restablecimiento al formato predeterminado.
- Descarga PDF con nombre identificable.
- Descarga y vista previa de recibos con validación de ownership.
- Validación de permisos antes de consultar, editar, guardar o descargar.

### Variables disponibles

El editor admite variables como `[contrato_codigo]`, `[propietario_nombre]`, `[propietario_dni]`, `[propietario_direccion]`, `[propiedad_nombre]`, `[propiedad_direccion]`, `[unidad_nombre]`, `[inquilino_nombre]`, `[inquilino_dni]`, `[meses]`, `[inicio]`, `[fin]`, `[dia_pago]`, `[renta]`, `[renta_texto]`, `[servicios]`, `[servicios_texto]`, `[dia_servicio]`, `[total_mensual]` y `[fecha_actual]`.

### Seguridad documental

Las plantillas se someten a limpieza de etiquetas activas y atributos de eventos. Toda operación de formatos verifica que el contrato o pago pertenezca al usuario autenticado.

## Próximas extensiones

- Interfaces para repositorios cuando sean necesarias.
- Excepciones de dominio específicas.
- Form Requests para metadata HTTP solamente, si se decide validar estructura básica fuera del servicio.
- Registro de paginación, auditoría y soft deletes si el proyecto lo requiere.
