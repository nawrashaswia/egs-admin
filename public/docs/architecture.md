# 🏗️ EGS-ADMIN Architecture & Folder Structure

EGS-ADMIN is designed for modularity, scalability, and maintainability. This section explains the folder structure, the philosophy behind it, and how each part fits together.

---

## 📁 Folder Structure Overview

```
app/
├── modules/             # Business logic by domain
│   └── {module}/
│       ├── controllers/ # Action scripts (insert, update, delete, etc.)
│       ├── views/       # UI screens for the module
│       └── reports/     # (optional) module-specific reports
├── helpers/
│   ├── core/            # Universal logic (log, CSV, file handling)
│   └── {module}/        # Domain-specific helpers
├── middleware/          # Reusable filters (auth, maintenance, role check)
├── services/            # Shared business services (PDF, Mail, Export)
├── api/                 # REST-like JSON API endpoints
├── ajax/                # AJAX endpoints for frontend use
├── cli/                 # Internal command-line tools (optional)
├── jobs/                # CRON or scheduled background tasks
├── tests/               # Manual/PHPUnit scripts for QA
└── core/                # Internal framework: Router, View, Auth, DB
```

- **/public/** — Web root, assets, entry point
- **/storage/** — Backups, logs, uploads (never public)
- **/config/** — All environment and DB config

---

## 🧩 Modularity Philosophy
- Each module is self-contained: views, controllers, helpers
- No module can directly depend on another; all shared logic is in helpers/services
- Modules are registered and routed dynamically
- All business logic is separated from core framework files

---

## 🔄 How It All Fits Together
- **Core**: Handles routing, authentication, view rendering, and system bootstrapping
- **Modules**: Contain all business/domain logic and UI
- **Helpers/Services**: Provide reusable logic for all modules
- **AJAX/API**: Allow for async and RESTful operations
- **Storage**: Keeps all user data, logs, and backups safe and out of the web root

---

## 🗂️ Example: Adding a New Module
1. Create a new folder in `/app/modules/{your_module}/`
2. Add `controllers/`, `views/`, and (optionally) `reports/`
3. Register routes using the Route Builder UI
4. Add any module-specific helpers in `/app/helpers/{your_module}/`

---

## 📦 Deployment & Hosting
- Local: Laragon (localhost)
- Production: Hostinger, GoDaddy, or any PHP host
- Routing: `/public/index.php` with `.htaccess` rewrite rules
- All logs, backups, and uploads are stored in `/storage` (non-public)

---

## 🔚 Summary
EGS-ADMIN is structured for clean scalability and safe modularization. The Route Builder, Folder Exporter, and DB Toolkit automate backend wiring and reduce human error. Always keep features modular and never directly place app logic in `/app/core` or `/public`. 