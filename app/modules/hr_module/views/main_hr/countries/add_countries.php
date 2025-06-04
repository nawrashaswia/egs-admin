<?php ob_start(); ?>
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">HR Module</div>
                <h2 class="page-title">
                    <i class="ti ti-flag me-2"></i> Add Country
                </h2>
            </div>
        </div>
    </div>
</div>

<form method="post" action="<?= BASE_URL ?>/hr/countries/store">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-4">

                        <!-- Left: Logical grouped form -->
                        <div class="col-md-8">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Country Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">ISO Code</label>
                                    <input type="text" name="iso_code" class="form-control" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Currency</label>
                                    <input type="text" name="default_currency_code" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Dial Key</label>
                                    <input type="text" name="base_dial_key" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Number Length</label>
                                    <input type="number" name="local_number_length" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Timezone</label>
                                    <input type="text" name="timezone" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Accepted Prefixes</label>
                                    <input type="text" name="accepted_prefixes" class="form-control" placeholder="+974,00974,974">
                                    <small class="form-hint text-muted">Separate each prefix with a comma (e.g., <code>+974,00974,974</code>)</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Flag Image Path</label>
                                    <input type="text" name="flag_image" id="flag_image_input" class="form-control" placeholder="flags/qa.png" required oninput="updateFlagPreview(this.value)">
                                    <small class="form-hint text-muted">Relative to <code>public/</code> folder</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <label class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                        <span class="form-check-label">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Flag Preview -->
                        <div class="col-md-4">
                            <label class="form-label">Flag Preview</label>
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="height: 140px;">
                                <img id="flag_preview" src="<?= BASE_URL ?>/assets/img/flags/ex-flag.svg" alt="Flag Preview" class="img-fluid" style="max-height: 100px;">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check"></i> Save
                    </button>
                    <a href="<?= BASE_URL ?>/hr/countries" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function updateFlagPreview(path) {
    const preview = document.getElementById('flag_preview');
    preview.src = '<?= BASE_URL ?>/' + path;
}
</script>

<?php $content = ob_get_clean(); ?>
<?php require VIEWS_PATH . '/layout/main.php'; ?>
