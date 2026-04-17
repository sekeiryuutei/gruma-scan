# Manual de Usuario — GRUMALOG Scan

> **Fecha de generación:** 2026-04-17  
> **Versión del documento:** 1.0  
> **Alcance:** Este manual describe el uso operativo del sistema GRUMALOG Scan tal como se desprende de su código fuente y flujos implementados. Toda información marcada `Pendiente de validación` debe ser confirmada con el equipo de operaciones antes de entregar al usuario final.

---

## Tabla de contenidos

1. [Objetivo del sistema](#1-objetivo-del-sistema)
2. [Público objetivo](#2-público-objetivo)
3. [Requisitos de uso](#3-requisitos-de-uso)
4. [Acceso al sistema](#4-acceso-al-sistema)
5. [Primer ingreso](#5-primer-ingreso)
6. [Navegación general](#6-navegación-general)
7. [Módulos principales](#7-módulos-principales)
8. [Flujos operativos paso a paso](#8-flujos-operativos-paso-a-paso)
9. [Errores comunes](#9-errores-comunes)
10. [Preguntas frecuentes](#10-preguntas-frecuentes)
11. [Buenas prácticas](#11-buenas-prácticas)
12. [Soporte y contacto](#12-soporte-y-contacto)
13. [Glosario](#13-glosario)

---

## 1. Objetivo del sistema

GRUMALOG Scan es el sistema de **conteo físico y auditoría de inventarios en bodega** de Grupo Mayorista S.A. Permite:

- Marcar ubicaciones físicas de almacén con stickers identificadores.
- Registrar el conteo de unidades por ubicación, usando lectura de códigos de barras o ingreso manual.
- Auditar los conteos comparando el resultado manual contra el registro del sistema.
- Detectar diferencias entre el inventario físico y el ERP SIESA.

---

## 2. Público objetivo

| Rol | Responsabilidad en el sistema |
|---|---|
| **Almacenista / Operario** | Usa stickers, realiza conteos de mercancía en bodega |
| **Auditor de inventario** | Valida conteos terminados, registra auditoría manual |
| **Supervisor / Jefe de bodega** | Revisa resultados, detecta diferencias, aprueba cierres |
| **Administrador del sistema** | Gestiona usuarios, estados, marcaciones y configuración |

---

## 3. Requisitos de uso

### Del dispositivo del usuario

- Navegador web moderno: Google Chrome (recomendado), Firefox, Edge.
- Conexión a la red interna de la empresa (LAN o VPN).
- Para lectura de códigos de barras: escáner USB o Bluetooth conectado al equipo.

### Del sistema

- Usuario activo con permiso `grumascan` habilitado (lo asigna el administrador).
- Contraseña asignada por el administrador del sistema.

> **Nota:** El sistema no es accesible desde Internet. Requiere estar conectado a la red corporativa.

---

## 4. Acceso al sistema

**URL de acceso:** `http://[servidor]/grumalog-scan/web/` *(Pendiente de validación: confirmar URL exacta de producción con el equipo de infraestructura)*

La página inicial redirige automáticamente al formulario de inicio de sesión si el usuario no está autenticado.

---

## 5. Primer ingreso

1. Abrir el navegador y dirigirse a la URL del sistema.
2. Ingresar el **nombre de usuario** y la **contraseña** proporcionados por el administrador.
3. Hacer clic en **"Iniciar sesión"**.
4. Si las credenciales son correctas y el usuario tiene permiso `grumascan`, el sistema muestra el **Dashboard principal**.
5. Si aparece el mensaje *"Acceso denegado"* o se redirige nuevamente al login, contactar al administrador para verificar el permiso `grumascan` en la cuenta.

> **Importante:** Si es el primer acceso, cambiar la contraseña de inmediato. *(Pendiente de validación: confirmar si existe flujo de cambio de contraseña en el sistema actual)*

---

## 6. Navegación general

El sistema cuenta con un menú superior (barra de navegación Bootstrap) y un **Dashboard** con accesos directos a los módulos principales.

### Dashboard

Al ingresar, se presentan tarjetas de acceso rápido a:

- **Marcaciones** — Administrar stickers/marcaciones de bodega.
- **Conteos** — Registrar y gestionar sesiones de conteo.
- **Conteo manual** — Registrar auditorías manuales.
- **Estados** — Gestión de estados de conteo *(uso administrativo)*.

### Menú superior

- **Inicio** — Regresa al Dashboard.
- **Cerrar sesión** — Cierra la sesión activa de forma segura.

---

## 7. Módulos principales

### 7.1 Marcaciones (Stickers)

Gestiona los **puntos físicos de conteo** dentro de la bodega. Cada marcación representa una ubicación (sección, pasillo, estante) identificada con un sticker impreso.

| Campo | Descripción |
|---|---|
| Bodega | Almacén al que pertenece la marcación |
| Ubicación | Descripción de la posición física |
| Sección | Subdivisión dentro de la bodega |

### 7.2 Conteos

Registra una **sesión de conteo** asociada a una marcación específica. Un conteo agrupa todos los items escaneados en una ubicación durante una toma de inventario.

| Campo | Descripción |
|---|---|
| Marcación | Punto físico que se está contando |
| Estado | Estado actual del conteo (Abierto, Terminado, etc.) |
| Total registros | Cantidad de líneas/items registrados |
| Total unidades | Suma de unidades contadas |

### 7.3 Detalle de Conteo

Cada **item escaneado** dentro de un conteo queda registrado como una línea de detalle con su código de barras y cantidad.

### 7.4 Conteo Manual (Auditoría)

Permite a un auditor **verificar un conteo terminado** ingresando manualmente las unidades físicas observadas. El sistema calcula la diferencia contra el registro digital.

| Campo | Descripción |
|---|---|
| Marcación | Punto físico a auditar |
| Unidades manual | Cantidad contada físicamente por el auditor |
| Unidades sistema | Cantidad registrada en el sistema (calculada automáticamente) |
| Diferencia | Resultado: Manual − Sistema |

> **Regla de negocio:** Solo se pueden auditar conteos en estado **Terminado** (idestado = 1). Si hay diferencia (≠ 0), el sistema **no guarda** la auditoría y solicita re-verificación.

### 7.5 Estados de Conteo

Catálogo de los posibles estados de una sesión de conteo. Uso principalmente administrativo.

---

## 8. Flujos operativos paso a paso

### Flujo A: Preparación — Crear una marcación (sticker)

```
1. Ir a Marcaciones → "Nueva marcación"
2. Seleccionar la Bodega correspondiente
3. Ingresar la Ubicación y Sección del punto físico
4. Guardar → El sistema asigna un ID único a la marcación
5. Ir a "Imprimir sticker" para generar la etiqueta física
6. Pegar el sticker impreso en la ubicación física del almacén
```

### Flujo B: Operación — Realizar un conteo de mercancía

```
1. Ir a Conteos → "Nuevo conteo"
2. Seleccionar la Marcación (sticker de la ubicación a contar)
3. Guardar para abrir la sesión de conteo
4. Ir al detalle del conteo → "Agregar item"
5. Escanear el código de barras del producto (o ingresar manualmente)
6. Confirmar la cantidad
7. Repetir para cada producto en la ubicación
8. Una vez terminado, cambiar el estado del conteo a "Terminado"
```

> **Consejo:** Usar un escáner USB conectado al equipo facilita el registro de items. El campo de código de barras captura automáticamente la lectura del escáner.

### Flujo C: Auditoría — Validar un conteo terminado

```
1. Ir a Conteo Manual → "Nueva auditoría"
2. Seleccionar la Marcación que se desea auditar
   → El sistema buscará automáticamente el conteo terminado para esa marcación
3. Ingresar las Unidades contadas manualmente (conteo físico del auditor)
4. Hacer clic en "Guardar"

Si Manual = Sistema:
   → El sistema guarda la auditoría con diferencia = 0 ✓
   
Si Manual ≠ Sistema:
   → El sistema muestra la diferencia y NO guarda
   → Se debe investigar el conteo y volver a realizar la auditoría
```

> **Regla importante:** Cada combinación marcación–conteo solo puede auditarse una vez. Si ya existe auditoría para esa marcación, el sistema mostrará un aviso de duplicado.

### Flujo D: Consulta de inventario vs. SIESA

*(Pendiente de validación: confirmar si existe pantalla dedicada para esta consulta o si es solo de uso interno/técnico)*

El sistema puede comparar existencias registradas en GRUMALOG contra las del ERP SIESA. Esto permite detectar diferencias entre el inventario físico auditado y el sistema central.

---

## 9. Errores comunes

| Mensaje | Causa probable | Acción recomendada |
|---|---|---|
| *"Acceso denegado"* / redirige a login | Usuario sin permiso `grumascan` | Contactar al administrador para habilitar el permiso |
| *"No se recibieron datos"* | Formulario enviado vacío o sin completar | Verificar que todos los campos obligatorios estén llenos |
| *"Marcación X no tiene conteo terminado"* | Se intentó auditar una marcación sin conteo en estado Terminado | Terminar el conteo antes de auditar |
| *"Ya existe auditoría manual para esta marcación"* | Se intentó registrar una segunda auditoría para el mismo conteo | Cada conteo admite una sola auditoría. Consultar el registro existente |
| *"Diferencia detectada. Manual: X \| Sistema: Y"* | El conteo manual no coincide con el sistema | Revisar el conteo físico o el detalle del sistema y volver a intentar |
| *"No se pudo guardar"* | Error de validación de datos o base de datos | Anotar el mensaje de error y reportar a soporte técnico |
| Página de error del sistema | Error interno de la aplicación | Contactar soporte técnico con la URL y hora del error |

---

## 10. Preguntas frecuentes

**¿Puedo auditar un conteo que aún está Abierto?**  
No. El sistema solo permite auditar conteos en estado **Terminado**. Debe cerrar el conteo antes de realizar la auditoría.

**¿Qué pasa si cometo un error al ingresar las unidades manuales?**  
Si la auditoría ya fue guardada (diferencia = 0), se puede editar el registro desde la lista de Conteo Manual, siempre que el administrador lo autorice.

**¿El sistema trabaja con varios almacenes?**  
Sí. Cada marcación está asociada a una bodega específica. Se pueden gestionar múltiples bodegas desde el mismo sistema.

**¿Necesito internet para usar el sistema?**  
No. El sistema opera en la red interna de la empresa. Solo se requiere conexión a la LAN corporativa o VPN.

**¿Puedo usar el sistema desde un celular o tablet?**  
Sí. La interfaz está construida con Bootstrap 5 y es responsiva. Sin embargo, para lectura de códigos de barras se recomienda un escáner externo en equipos de escritorio.

**¿Qué significa la "clave de administrador"?**  
Algunas acciones sensibles (como eliminar ítems de un conteo) requieren una clave de administrador adicional. Esta clave la provee el jefe de bodega o el administrador del sistema. *(Pendiente de validación: confirmar la clave actual con el administrador)*

---

## 11. Buenas prácticas

- **Verificar el sticker antes de contar.** Asegurarse de que la marcación seleccionada en el sistema coincide con el sticker físico de la ubicación.
- **No cerrar el navegador durante un conteo activo.** Puede dejar la sesión de conteo abierta. Siempre usar el botón de cerrar sesión.
- **Auditar el mismo día del conteo.** Entre más tiempo transcurra, mayor es el riesgo de movimiento de mercancía que afecte la comparación.
- **No registrar items duplicados.** Si un item ya fue escaneado, verificar la cantidad antes de agregarlo nuevamente.
- **Revisar el estado del conteo** antes de intentar auditarlo. Solo los conteos "Terminados" son auditables.
- **Reportar diferencias de inmediato.** Si el conteo manual no coincide con el sistema, notificar al supervisor antes de repetir el conteo.

---

## 12. Soporte y contacto

| Canal | Datos |
|---|---|
| **Soporte técnico** | Área de sistemas — Grupo Mayorista S.A. |
| **Email del desarrollador** | victorburbano@gruma.com.co |
| **Dirección empresa** | Cr 32 14-25 |
| **Teléfono** | 3229200 |

Para reportar un error, incluir:
1. URL exacta donde ocurrió el problema.
2. Mensaje de error mostrado en pantalla.
3. Fecha y hora del incidente.
4. Nombre de usuario con el que se estaba operando.
5. Pasos que llevaron al error.

---

## 13. Glosario

| Término | Definición |
|---|---|
| **Marcación / Sticker** | Etiqueta física colocada en una ubicación de bodega. Identifica un punto de conteo. En el sistema corresponde al modelo `Grumascanmarcacion`. |
| **Conteo** | Sesión de registro de mercancía para una marcación específica. Agrupa todos los ítems escaneados en esa ubicación. |
| **Detalle de conteo** | Cada línea individual registrada dentro de un conteo: un ítem con su cantidad. |
| **Auditoría manual** | Verificación física realizada por un auditor después de un conteo, comparando unidades manuales contra las del sistema. |
| **Diferencia** | Resultado de restar unidades del sistema a las unidades manuales. Cero significa que el conteo cuadra. |
| **Estado de conteo** | Fase en la que se encuentra una sesión de conteo. Ejemplo: Abierto, Terminado, Validado. |
| **Bodega** | Almacén físico donde se realiza el conteo. |
| **Sección** | Subdivisión de una bodega (pasillo, zona, estante). |
| **SIESA** | ERP corporativo de Grupo Mayorista S.A. Fuente oficial de existencias e ítems. |
| **GRUMALOG** | Base de datos central del sistema logístico. Almacena marcaciones, conteos y auditorías. |
| **EAN / Código de barras** | Código único de identificación de un producto. Se usa para escanear ítems durante el conteo. |
| **grumascan** | Permiso específico que habilita el acceso al módulo de auditoría de inventarios. Se asigna por usuario. |
| **Clave de administrador** | Contraseña adicional requerida para operaciones sensibles dentro del módulo de conteo. |
