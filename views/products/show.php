<?php
$pageTitle = 'Product Details';
include __DIR__ . '/../layout/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if (!empty($product['image_url'])): ?>
                <img src="<?php echo e($product['image_url']); ?>" alt="<?php echo e($product['sku']); ?>" 
                     class="img-fluid rounded" 
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'400\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'400\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?php echo e($product['sku']); ?></h4>
                <div>
                    <?php if (isAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>index.php?action=product_form&id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-success btn-sm" onclick="addToProposal(<?php echo $product['id']; ?>)">
                        <i class="bi bi-cart-plus"></i> Add to Proposal
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">SKU</th>
                        <td><?php echo e($product['sku']); ?></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?php echo e($product['description']); ?></td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><?php echo e($product['category_name'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Product Link</th>
                        <td>
                            <?php if (!empty($product['product_link'])): ?>
                            <a href="<?php echo e($product['product_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right"></i> View on Alibaba
                            </a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <h5 class="mt-4">Pricing Breakdown</h5>
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Unit Price</th>
                        <td>$<?php echo formatCurrency($product['unit_price']); ?></td>
                    </tr>
                    <tr>
                        <th>Duty (30%)</th>
                        <td>$<?php echo formatCurrency($product['duty']); ?></td>
                    </tr>
                    <tr>
                        <th>Shipping Cost (5%)</th>
                        <td>$<?php echo formatCurrency($product['shipping_cost']); ?></td>
                    </tr>
                    <tr>
                        <th>Box Price</th>
                        <td>$<?php echo formatCurrency($product['box_price']); ?></td>
                    </tr>
                    <tr class="table-info">
                        <th>Landing Cost</th>
                        <td><strong>$<?php echo formatCurrency($product['landing_cost']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Margin %</th>
                        <td><?php echo formatPercentage($product['margin_percentage']); ?></td>
                    </tr>
                    <tr class="table-success">
                        <th>Final Price</th>
                        <td><strong>$<?php echo formatCurrency($product['final_price']); ?></strong></td>
                    </tr>
                </table>
                
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add to Proposal Modal -->
<div class="modal fade" id="addToProposalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=add_to_cart">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add to Proposal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" value="1" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addToProposal(productId) {
    new bootstrap.Modal(document.getElementById('addToProposalModal')).show();
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

