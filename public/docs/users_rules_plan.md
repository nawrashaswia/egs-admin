User Roles and Permission System Overview

This document outlines the complete lifecycle and architecture for implementing and maintaining a dynamic, auto-updating user roles and permissions system in the EGS Admin platform.

🔧 Database Structure

Tables

1. users
   - id, username, ...

2. roles
   - id, name, description

3. permissions
   - id, module, type, target, permission_key, file, source_file_path, is_auto_generated, is_active, description

4. user_roles
   - user_id, role_id

5. role_permissions
   - role_id, permission_id

🧠 Core Concepts

Permissions Format
- Each permission is uniquely identified by a key: `module.type.target`
- Example: `pages.view.dashboard` or `system.controller.save`

Permission Types
- view: page rendering (views)
- controller: backend logic or routes

Relationships
- A user has one or more roles
- A role has many permissions
- A user inherits permissions via roles

🗂️ File Responsibilities

✅ PermissionSeeder.php
- Location: app/services/
- Task: Scans all controllers/views and populates the permissions table.
- Compares scanned keys with DB and inserts only new entries (no duplication).
- Optional logging modes: silent, cli, or log.

✅ PermissionScannerHelper.php
- Location: app/helpers/core/
- Task: Collects controller methods and view paths using directory walking.
- Supports detection of view files and `routes.map.php` entries.
- Outputs structured permission data including file paths and module context.

✅ PermissionEnforcer.php
- Location: app/core/
- Task: Middleware check to see if current user can access module.type.target
- Used in ViewRenderer and controllers.

✅ Auth.php
- Location: app/core/
- Method Auth::can($permissionKey) checks against current user’s dynamic permission cache.

🧭 Flow: Page Access (e.g., /dashboard)

1. User visits `/dashboard`
2. `ViewRenderer::render('pages/dashboard')` is called
3. `PermissionEnforcer::check('pages', 'view', 'dashboard')` is triggered
4. DB checked: does the current user’s roles have `pages.view.dashboard`?
5. If allowed, view is rendered. Otherwise, 403 or redirect.

🔄 Auto-updating Permissions

Trigger Options:
- CLI script (`php app/services/seed_perm.php`)
- AJAX button from Maintenance UI (via `/system/maintenance`)
- Future: Permission Assignment UI

Process:
- `PermissionScannerHelper` scans the filesystem
- `PermissionSeeder::run()` compares against DB
- Missing permissions are inserted with full metadata (module, type, target, file path, etc.)

🖥️ UI Integration

🔘 Maintenance Page Button:
- Location: `/system/maintenance`
- Triggers AJAX call to `ajax/system_module/scan_permissions.php`
- Button: "Sync Permissions"
- Returns inserted count in a user-friendly alert

Role Management Page
- CRUD for roles
- Assign permissions to each role

User Management Page
- Assign one or more roles to users

Permission Management (Optional)
- See full permission list (auto-generated)

🧪 Debugging Support

- All permission checks logged to `logs` table
- Trace ID enabled in views
- On DB failure: 500 log shows exact permission_key checked
- Scan and seed logs handled by Logger (optional mode)

🧷 Example Permission Key Mappings

| URL / View             | Module | Type       | Target     | Permission Key            |
|------------------------|--------|------------|------------|---------------------------|
| /dashboard             | pages  | view       | dashboard  | pages.view.dashboard      |
| /system/users/save     | system | controller | save       | system.controller.save    |

🏁 Summary

- Permissions are tightly tied to views and controllers
- DB structure allows granular control
- The system is self-maintaining via scanners
- Logging and traceability are first-class citizens
- AJAX and UI buttons make maintenance smooth and non-technical
