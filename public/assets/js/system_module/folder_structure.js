function toggleAll(state) {
  document.querySelectorAll('.folder-checkbox, .files-checkbox').forEach(cb => {
    cb.checked = state;
  });
}

function showToast(message = 'Done!', type = 'success') {
  const toast = document.getElementById('custom-toast');
  const body = document.getElementById('custom-toast-body');
  toast.classList.remove('bg-success', 'bg-danger', 'bg-info', 'bg-warning', 'd-none');
  toast.classList.add('bg-' + type, 'text-white');
  body.textContent = message;
  new bootstrap.Toast(toast).show();
}

function saveSelection(button = null) {
  if (button) button.disabled = true;

  const folders = [...document.querySelectorAll('.folder-checkbox:checked')].map(cb => cb.value);
  const files = [...document.querySelectorAll('.files-checkbox:checked')].map(cb => cb.value);

  fetch(BASE_URL + '/system/maintenance/save_export_selection', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ include_folders: folders, include_files_in: files })
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showToast(`✅ Selection saved (${data.bytes_written || 0} bytes)`, 'success');
      } else {
        showToast(data.message || '❌ Failed to save selection', 'danger');
      }
    })
    .catch(err => {
      console.error(err);
      showToast('❌ Save failed: ' + err.message, 'danger');
    })
    .finally(() => {
      if (button) button.disabled = false;
    });
}

function injectSelectionsIntoForm(form) {
  const folders = [...document.querySelectorAll('.folder-checkbox:checked')];
  const files = [...document.querySelectorAll('.files-checkbox:checked')];

  folders.forEach(cb => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'include_folders[]';
    input.value = cb.value;
    form.appendChild(input);
  });

  files.forEach(cb => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'include_files_in[]';
    input.value = cb.value;
    form.appendChild(input);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  // ✅ Export form logic
  const exportForm = document.getElementById('exportForm');
  if (exportForm) {
    exportForm.addEventListener('submit', function (e) {
      e.preventDefault();
      exportForm.querySelectorAll('input[name="include_folders[]"], input[name="include_files_in[]"]').forEach(el => el.remove());
      injectSelectionsIntoForm(exportForm);
      exportForm.submit();
    });
  }

  // ✅ Folder checkbox: cascades to all child checkboxes
  document.querySelectorAll('.folder-checkbox').forEach(cb => {
    cb.addEventListener('change', () => {
      const basePath = cb.value;
      const checked = cb.checked;
      document.querySelectorAll('.folder-checkbox, .files-checkbox').forEach(el => {
        if (el.value === basePath || el.value.startsWith(basePath + '/')) {
          el.checked = checked;
        }
      });
    });
  });

  // ✅ Files checkbox: cascades only to child file checkboxes
  document.querySelectorAll('.files-checkbox').forEach(cb => {
    cb.addEventListener('change', () => {
      const basePath = cb.value;
      const checked = cb.checked;
      document.querySelectorAll('.files-checkbox').forEach(el => {
        if (el.value !== basePath && el.value.startsWith(basePath + '/')) {
          el.checked = checked;
        }
      });
    });
  });
});
function triggerZipExport() {
  const form = document.getElementById('exportForm');
  const zipInput = document.getElementById('zipExportInput');
  if (!form || !zipInput) return;

  zipInput.value = '1'; // set ZIP export mode

  // Clear previous hidden injected fields
  form.querySelectorAll('input[name="include_folders[]"], input[name="include_files_in[]"]').forEach(el => el.remove());

  // Inject current selections
  injectSelectionsIntoForm(form);

  // Submit form
  form.submit();
}
