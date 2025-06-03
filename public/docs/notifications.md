# 📢 Alerts & Flash Notification System

EGS-ADMIN uses a centralized alert (flash message) system for all success, error, warning, and info messages. This ensures a consistent user experience and easy integration across all modules.

---

## ✅ Overview
- Show messages after any action (form submission, deletion, etc.)
- Consistent Tabler-based UI
- All behavior controlled through a single helper

---

## 🧠 Architecture
| Component | Description |
|-----------|-------------|
| FlashHelper.php | Main logic for setting and rendering flash messages |
| footer.php | Calls `FlashHelper::renderToast()` once globally |
| $_SESSION['flash'] | Storage for queued messages between requests |

---

## 📝 Usage Example
```php
// In a controller
global $BASE_URL;
FlashHelper::set('success', 'User saved successfully.');
header("Location: $BASE_URL/system/users");
exit;
```

---

## 🎨 Message Types
| Type | Class Used | Example Use |
|------|------------|-------------|
| success | bg-success text-white | Data saved, User added |
| error | bg-danger text-white | Invalid input, DB error |
| warning | bg-warning text-dark | Non-blocking issue |
| info | bg-info text-white | General system notes |

---

## ⚙️ Under the Hood
- `FlashHelper::renderToast()` generates a top-right floating alert
- Uses Tabler styling + Bootstrap Toast JS
- Place the renderer only once in the footer

---

## 🔒 Best Practices
- Always use `FlashHelper::set()`
- Never echo inline alerts directly
- Place the renderer only once in the footer
- Avoid passing alerts via URL query strings 