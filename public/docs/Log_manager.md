# Log Manager & Error Handling System

## 🚀 Latest Features & Improvements (2025-06)

- **Tag-based deduplication:** Only logs with a `tag` are deduplicated (one per event/tag per 30s), all others are always logged.
- **Tag column and filtering:** The `logs` table now has a `tag` column. You can filter logs by tag in the UI.
- **UI enhancements:**
  - Filters for level, user, trace ID, and tag are available and functional.
  - Tags are displayed in the logs table.
  - Button to open Construction Trace Logs in a new tab.
- **Smarter error/trace logging:**
  - All catch blocks in log manager helpers log errors with full context (file, line, stack trace, error message, request/user info).
  - AJAX log receiver logs all submissions, invalid input, and exceptions.
  - Performance manager logs both heavy and near-heavy requests.
- **Context enrichment:** All logs are enriched with request/user context (URL, method, user, IP, agent). Trace ID is included when tracing is active.
- **Admin actions:** Log deletion and trace mode toggling are logged with user context (if not, consider adding).
- **Performance & scalability:** Indexes on `tag`, `event`, `timestamp` for fast filtering and deduplication.

## Overview
This document explains the logging and error handling system for the EGS Admin project, as refactored to use a "native talker" approach. The system is designed to:
- Log all important events and errors in a human-friendly, conversational style.
- Separate normal logs and construction (trace) logs into different tables.
- Provide rich context for every log entry.
- Make error pages and logs as helpful and self-explanatory as possible.

---

