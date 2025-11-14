<?php
$pageTitle = 'Settings';
include __DIR__ . '/../layout/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-gear"></i> Application Settings</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=settings_update" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <h5 class="mb-3">Default Pricing Settings</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Margin %</label>
                            <input type="number" step="0.01" class="form-control" name="default_margin_percentage" 
                                   value="<?php echo $settings['default_margin_percentage']; ?>" required>
                            <small class="form-text text-muted">Default margin percentage for new products</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Duty %</label>
                            <input type="number" step="0.01" class="form-control" name="default_duty_percentage" 
                                   value="<?php echo $settings['default_duty_percentage']; ?>" required>
                            <small class="form-text text-muted">Default duty percentage (typically 30%)</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Shipping %</label>
                            <input type="number" step="0.01" class="form-control" name="default_shipping_percentage" 
                                   value="<?php echo $settings['default_shipping_percentage']; ?>" required>
                            <small class="form-text text-muted">Default shipping percentage (typically 5%)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Box Price</label>
                            <input type="number" step="0.01" class="form-control" name="default_box_price" 
                                   value="<?php echo $settings['default_box_price']; ?>" required>
                            <small class="form-text text-muted">Default box price per unit</small>
                        </div>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">Company Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Company Logo</label>
                        <?php if (!empty($settings['company_logo'])): ?>
                        <div class="mb-2">
                            <img src="<?php echo BASE_URL . $settings['company_logo']; ?>" alt="Company Logo" 
                                 class="img-thumbnail" style="max-height: 100px;">
                            <p class="text-muted small">Current logo</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="company_logo" accept="image/*">
                        <small class="form-text text-muted">Upload company logo for exports (JPEG, PNG, GIF, WebP - Max 5MB)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Default Event Name</label>
                        <input type="text" class="form-control" name="default_event_name" 
                               value="<?php echo e($settings['default_event_name'] ?? ''); ?>" 
                               placeholder="Enter default event name">
                        <small class="form-text text-muted">This will be pre-filled when creating new proposals</small>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo BASE_URL; ?>index.php?action=dashboard" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

