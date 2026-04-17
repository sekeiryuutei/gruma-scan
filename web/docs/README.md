# GRUMALOG Scan — Documentación del Proyecto

> **Fecha de generación:** 2026-04-17  
> **Alcance:** Documentación generada a partir del análisis directo del código fuente, configuraciones y estructura del repositorio. Toda información marcada como `TODO` o `Pendiente de validación` requiere confirmación con el equipo de desarrollo o infraestructura.

---

## Descripción del sistema

**GRUMALOG Scan** es una aplicación web corporativa de **auditoría y conteo de inventarios en bodega**, desarrollada para **Grupo Mayorista S.A.** (NIT 900.091.175). Permite a los operarios escanear y registrar mercancía en ubicaciones físicas de almacén, y a los supervisores validar los conteos contra el sistema ERP (SIESA).

El sistema opera como módulo de soporte logístico, conectado a la base de datos central **GRUMALOG** (SQL Server on-premise) y al ERP **SIESA** (SQL Server en AWS RDS).

---

## Índice de documentación

| Documento | Descripción | Audiencia |
|---|---|---|
| [README.md](README.md) | Este índice | Todos |
| [manual-usuario.md](manual-usuario.md) | Guía operativa del sistema | Almacenistas, auditores, supervisores |
| [documento-tecnico.md](documento-tecnico.md) | Arquitectura, stack, despliegue y mantenimiento | Desarrolladores, DevOps, soporte técnico |

---

## Acceso rápido

- **Aplicación web:** `http://[servidor]/grumalog-scan/web/` (TODO: confirmar URL de producción)
- **Módulo principal:** `/grumascanmarcacion/`
- **Login:** `/site/login`

---

## Contacto del proyecto

- **Desarrollador:** Victor Burbano — victorburbano@gruma.com.co
- **Organización:** Grupo Mayorista S.A.
- **Repositorio:** `\\192.168.2.20\c$\Apache24\htdocs\grumalog-scan`
