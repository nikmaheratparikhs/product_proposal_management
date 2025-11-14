<?php
$pageTitle = 'Import Excel';
include __DIR__ . '/../layout/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-upload"></i> Import Products from Excel</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=import_process" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Excel File (.xlsx or .xls) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls" required>
                        <small class="form-text text-muted">Maximum file size: 5MB</small>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import File
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Import Instructions at Bottom -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="bi bi-info-circle"></i> Import Instructions</h5>
            </div>
            <div class="card-body">
                <p>Please ensure your Excel file has the following column order:</p>
                <ol>
                    <li>Sr No</li>
                    <li>Image</li>
                    <li>SKU</li>
                    <li>Description</li>
                    <li>Final Cost</li>
                    <li>Proposal Margin</li>
                    <li>Price</li>
                    <li>Selection</li>
                    <li>Product Link</li>
                    <li>Unit Price</li>
                    <li>30% Duty</li>
                    <li>Shipping Cost 5%</li>
                    <li>Box Price</li>
                    <li>US Landing Cost</li>
                    <li>Margin</li>
                    <li>Final Price</li>
                </ol>
                <p><strong>Note:</strong> If SKU already exists, the product will be updated. Empty SKUs will be skipped.</p>
                <hr>
                <div class="d-flex align-items-center">
                    <i class="bi bi-download me-2"></i>
                    <strong>Need a template?</strong>
                    <a href="<?php echo BASE_URL; ?>index.php?action=download_sample" class="btn btn-sm btn-success ms-2">
                        <i class="bi bi-file-earmark-excel"></i> Download Sample Excel File
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

