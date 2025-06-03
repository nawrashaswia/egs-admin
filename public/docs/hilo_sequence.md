# 🔢 HiLoSequence Reference System

The HiLoSequence system generates unique, prefixed, module-aware reference numbers for insert operations across EGS-ADMIN.

---

## 💡 Purpose
- Replaces AUTO_INCREMENT for public-facing IDs
- Unifies how forms and attachments relate
- Auto-detects the current module
- Generates one number per session per module per user
- Minimizes DB access by caching blocks

---

## 🔠 Format
`{PREFIX}{HI_ALPHA}{LO_PADDED}`

Example: `GENA00000`, `GENA00001`, ...
- `GEN` → module prefix
- `A`   → high index
- `00000` → current LO index

---

## 🗂️ Table: sequence_tracker
```sql
CREATE TABLE sequence_tracker (
  module_name  VARCHAR(100) PRIMARY KEY,
  prefix       VARCHAR(10) NOT NULL,
  last_hi_value INT NOT NULL DEFAULT 0,
  block_size   INT NOT NULL DEFAULT 100000,
  lo_cursor    INT NOT NULL DEFAULT 0,
  updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## ⚙️ How It Works
- Module auto-detected from stack trace
- Reference generated via `HiLoSequence::get()`
- Session-scoped key from user, session, module
- Header auto-generates `$ref` for all pages

---

## ✅ Usage
```php
$ref = HiLoSequence::get();
```
- Used for form submits, attachments, previews, etc.

---

## 📄 Example Seed SQL
```sql
INSERT INTO sequence_tracker (module_name, prefix, last_hi_value, block_size, lo_cursor, updated_at) VALUES
('general',    'GEN', 0, 100000, 0, NOW()),
('telecom',    'TEL', 0, 100000, 0, NOW()),
('hr',         'HR',  0, 100000, 0, NOW());
```

---

## 🧼 Checklist
- Make sure `sequence_tracker` is seeded
- Reference initialized in header
- Avoid `reset()` in production 