## Table of Contents
1. [Log Tables](#log-tables)
2. [Logger Logic](#logger-logic)
3. [Trace Mode & Construction Logs](#trace-mode--construction-logs)
4. [Error Handler Integration](#error-handler-integration)
5. [Log Message Format](#log-message-format)
6. [How the System Talks to You](#how-the-system-talks-to-you)
7. [Troubleshooting & Limitations](#troubleshooting--limitations)
8. [Best Practices](#best-practices)
9. [Log Manager Related Files & Their Responsibilities](#log-manager-related-files--their-responsibilities)
10. [Files That Share or Interact With the Log Manager](#files-that-share-or-interact-with-the-log-manager)

---

## Log Tables

- **logs**: Stores all normal system, audit, and info logs.
- **construction_logs**: Stores trace logs for debugging and construction, only when trace mode is enabled and a trace session is active.

Each table has columns like:
- `trace_id` (nullable for logs, required for construction_logs)
- `event` (the log message, now conversational)
- `level` (INFO, DEBUG, ERROR, etc.)
- `user` (who triggered the event)
- `mode` (system, trace, audit, etc.)
- `ip`, `timestamp`, `context` (JSON-encoded extra info)

---

## Logger Logic

- **Logger::trigger()** is the main entry point for logging.
- If trace mode is ON and a trace session is active, and the log is a trace event, it goes to `construction_logs`.
- All other events go to `logs`.
- If the database is unavailable, logs are written to an emergency file (`logs/emergency_log.json`).
- All log messages are formatted in a conversational, human-friendly way using `LogFormatter::formatConversational()`.

---

## Trace Mode & Construction Logs

- **Trace mode** is a special debugging mode.
- When enabled, you can start a trace session (usually from a UI action or manually via `Logger::startConstructionTrace`).
- All actions and errors during the session/request are logged to `construction_logs`.
- This acts as a "diary" of everything the system does for that session, making debugging much easier.
- Trace logs are also mirrored to a JSON file for easy review.

---

## Error Handler Integration

- The custom `ErrorHandler` is registered at boot and handles:
  - PHP errors (warnings, notices, fatals)
  - Uncaught exceptions
  - Shutdown errors
- For every error/exception/shutdown:
  - A conversational log entry is written (to both logs and construction_logs if trace mode is on).
  - The error page or JSON response is rendered in a friendly, helpful tone.
  - If the error handler itself fails, a minimal fallback message is shown and the error is logged to a file.
- **Note:** Some fatal errors (parse errors, missing files, etc.) cannot be caught by any PHP error handler. For these, check your web server logs.

---

## Log Message Format

All log entries are now formatted as a "story". Example:

```
Hey friend!
At 2025-06-01 12:44:24, I (user: admin, IP: 127.0.0.1) was trying to save a user, ran into this: Duplicate entry for email.
It happened in /app/controllers/UserController.php:42.
Here's what I was doing:
[stack trace or context]
Suggestion: Try a different email address.
Mood: ERROR
```

- Every log includes: who, when, what, where, how, and a "mood" (level).
- Context is always included (file, line, action, trace, suggestion, etc.).

---

## How the System Talks to You

- **Error pages** are friendly and honest, explaining:
  - What happened
  - Where and why
  - What the system was trying to do
  - Suggestions for fixing the issue
  - Technical details (if debug mode is on)
- **Logs** read like a conversation, not just a data dump.
- **Trace logs** act as a diary for the whole request/session, not just a single file.

---

## Troubleshooting & Limitations

- **Uncatchable errors:** Some errors (parse errors, missing files, autoloader issues) happen before the error handler is registered and cannot be caught. For these, check your web server error logs or enable `display_errors` in development.
- **DB failures:** If the database is down, logs are written to an emergency file.
- **Output buffering:** If output has already started, error pages may not render as expected.

---

## Best Practices

- Always start trace sessions from the UI or via `Logger::startConstructionTrace()` when debugging complex flows.
- Use the conversational logs to understand not just what failed, but the whole story of the request.
- For production, keep `display_errors` off and rely on logs and error pages.
- For development, enable `display_errors` to catch uncatchable errors.
- Regularly review both `logs` and `construction_logs` for a complete picture of system health and user actions.

---

## Log Manager Related Files & Their Responsibilities

| File | Location | Responsibility |
|------|----------|---------------|
| Logger.php | app/core/Logger.php | Main logging logic, routes logs to correct table, formats messages, handles emergency fallback |
| LogFormatter.php | app/core/LogFormatter.php | Formats log messages (conversational and standard) |
| TraceManager.php | app/core/TraceManager.php | Manages trace sessions, trace IDs, and trace log file paths |
| DB.php | app/core/DB.php | Database connection and query logic, used by logger for inserts |
| LogStorage.php | app/core/LogStorage.php | (If used) Handles file-based and JSON trace log storage |
| ErrorHandler.php | app/helpers/core/ErrorHandler.php | Registers error/exception/shutdown handlers, logs errors, renders error pages |
| LogHelper.php | app/helpers/core/LogHelper.php | Simple file-based logger for fallback/debugging |
| ConstructionTraceScanner.php | app/helpers/general_module/logmanager/ConstructionTraceScanner.php | Scans for trace session starts in codebase |
| LogQueryHelper.php | app/helpers/general_module/logmanager/LogQueryHelper.php | Fetches logs and trace sessions for UI or API |
| logger_receiver.php | app/ajax/general_module/logger_receiver.php | AJAX endpoint for receiving logs from the frontend |
| settings_ui.php | app/modules/general_module/views/attachment_manager/settings_ui.php | Example UI that starts a trace session |
| edit_extensions_ui.php | app/modules/general_module/views/attachment_manager/edit_extensions_ui.php | Example UI that can trigger trace logging |
| LogManagerView.php | app/modules/general_module/views/logmanager/LogManagerView.php | UI for viewing general logs |
| ConstructionTraceView.php | app/modules/general_module/views/logmanager/ConstructionTraceView.php | UI for viewing construction/trace logs |
| manual_app_log_test.php | public/manual_app_log_test.php | Manual test for logger in app context |
| test_db_log.php | public/test_db_log.php | Standalone DB/log test script |

---

## Files That Share or Interact With the Log Manager

- **AppKernel.php** (`app/core/AppKernel.php`): Boots the app, registers the error handler, and can start trace sessions automatically.
- **Router.php** (`app/core/Router.php`): Uses logger for routing/dispatch events and error handler for 404s.
- **RouteLoader.php** (`app/core/RouteLoader.php`): Logs route loading and registration events.
- **ViewRenderer.php** (`app/core/ViewRenderer.php`): Logs view rendering and partials, can show trace info in HTML.
- **BaseController.php** (`app/core/BaseController.php`): Provides logging/trace helpers for controllers.
- **Auth.php** (`app/core/Auth.php`): Logs login/logout events, supports trace logging for auth actions.
- **Various UI/Controller files**: Many UI and controller files start trace sessions or log events as part of their workflow.

---

This comprehensive structure ensures that all important actions, errors, and traces are logged, viewable, and actionable, with clear separation and a conversational, developer-friendly approach.

---

## Summary

This logging and error handling system is designed to be your friend: it explains itself, tells you what went wrong, why, and what you can do about it. Use the logs and error pages as a conversation with your system, and you'll always know what's happening under the hood. 

---

# 📘 Log Manager v2 Documentation — Developer Edition

Welcome to **Log Manager v2** — a fully enhanced, trace-aware logging system designed for debugging with style, clarity, and intelligence.

---

## 🧭 What's Inside?
- 📌 Dual-mode logging (system vs trace)
- 🧠 Smart trace sessions with auto-detection
- 🐢 Slow query tracking
- 🧼 Log deduplication + garbage filtering
- 📁 JSON-based trace mirrors

---

## 🆕 Newly Added Files

| File | Location | Purpose |
|------|----------|---------|
| `TracingDBPDO.php` | `app/helpers/core/` | Wraps PDO with query logging logic (tracing-aware). |
| `TracingDBStatement.php` | `app/helpers/core/` | Captures SQL executions with file origin and duration. |
| `ConstructionTraceScanner.php` | `app/helpers/general_module/logmanager/` | Scans project files for trace entry points (`Logger::startConstructionTrace`). |
| `ConstructionTraceView.php` | `app/modules/general_module/views/logmanager/` | UI view for live trace session display. |
| `logger_receiver.php` | `app/ajax/general_module/` | Accepts real-time logs from frontend (optional). |

---

## 🧬 Modified Files (with Role Update)

| File | Description |
|------|-------------|
| `DB.php` | Uses `TracingDBPDO` to auto-log queries; marks slow ones (`> 2000ms`). |
| `Logger.php` | Routes logs based on mode: `system` ➝ `logs`, `trace` ➝ `construction_logs`. Filters out repeated and non-useful logs. |
| `TraceManager.php` | Detects trace session validity, cleans up zombie sessions, manages mirrored JSON log files. |
| `AppKernel.php` | Auto-starts trace if active. Logs DB startup. |
| `ErrorHandler.php` | Triggers meaningful logs on fatal errors. |

---

## 🛠️ Major Enhancements

### 🔁 Trace Session Lifecycle
- Auto-created when `Logger::startConstructionTrace(__FILE__)` is detected.
- Auto-killed if:
  - The file is removed.
  - The logger line is removed.
- Logs stored in DB (`construction_logs`) and JSON (`/logs/trace/*.json`).

### 🧼 Useless Log Filtering
- Skips logs shorter than 10 characters.
- Skips logs without meaningful context (`file`, `module`, `action`, etc.).
- Rejects repeated logs within 30s.

### 🐢 SQL Tracking
- Every query now logged with origin file + duration.
- If slow (`> 2s`), logged as `WARN`.

### 🔍 Trace Awareness Everywhere
- `DB::query()` and even `insert/update/delete()` inherit tracing mode.
- JSON mirrors for all trace logs stored safely.

---

## 🐛 Solved Issues

| Problem | Fix |
|--------|------|
| Duplicate trace sessions per file | `realpath()` + session trace_id check. |
| Dead trace sessions | TraceManager checks if file still exists + has trace call. |
| Logs persisted even after trace removal | Live file scan prevents stale logs. |
| Unwanted DB slowdown | Log filtering + verbosity controls. |

---

## 💡 How to Use

### 1. Start a Trace
```php
Logger::startConstructionTrace(__FILE__, "Descriptive notes here");
```

### 2. Track Automatically
```php
ConstructionTraceScanner::autoLogPageLoad(__FILE__);
```

### 3. View the Trace
Head to: `/general/logmanager/view_trace` or custom viewer.

---

### the lines needed in any file should be traced
\App\Core\Logger::startConstructionTrace(__FILE__, 'Tracing user login flow');
\App\Helpers\general_module\logmanager\ConstructionTraceScanner::autoLogPageLoad(__FILE__);

## 📚 Developer Tips

- Don't leave old trace lines in production files.
- Never hardcode paths — always use `__FILE__`.
- Use `"short"` verbosity when tracing live traffic.
- Clean up trace folders regularly (in cronjobs or CI).

---
