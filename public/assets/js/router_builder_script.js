// router_builder_script.js

// ✅ Update UI prefixes and toggle input locks when module changes
function updatePrefix() {
  const mod = document.getElementById('moduleSelect').value;
  const prefix = '/' + mod.replace('_module', '') + '/';

  const viewPrefixEl = document.getElementById('routePrefix');
  if (viewPrefixEl) viewPrefixEl.textContent = prefix;

  document.querySelectorAll('.ctrlPrefix').forEach(el => el.textContent = prefix);

  const viewFileInput = document.getElementById('viewFileInput');
  const viewPathInput = document.getElementById('viewPathInput');

  if (viewFileInput) viewFileInput.disabled = !mod;
  if (viewPathInput) viewPathInput.disabled = !mod;

  document.querySelectorAll('input[name="controller_paths[]"], input[name="controller_files[]"]').forEach(input => {
    input.disabled = !mod;
  });

  updateViewPathFromFile();
}


// ✅ Extract clean route name from file path
function extractRouteFromPath(path) {
  return path
    .replace('.php', '')
    .replace(/_ui$/, '')
    .toLowerCase();
}

// ✅ Auto-fill view route path and show preview
function updateViewPathFromFile() {
  const viewFileInput = document.getElementById('viewFileInput');
  const viewPathInput = document.getElementById('viewPathInput');
  const val = viewFileInput.value.trim();

  let routePath = extractRouteFromPath(val);
  routePath = routePath.replace(/\\/g, '/');

  viewPathInput.value = routePath;
  updateViewPreview();
  validateViewRoute();
}

// ✅ Generate live preview for RouteHelper::view()
function updateViewPreview() {
  const mod = document.getElementById('moduleSelect').value;
  const viewFile = document.getElementById('viewFileInput').value.trim();
  const viewPath = document.getElementById('viewPathInput').value.trim();
  const preview = document.getElementById('viewRoutePreview');

  if (mod && viewFile && viewPath) {
    preview.classList.remove('d-none');
    const path = `/${mod.replace('_module', '')}/${viewPath}`;
    const file = `modules/${mod}/views/${viewFile.replace(/\.php$/, '')}`;
    preview.textContent = `'${path}' => '${file}',`;
  } else {
    preview.classList.add('d-none');
  }
}

// ✅ Auto-fill controller route path and preview RouteHelper line
function updateControllerPaths() {
  const mod = document.getElementById('moduleSelect').value;

  document.querySelectorAll('.controller-block').forEach(block => {
    const fileInput = block.querySelector('input[name="controller_files[]"]');
    const pathInput = block.querySelector('input[name="controller_paths[]"]');
    const method = block.querySelector('select[name="controller_methods[]"]').value;
    const preview = block.querySelector('.route-preview');

    const fullPath = extractRouteFromPath(fileInput.value.trim());
    const parts = fullPath.split('/');
    const filename = parts.pop();
    const folder = parts.join('/');

    const ctrlPath = `${folder ? folder + '/' : ''}${filename}`;

    pathInput.value = ctrlPath;
    updateMethod(fileInput);

    if (mod && fullPath && ctrlPath) {
      preview.classList.remove('d-none');
      const fileName = fileInput.value.trim();
      const routePath = `/${mod.replace('_module', '')}/${ctrlPath}`;
      preview.textContent = `['method' => '${method}', 'path' => '${routePath}', 'file' => '${fileName}'],`;
    } else {
      preview.classList.add('d-none');
    }

    setTimeout(() => validateControllerRoute(block), 0);
  });
}

// ✅ Suggest GET or POST method based on file name keywords
function updateMethod(input) {
  const val = input.value.toLowerCase();
  const methodSelect = input.closest('.controller-block').querySelector('select');
  const postKeywords = ['save', 'update', 'submit', 'add', 'create', 'password'];
  methodSelect.value = postKeywords.some(keyword => val.includes(keyword)) ? 'post' : 'get';
}

// ✅ Add a controller block dynamically
function addController() {
  const wrapper = document.getElementById('controller-fields');
  const block = document.createElement('div');
  block.classList.add('controller-block');
  block.innerHTML = `
    <div class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label">File Name</label>
        <div class="input-group">
          <span class="input-group-text">controllers/</span>
          <input type="text" name="controller_files[]" class="form-control" placeholder="e.g. folder/save.php" disabled>
        </div>
        <div class="validation-status" id="ctrlFileStatus"></div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Route Path</label>
        <div class="input-group">
          <span class="input-group-text ctrlPrefix">/</span>
          <input type="text" name="controller_paths[]" class="form-control" disabled>
        </div>
        <div class="validation-status" id="ctrlPathStatus"></div>
      </div>
      <div class="col-md-3">
        <label class="form-label d-flex justify-content-between">
          <span>Method</span>
          <button type="button" class="btn btn-sm btn-danger remove-controller" onclick="removeController(this)">
            <i class="ti ti-x"></i>
          </button>
        </label>
        <select name="controller_methods[]" class="form-select">
          <option value="get">GET</option>
          <option value="post">POST</option>
        </select>
      </div>
    </div>
    <div class="route-preview d-none mt-2 small text-muted"></div>
  `;

  wrapper.appendChild(block);
  updatePrefix();

  const fileInput = block.querySelector('input[name="controller_files[]"]');
  const pathInput = block.querySelector('input[name="controller_paths[]"]');

  fileInput.addEventListener('input', () => updateMethod(fileInput));

  fileInput.addEventListener('blur', () => {
    if (!fileInput.value.endsWith('.php') && fileInput.value.trim() !== '') {
      fileInput.value = fileInput.value.trim() + '.php';
    }
    updateControllerPaths();
    setTimeout(() => validateControllerRoute(block), 0);
  });

  pathInput.addEventListener('blur', () => {
    setTimeout(() => validateControllerRoute(block), 0);
  });

  setTimeout(() => updateControllerPaths(), 0);
}

