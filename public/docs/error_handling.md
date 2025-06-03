# 🛑 Centralized Error Handling

EGS-ADMIN uses a centralized error handling system to ensure all errors are displayed consistently and with relevant information.

---

## 🧠 Centralized Error Handler
- `ErrorHandler` class in `app/helpers/core/ErrorHandler.php`
- Renders error pages and returns JSON error responses for AJAX
- Handles 404, router, server, and validation errors

---

## 📝 Usage
```php
use App\Helpers\Core\ErrorHandler;

// Render a 404 error
ErrorHandler::notFound('The requested page does not exist.', '/some/path');

// Render a router error
ErrorHandler::routerError('The target file is missing.', 'RTE1');

// Render a general server error
ErrorHandler::serverError('An unexpected error occurred.', 'SRV1');

// Return a JSON error response
ErrorHandler::jsonError('Invalid input.', 'VAL1');
```

---

## 🧾 Error Types
- 404 Not Found
- Router Error
- General Server Error
- Validation Error (AJAX)

---

## 🔒 Best Practices
- Always use the centralized error handler
- Include debug info (file, line, stack trace) for server errors
- Use error views in `app/views/error/`
- Never expose sensitive info in production 