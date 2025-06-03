// File: public/assets/js/general_module/attachments/refresh_list.js

export async function refreshAttachmentList(ref = window.currentAttachmentRef) {
  const container = document.getElementById('attachment-list');
  if (!container || !ref) return;

  try {
    const res = await fetch(`/ajax/general_module/get_attachment_list?ref=${encodeURIComponent(ref)}`);

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const html = await res.text();
    container.innerHTML = html;

    // Hide placeholder if we got valid results
    const isEmpty = html.includes('attachment-empty-msg');
    if (!isEmpty) {
      const emptyMsg = document.getElementById('attachment-empty-msg');
      if (emptyMsg) emptyMsg.remove();
    }

  } catch (err) {
    container.innerHTML = `<div class='text-danger'>❌ Failed to load attachments.</div>`;
    console.error('[Attachment List Refresh Error]', err);
  }
}
