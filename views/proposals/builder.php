<?php
$pageTitle = 'Build Proposal';
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cart-plus"></i> Build Proposal</h2>
    <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add More Products
    </a>
</div>

<?php if (empty($products)): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle"></i> Your proposal cart is empty. 
    <a href="<?php echo BASE_URL; ?>index.php?action=products">Add products</a> to get started.
</div>
<?php else: ?>

<form method="POST" action="<?php echo BASE_URL; ?>index.php?action=proposal_save">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-cart"></i> Cart Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>SKU</th>
                                    <th>Description</th>
                                    <th>Unit Price</th>
                                    <th>Landing Cost</th>
                                    <th>Margin %</th>
                                    <th>Final Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $index => $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?php echo e($product['image_url']); ?>" alt="<?php echo e($product['sku']); ?>" 
                                             class="img-thumbnail" style="max-width: 50px; max-height: 50px;"
                                             onerror="this.style.display='none'">
                                        <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($product['sku']); ?></td>
                                    <td><?php echo e($product['description']); ?></td>
                                    <td>$<?php echo formatCurrency($product['unit_price']); ?></td>
                                    <td>$<?php echo formatCurrency($product['landing_cost']); ?></td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control form-control-sm" 
                                               name="margin[<?php echo $product['id']; ?>]" 
                                               value="<?php echo $product['proposal_margin']; ?>"
                                               onchange="updateRowTotal(<?php echo $product['id']; ?>, <?php echo $product['landing_cost']; ?>, this.value)">
                                    </td>
                                    <td id="final_price_<?php echo $product['id']; ?>">
                                        $<?php echo formatCurrency($product['proposal_final_price']); ?>
                                    </td>
                                    <td>
                                        <input type="number" min="1" class="form-control form-control-sm" 
                                               name="quantity[<?php echo $product['id']; ?>]" 
                                               value="<?php echo $product['proposal_quantity']; ?>"
                                               onchange="updateRowTotal(<?php echo $product['id']; ?>, <?php echo $product['landing_cost']; ?>, document.querySelector('input[name=\"margin[<?php echo $product['id']; ?>]\"]').value)">
                                    </td>
                                    <td id="total_<?php echo $product['id']; ?>">
                                        $<?php echo formatCurrency($product['proposal_total']); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>index.php?action=remove_from_cart&product_id=<?php echo $product['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Remove this item?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <th colspan="8" class="text-end">GRAND TOTAL:</th>
                                    <th id="grand_total">$<?php echo formatCurrency($totalAmount); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> Proposal Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Event Name</label>
                        <input type="text" class="form-control" name="event_name" 
                               value="<?php echo e($settings['default_event_name'] ?? ''); ?>" 
                               placeholder="Enter event name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" name="customer_name" 
                               placeholder="Enter customer name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="5" 
                                  placeholder="Additional notes..."></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-save"></i> Save Proposal
                        </button>
                        <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Add More Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function updateRowTotal(productId, landingCost, marginPercent) {
    const quantity = parseFloat(document.querySelector('input[name="quantity[' + productId + ']"]').value) || 1;
    const margin = parseFloat(marginPercent) || 0;
    
    // Calculate final price: MROUND(landingCost / (1 - margin/100), 1)
    const finalPrice = Math.round(landingCost / (1 - (margin / 100)));
    const total = finalPrice * quantity;
    
    document.getElementById('final_price_' + productId).textContent = '$' + finalPrice.toFixed(2);
    document.getElementById('total_' + productId).textContent = '$' + total.toFixed(2);
    
    // Update grand total
    updateGrandTotal();
}

function updateGrandTotal() {
    let grandTotal = 0;
    <?php foreach ($products as $product): ?>
    const total_<?php echo $product['id']; ?> = parseFloat(document.getElementById('total_<?php echo $product['id']; ?>').textContent.replace('$', '')) || 0;
    grandTotal += total_<?php echo $product['id']; ?>;
    <?php endforeach; ?>
    
    document.getElementById('grand_total').textContent = '$' + grandTotal.toFixed(2);
}
</script>

<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

