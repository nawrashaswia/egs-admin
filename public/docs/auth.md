# 🔐 Authentication & Session Management

EGS-ADMIN uses a secure, session-based authentication system with role support and centralized configuration.

---

## ✅ Core Features
- Passwords hashed with `password_hash()`
- Session-based authentication (`$_SESSION['user_id']`)
- Global route protection (all non-login pages)
- Auto timeout (configurable)
- Flash feedback for login/logout

---

## 🛠️ Configuration
- `/config/session.php` — session settings (name, timeout)
- `/config/app.php` — app name, base URL, debug, charset, timezone

---

## 🚦 How Auth Works
- Session auto-started for all requests
- Excluded paths: `/login`, `/login/submit`, `/logout`
- All other routes require login
- Timeout enforced (default: 30 min)
- On timeout, session is destroyed and user is redirected to login

---

## 🧑‍💻 AuthService & Auth.php
- `AuthService` handles login form, validation, and logout
- `Auth` manages session state, login, logout, and role checks

---

## 📝 Usage Example
```php
// Check login
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// Login
Auth::login($userId);

// Logout
Auth::logout();
```

---

## 👁️ Login View
Show errors using flash messages or session:
```php
<?php if (!empty($_SESSION['login_error'])): ?>
  <div class="alert alert-danger text-center">
    <?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
  </div>
<?php endif; ?>
```
Or use:
```php
FlashHelper::show();
```

---

## 🧰 Protecting New Pages
- No special code needed — bootstrap handles it
- To exclude a route, add it to the exclusion array

---

## 💡 Best Practices
- Always hash passwords
- Use flash messages for feedback
- Never expose session or config files
- Use role checks for sensitive routes
- Plan for future enhancements: password reset, login audit, MFA 