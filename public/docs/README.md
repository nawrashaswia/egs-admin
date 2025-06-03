# 📘 EGS-ADMIN User Guide (2025 Edition)

Welcome to the official user and developer guide for **EGS-ADMIN**. This documentation is fully updated for the current system and covers everything you need to understand, extend, and maintain your EGS-ADMIN deployment.

---

## 🚀 Quick Start

- **Project Root:** `/egs-admin`
- **Web Entry Point:** `/public/index.php`
- **Admin Panel:** `/system` (after login)
- **Docs:** `/public/docs/updated_egs_guide/`

---

## 🧠 System Overview

EGS-ADMIN is a modular, secure, and scalable backend system designed for rapid business application development. It features:

- **Domain-driven modular architecture** (each business area is a module)
- **Centralized core** for routing, authentication, and helpers
- **Tabler UI** for a modern, responsive interface
- **Dynamic route builder** for safe, visual route management
- **Centralized error handling, notifications, and performance monitoring**

---

## 🏗️ Architecture at a Glance

- **/app/core/** — The "brain" of the system (Kernel, Router, Auth, DB, ViewRenderer)
- **/app/modules/** — Business logic, views, and controllers (by domain)
- **/app/helpers/** — Shared and module-specific helpers
- **/public/assets/** — All JS, CSS, images
- **/storage/** — Backups, logs, uploads (never public)
- **/config/** — All environment and DB config

See [architecture.md](architecture.md) for a full breakdown.

---

## 🧩 How the Kernel & Core Files Manage the System

**AppKernel.php** is the entry point for all backend logic. It:
- Defines all system constants (paths, config locations)
- Autoloads all classes under the `App\*` namespace
- Loads configuration and initializes sessions, DB, and authentication
- Registers error and exception handlers
- Provides a global context for all views and controllers

**Router.php** and **RouteLoader.php**:
- Load all module routes from `/modules/{module}/controllers/routes.map.php`
- Register both view and controller endpoints
- Dispatch requests to the correct handler

**ViewRenderer.php**:
- Renders all pages with the correct layout and context
- Injects user, ref, config, and flash messages automatically

**Helpers**:
- Are auto-loaded and namespaced (no manual require_once needed)
- Provide reusable logic for logging, CSV, attachments, etc.

---

## 📝 How to Add a New Page or File

1. **Create your view/controller in the correct module folder:**
   - Views: `/app/modules/{module}/views/`
   - Controllers: `/app/modules/{module}/controllers/`

2. **Register the route using the Route Builder UI** (`/system/router-manager`):
   - This updates the module's `routes.map.php` safely
   - No manual editing required

3. **Use the Kernel in your file if you need helpers or config:**
   ```php
   if (!class_exists(\App\Core\AppKernel::class)) {
       require_once dirname(__DIR__, 4) . '/app/core/AppKernel.php';
       \App\Core\AppKernel::boot();
   }
   ```
   - This ensures all constants, helpers, and config are available

4. **Use proper namespacing and imports:**
   ```php
   use App\Helpers\Core\FlashHelper;
   use App\Helpers\Core\AttachmentHelper;
   // ...
   ```

5. **Render your view using ViewRenderer:**
   ```php
   ViewRenderer::render('module/view', [...]);
   ```

6. **Leverage flash messages, error handling, and performance monitoring as needed.**

---

## 🔒 Security & Best Practices
- Never place business logic in `/core` or `/public`
- Always use helpers for shared logic
- Use flash messages for user feedback
- Validate all user input
- Use the provided error handler for all exceptions

---

## 📚 Next Steps
- [architecture.md](architecture.md): Full folder/module breakdown
- [routing.md](routing.md): Dynamic routing and route builder
- [auth.md](auth.md): Authentication and session management
- [attachments.md](attachments.md): File uploads and scanner integration
- [maintenance.md](maintenance.md): Backups, exports, and system tools
- [notifications.md](notifications.md): Alerts and flash messages
- [performance.md](performance.md): Performance monitoring
- [error_handling.md](error_handling.md): Error handling system
- [hilo_sequence.md](hilo_sequence.md): Reference number system
- [faq.md](faq.md): Common issues and troubleshooting

---

**Welcome to EGS-ADMIN — build with confidence, scale with ease!** 