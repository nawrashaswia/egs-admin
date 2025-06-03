# 📎 Attachment Manager & Scanner Agent Guide

The Attachment Manager in EGS-ADMIN provides robust file upload, validation, and scanning features, including integration with a local scanner agent for direct document capture.

---

## ✅ Features
- Drag-and-drop uploader with preview
- Rule enforcement (extensions, file size, uniqueness)
- Automatic filename suggestions
- Scanner integration (one-click scan from Windows WIA-compatible devices)
- JPEG compression (client and server side)
- Status-based feedback and retry

---

## 📁 File Structure
```
app/
├── ajax/general_module/handle_attachment_upload.php
├── ajax/general_module/get_attachment_list.php
├── helpers/general_module/attachment_block.php
├── helpers/general_module/attachmentmanagerhelper.php
├── helpers/general_module/AttachmentUI.php
public/
└── assets/js/
    ├── attachment_uploader.js
    ├── attachment_modal.js
    ├── attachment_helpers.js
    └── general_module/attachments/
        ├── form_builder.js
        ├── refresh_list.js
        └── thumbnail_helper.js
```

---

## 🔄 How It Works
1. UI renders via `AttachmentUI::render($ref, $ruleId)`
2. User uploads file (drag/drop or browse)
3. JS handles compression, preview, and upload
4. PHP validates and saves file
5. List refreshes via AJAX

---

## 🖨️ Scanner Agent
- Runs locally at `http://localhost:7788`
- Accepts scan requests from frontend
- Handles retries, busy status, JPEG compression
- Endpoints: `/ping`, `/devices`, `/scan`
- Must be running on each client PC for scan to work

---

## 🔗 JS Integration
- JS fetches scan via POST to `/scan`
- Handles loading, error, and file injection
- Shows live status in UI

---

## 🧪 Local Development
- Run site from Laragon (e.g. `http://egs-admin.test`)
- Ensure all JS files are present in `/public/assets/js/`
- Run scanner agent with Python: `python agent.py`
- Open any form with `AttachmentUI::render(...)` to test

---

## 🚚 Deployment
- All PHP and JS files must be present on server
- `/storage/uploads/attachments` must be writable
- Agent must run on each client PC
- Configure CORS in `agent.py` for production
- Watch for mixed content (HTTPS vs HTTP)

---

## ⚠️ Troubleshooting
- Ensure agent is running and accessible
- Check browser console for CORS or connection errors
- Antivirus/firewall may block port 7788
- Scanned files should be under ~2MB after compression 