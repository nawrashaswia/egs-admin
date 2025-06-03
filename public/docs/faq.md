# ❓ FAQ & Troubleshooting

This section covers common issues, solutions, and best practices for EGS-ADMIN.

---

## 1. Namespace Issues
**Problem:** Class not found (e.g., Helpers\Core\ModuleDiscoveryHelper)
**Solution:** Use correct namespaced import and ensure autoloading is set up.

---

## 2. View File Autoloading
**Problem:** Helper not found in views
**Solution:** Ensure `AppKernel::boot()` is called before using helpers/constants in views.

---

## 3. Helper Usage
- Always use proper namespacing
- Include fallback requires for critical helpers
- Boot kernel when needed in views
- Check class existence before using

---

## 4. Error Handling
- Use centralized error handler for all errors
- Include debug info for server errors
- Use error views in `app/views/error/`

---

## 5. Route Validation
- Use the correct registered route for AJAX validation
- Always verify route registration in RouteLoader.php before implementing client-side calls

---

## 6. Validation Endpoint Implementation
- Use output buffering to prevent unwanted output
- Always set content type header for JSON
- Clean output buffer before sending JSON
- Handle errors with proper JSON responses
- Validate array access with null coalescing
- Check file existence before operations

---

## 7. Route Map Handling
- Initialize arrays with default structure
- Use null coalescing for safe access
- Validate array types before iteration
- Handle missing files gracefully
- Check file read operations

---

## 8. Security
- Never expose sensitive info in production
- Validate all user input
- Use role checks for sensitive routes

---

## 9. Performance
- Use timers for heavy code
- Watch for warnings about slow or memory-heavy operations
- Use the performance page to identify bottlenecks

---

## 10. General Tips
- Prefer structured error handling over raw echo
- Always start with AppKernel.php for CLI or controller logic
- Use App::redirect() and FlashHelper for control flow
- Rely on session-based ref for uploads or temporary data grouping
- Use proper namespacing and modern PHP features
- Follow established patterns for controllers and views 