<?php
$pageTitle = ($product ? 'Edit' : 'Add') . ' Product';
include __DIR__ . '/../layout/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-<?php echo $product ? 'pencil' : 'plus-circle'; ?>"></i> <?php echo $product ? 'Edit' : 'Add'; ?> Product</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=product_save">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" value="<?php echo $product['id'] ?? 0; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="sku" value="<?php echo e($product['sku'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo e($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" value="<?php echo e($product['image_url'] ?? ''); ?>" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Link (Alibaba)</label>
                            <input type="url" class="form-control" name="product_link" value="<?php echo e($product['product_link'] ?? ''); ?>" placeholder="https://alibaba.com/...">
                        </div>
                    </div>
                    
                    <hr>
                    <h5>Pricing</h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="unit_price" 
                                   value="<?php echo $product['unit_price'] ?? 0; ?>" required 
                                   onchange="calculatePricing()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Duty %</label>
                            <input type="number" step="0.01" class="form-control" name="duty_percentage" 
                                   value="<?php echo $product ? (($product['duty'] / $product['unit_price']) * 100) : $settings['default_duty_percentage']; ?>" 
                                   onchange="calculatePricing()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Shipping %</label>
                            <input type="number" step="0.01" class="form-control" name="shipping_percentage" 
                                   value="<?php echo $product ? (($product['shipping_cost'] / ($product['unit_price'] + $product['duty'])) * 100) : $settings['default_shipping_percentage']; ?>" 
                                   onchange="calculatePricing()">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Box Price</label>
                            <input type="number" step="0.01" class="form-control" name="box_price" 
                                   value="<?php echo $product['box_price'] ?? $settings['default_box_price']; ?>" 
                                   onchange="calculatePricing()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Margin %</label>
                            <input type="number" step="0.01" class="form-control" name="margin_percentage" 
                                   value="<?php echo $product['margin_percentage'] ?? $settings['default_margin_percentage']; ?>" 
                                   onchange="calculatePricing()">
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Calculated Values:</strong><br>
                        Landing Cost: $<span id="landing_cost"><?php echo formatCurrency($product['landing_cost'] ?? 0); ?></span><br>
                        Final Price: $<span id="final_price"><?php echo formatCurrency($product['final_price'] ?? 0); ?></span>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calculatePricing() {
    const unitPrice = parseFloat(document.querySelector('input[name="unit_price"]').value) || 0;
    const dutyPercent = parseFloat(document.querySelector('input[name="duty_percentage"]').value) || 0;
    const shippingPercent = parseFloat(document.querySelector('input[name="shipping_percentage"]').value) || 0;
    const boxPrice = parseFloat(document.querySelector('input[name="box_price"]').value) || 0;
    const marginPercent = parseFloat(document.querySelector('input[name="margin_percentage"]').value) || 0;
    
    // Calculate
    const duty = unitPrice * (dutyPercent / 100);
    const shipping = (unitPrice + duty) * (shippingPercent / 100);
    const landingCost = unitPrice + duty + shipping + boxPrice;
    const finalPrice = Math.round(landingCost / (1 - (marginPercent / 100)));
    
    document.getElementById('landing_cost').textContent = landingCost.toFixed(2);
    document.getElementById('final_price').textContent = finalPrice.toFixed(2);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