// ✅ Remove controller route block
function removeController(btn) {
  btn.closest('.controller-block').remove();
}

// ✅ Show issues returned from backend validation
function showIssues(issues, container) {
  if (!issues || !container) return;

  const existing = new Set(
    [...container.querySelectorAll('.validation-warning')].map(el => el.innerText.trim())
  );

  issues.forEach(msg => {
    const textContent = msg.replace(/<[^>]*>?/gm, '').trim();
    if (existing.has(textContent)) return;

    const warn = document.createElement('div');
    warn.className = 'validation-warning mt-1';

    if (msg.includes('View route already exists')) {
      warn.innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis">${msg}</span>`;
    } else if (msg.includes('Route defined in')) {
      warn.innerHTML = `<span class="badge bg-danger-subtle text-danger-emphasis">${msg}</span>`;
    } else if (msg.includes('Controller already exists') || msg.includes('file already exists')) {
      warn.innerHTML = `<span class="badge bg-secondary-subtle text-secondary-emphasis">${msg}</span>`;
    } else {
      warn.classList.add('text-danger', 'small');
      warn.innerHTML = msg;
    }

    container.appendChild(warn);
  });
}

// ✅ Clear existing validation warnings
function removeWarnings(container) {
  if (!container) return;
  container.querySelectorAll('.validation-warning').forEach(el => el.remove());
}

// ✅ Send route data to validation endpoint
async function validateRoute(module, viewPath, viewFile, ctrlPath, ctrlFile, ctrlMethod) {
  const formData = new FormData();
  formData.append('module', module);
  formData.append('view_path', viewPath);
  formData.append('view_file', viewFile);
  formData.append('ctrl_path', ctrlPath);
  formData.append('ctrl_file', ctrlFile);
  formData.append('ctrl_method', ctrlMethod);

  try {
    const res = await fetch('/ajax/system_module/validate', {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json'
      }
    });

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const contentType = res.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      throw new Error('Server did not return JSON');
    }

    return await res.json();
  } catch (error) {
    console.error('Validation error:', error);
    return { issues: [error.message] };
  }
}

// ✅ Validate view route
async function validateViewRoute() {
  const module = document.getElementById('moduleSelect').value;
  const viewPath = document.getElementById('viewPathInput').value.trim();
  const viewFile = document.getElementById('viewFileInput').value.trim();

  if (!module || !viewPath || !viewFile) return;

  const fileStatus = document.getElementById('viewFileStatus');
  const pathStatus = document.getElementById('viewPathStatus');

  removeWarnings(fileStatus);
  removeWarnings(pathStatus);

  const result = await validateRoute(module, viewPath, viewFile, '', '', '');

  if (result.issues) {
    showIssues(result.issues, fileStatus);
    showIssues(result.issues, pathStatus);
  }
}

// ✅ Validate controller route
async function validateControllerRoute(block) {
  if (!block) return;

  const module = document.getElementById('moduleSelect').value;
  const fileInput = block.querySelector('input[name="controller_files[]"]');
  const pathInput = block.querySelector('input[name="controller_paths[]"]');
  const methodSelect = block.querySelector('select[name="controller_methods[]"]');

  const file = fileInput.value.trim();
  const path = pathInput.value.trim();
  const method = methodSelect.value;

  if (!module || !file || !path) return;

  const fileStatus = block.querySelector('#ctrlFileStatus');
  const pathStatus = block.querySelector('#ctrlPathStatus');

  removeWarnings(fileStatus);
  removeWarnings(pathStatus);

  const result = await validateRoute(module, '', '', path, file, method);

  if (result.issues) {
    showIssues(result.issues, fileStatus);
    showIssues(result.issues, pathStatus);
  }
}

// ✅ Form submission handler
document.getElementById('routeForm').addEventListener('submit', function(event) {
  const module = document.getElementById('moduleSelect').value;
  const viewFile = document.getElementById('viewFileInput').value.trim();
  const viewPath = document.getElementById('viewPathInput').value.trim();
  const views = {};
  const controllers = [];

  if (viewFile && viewPath) {
    views[`/${module.replace('_module', '')}/${viewPath}`] = `modules/${module}/views/${viewFile}`;
  }

  document.querySelectorAll('.controller-block').forEach(block => {
    const file = block.querySelector('input[name="controller_files[]"]').value.trim();
    const path = block.querySelector('input[name="controller_paths[]"]').value.trim();
    const method = block.querySelector('select[name="controller_methods[]"]').value;

    if (!file || !path) {
      block.querySelectorAll('input').forEach(input => input.classList.add('is-invalid'));
      event.preventDefault();
      return;
    }

    controllers.push({
      method: method,
      path: `/${module.replace('_module', '')}/${path}`,
      file: file
    });
  });

  document.getElementById('views_json').value = JSON.stringify(views);
  document.getElementById('controllers_json').value = JSON.stringify(controllers);
});
