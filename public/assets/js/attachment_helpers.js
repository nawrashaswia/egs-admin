/**
 * Suggest a filename based on module and reference
 */
export function suggestFilename(ref) {
  const module = extractModuleName().toUpperCase().replace(/\W+/g, '_');
  const timestamp = new Date().toISOString().slice(0, 16).replace(/[-:T]/g, '');
  return `${module}_${ref}_${timestamp}`;
}

/**
 * Extract the current module name from global
 */
export function extractModuleName() {
  return window.currentModuleName || 'unknown';
}

/**
 * Detects if current page is HTTPS and would block scanner fetch
 */
export function isMixedContentBlocked() {
  return window.location.protocol === 'https:' && location.hostname !== 'localhost';
}

/**
 * Checks if the scanner agent is available on localhost
 */
let scannerStatusChecked = false;
let scannerStatus = null;
let scannerCheckPromise = null;

export async function checkScannerAgentOnline() {
  // Return cached status if already checked
  if (scannerStatusChecked) {
    return scannerStatus;
  }

  // If a check is already in progress, return that promise
  if (scannerCheckPromise) {
    return scannerCheckPromise;
  }

  // Create new check promise
  scannerCheckPromise = (async () => {
    if (isMixedContentBlocked()) {
      console.warn('Blocked: HTTPS page cannot fetch HTTP scanner bridge.');
      scannerStatus = false;
      scannerStatusChecked = true;
      return false;
    }

    try {
      const res = await fetch('http://localhost:7788/ping');
      scannerStatus = res.ok;
      scannerStatusChecked = true;
      return res.ok;
    } catch (err) {
      // Only log the error once
      if (!scannerStatusChecked) {
        console.warn('Scanner agent unreachable:', err);
      }
      scannerStatus = false;
      scannerStatusChecked = true;
      return false;
    } finally {
      // Clear the promise after completion
      scannerCheckPromise = null;
    }
  })();

  return scannerCheckPromise;
}

/**
 * Setup scanner button with bridge support (auto-device, auto-mode)
 */
export function setupScanButtons() {
  document.querySelectorAll('[data-scan]').forEach(button => {
    button.addEventListener('click', async () => {
      const originalText = button.innerHTML;
      const fileInput = document.getElementById('real-file-input');

      const showStatus = (msg, type = 'info') => {
        const statusBox = document.getElementById('upload-status');
        statusBox.textContent = msg;
        const styles = {
          success: 'text-success fw-semibold',
          error: 'text-danger fw-semibold',
          info: 'text-muted fst-italic'
        };
        statusBox.className = `mt-3 small ${styles[type] || styles.info}`;
      };

      // Only show checking status if we haven't checked before
      if (!scannerStatusChecked) {
        showStatus('📡 Checking scanner agent availability...', 'info');
      }
      
      const isOnline = await checkScannerAgentOnline();
      if (!isOnline) {
        showStatus('❌ Scanner agent not detected. Please make sure it\'s running.', 'error');
        alert('❌ Scanner agent not detected.\nMake sure it\'s installed and running locally.');
        return;
      }

      try {
        // Show scanning in progress
        button.disabled = true;
        button.innerHTML = `<i class="ti ti-loader spinner-border spinner-border-sm me-1"></i> Scanning...`;
        showStatus('🖨 Scanner is working on it... please wait.', 'info');

        const scanRes = await fetch('http://localhost:7788/scan', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({})
        });

        const contentType = scanRes.headers.get("Content-Type") || "";

        if (!scanRes.ok) {
          if (contentType.includes("application/json")) {
            const data = await scanRes.json();
            const errorMessage = data?.error || '❌ Scan failed.';
            showStatus(errorMessage, 'error');
            throw new Error(errorMessage);
          } else {
            const fallbackText = await scanRes.text();
            showStatus("❌ Unexpected HTML response from scanner. Check the agent's logs.", 'error');
            throw new Error(fallbackText);
          }
        }

        // ✅ Only now we access blob after verifying it's not an error
        if (!contentType.includes("image/")) {
          showStatus("❌ Unexpected response type from scanner.", 'error');
          throw new Error("Unexpected response from scanner.");
        }

        const blob = await scanRes.blob();
        const file = new File([blob], `scanned_${Date.now()}.jpg`, { type: blob.type });

        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        fileInput.dispatchEvent(new Event('change'));

        showStatus('✅ Scan complete. File added to uploader.', 'success');

      } catch (err) {
        console.error('Scan failed:', err);
        showStatus(err.message || '❌ Unknown scanner error occurred.', 'error');
        alert(err.message || '❌ Unknown error occurred during scanning.');
      } finally {
        button.disabled = false;
        button.innerHTML = originalText;
      }
    });
  });
}

/**
 * Compress JPEG files client-side; PDFs are skipped
 */
export async function compressFile(file) {
  const type = file.type;

  if (type === 'image/jpeg' || type === 'image/jpg') {
    const bitmap = await createImageBitmap(file);
    const canvas = document.createElement('canvas');
    canvas.width = bitmap.width;
    canvas.height = bitmap.height;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(bitmap, 0, 0);

    return new Promise(resolve => {
      canvas.toBlob(blob => {
        resolve(new File([blob], file.name, { type: 'image/jpeg' }));
      }, 'image/jpeg', 0.6); // 60% quality
    });
  }

  return file;
}
