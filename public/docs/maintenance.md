# 🛠️ Maintenance & Backup Guide

EGS-ADMIN provides a robust maintenance system for folder exports, database backups/restores, and full system ZIP backups. All tools are modular, safe, and designed for reliability.

---

## 🆕 Dev Mode Switcher
A new switcher is available in the Maintenance UI to enable or disable **Dev Mode**.

- **Location:** Maintenance UI (`/system/maintenance`)
- **Config:** Controlled by `dev_mode` in `/config/app.php`
- **How it works:**
  - Toggle the switch to enable or disable developer features
  - When enabled, the system may show extra debug info, stack traces, or allow advanced tools
  - When disabled, the system hides debug info and restricts sensitive actions
- **Confirmation:**
  - When you switch dev mode, a message will explain what enabling or disabling means for your environment and security

---

## 📁 File Structure
| Path | Purpose |
|------|---------|
| /views/maintenance/maintenance_UI.php | Loads DB and folder UIs |
| /views/maintenance/db_UI.php | DB manager: backup, restore, info |
| /views/maintenance/folder_structure_UI.php | Folder selector + tree exporter |
| /system/maintenance/folder_export.php | Export folders/files as .txt or .zip |
| /system/maintenance/save_export_selection.php | Save JSON of selected folders |
| /system/maintenance/full_zip_backup.php | ZIP the entire /www folder |
| /system/maintenance/db_backup.php | Dump full SQL to .sql |
| /system/maintenance/db_restore.php | Restore .sql file upload |
| /system/maintenance/db_info_export.php | Export DB schema only |
| /system/maintenance/full_system_backup.php | ZIP of /www + DB |
| /storage/system/folder_export_save.json | Last export preferences |
| /storage/backups/YYYY-MM-DD/ | Archive of all .txt, .sql, .zip |

---

## 🔧 Capabilities
- Folder export (tree explorer, .txt/.zip)
- DB schema info export
- Full DB backup/restore
- Full ZIP of /www
- All-in-one system backup (code + DB)
- Modular UI (DB and folder tools work independently)

---

## 🔐 Security
- Only .sql uploads allowed
- Path traversal prevented via realpath/scandir
- DB credentials never echoed
- ZIP creation uses ZipArchive (no shell)
- No temp files exposed to /public

---

## 🧰 Usage
- Access via `/system/maintenance`
- Tabs: Folder Export, DB Tools, Full ZIP, Schema Viewer
- All operations store output in `$_SESSION['db_summary']` for logs

---

## 🔜 Future Improvements
- Scheduled CRON backups
- Email ZIP to admin
- Restore from backup directory
- Visual diff between schema and backup
- Full audit trail via LogHelper 