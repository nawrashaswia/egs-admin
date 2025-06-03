# 🚦 Routing & Route Builder Guide

EGS-ADMIN uses a dynamic, modular routing system that makes it easy to manage, validate, and extend routes for both views and controllers.

---

## 🔩 Core Routing System

| File Path | Purpose |
|-----------|---------|
| /public/index.php | Entry point, loads bootloader, routes |
| /app/core/Router.php | Main router logic, dispatching |
| /app/helpers/core/RouteHelper.php | Helper to simplify route declarations |
| /app/core/View.php | Smart view loader, layout-aware |
| /app/modules/{module}/controllers/routes.php | Registers per-module routes via route map |
| /app/modules/{module}/controllers/routes.map.php | Auto-managed route structure from the builder |
| /app/ajax/system_module/router_validation.php | Validates input paths and duplicate conflicts |

---

## ⚙️ Routing Lifecycle
1. Request hits `/public/index.php`
2. Loads core + all module routes
3. Each `routes.php` inside modules loads `routes.map.php`
4. Registers:
   - `RouteHelper::view()` for views
   - `RouteHelper::get()/post()` for logic handlers
5. `Router::dispatch()` handles matching and execution

---

## 🛠️ Route Builder UI
- Access at `/system/router-manager`
- Add new views/controllers visually
- Real-time validation for conflicts and file existence
- Auto-creates subfolders as needed
- Only appends new routes (never overwrites existing)
- Shows a live preview of the final route entries

---

## 📝 Registering Routes (Code)
```php
// Simple view
RouteHelper::view('/system/users', 'modules/system_module/views/users_UI', [
  'title' => 'User Manager'
]);

// Controller logic
RouteHelper::post('/system/users/save', function () {
  require __DIR__ . '/save_user.php';
});
```

---

## 🧠 Best Practices
- Always use the Route Builder UI for new routes
- Never edit `routes.map.php` manually
- Use `RouteHelper::view()` for static views
- Use controller subfolders freely — builder handles it
- Validate all user input for controller routes
- Never hardcode links — use `BASE_URL`

---

## 💡 Highlights
- Auto route validation (conflicts, duplicate paths/files)
- Support for nested controller/view folders
- Automatic path construction (via filename + prefix)
- Route preview in UI before saving
- Logs recent route actions (session-based)
- Prevents damage to critical routes

---

## 🧭 Hosting Checklist
- Web root: `/public`
- Rewrite: `.htaccess` → `index.php`
- `BASE_URL`: Defined in `config/app.php`
- Assets: `/public/assets/...` 