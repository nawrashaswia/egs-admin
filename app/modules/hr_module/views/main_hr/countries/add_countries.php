<?php ob_start(); ?>
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-flag me-2"></i> Add Country</h2>
            </div>
        </div>
    </div>
</div>
<form method="post" action="<?= BASE_URL ?>/hr/countries/store">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">ISO Code</label>
                        <input type="text" name="iso_code" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <input type="text" name="default_currency_code" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Dial Key</label>
                        <input type="text" name="base_dial_key" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Number Length</label>
                        <input type="number" name="local_number_length" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                        <label class="form-label">Accepted Prefixes</label>
                        <input type="text" name="accepted_prefixes" class="form-control" placeholder="Comma separated e.g. +974,00974,974">
                        </div>
                        <div class="col-md-3">
                        <label class="form-label">Timezone</label>
                        <input type="text" name="timezone" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                        <label class="form-label">Flag Image Path</label>
                        <input type="text" name="flag_image" class="form-control" placeholder="flags/qa.png" required>
                        </div>
                        <div class="col-md-2">
                        <label class="form-label">Active</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success"><i class="ti ti-check"></i> Save</button>
                    <a href="<?= BASE_URL ?>/hr/countries" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
<?php $content = ob_get_clean(); ?>
<?php require VIEWS_PATH . '/layout/main.php'; ?>
