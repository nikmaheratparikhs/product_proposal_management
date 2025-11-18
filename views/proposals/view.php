<?php
$pageTitle = 'Proposal Details';
include __DIR__ . '/../layout/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-file-earmark-text"></i> Proposal #<?php echo $proposal['id']; ?></h4>
                <div class="btn-group">
                    <?php if (isAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>index.php?action=proposal_edit&id=<?php echo $proposal['id']; ?>" 
                       class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?action=proposal_delete&id=<?php echo $proposal['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this proposal? This action cannot be undone.');">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                    <?php endif; ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?action=export_excel&id=<?php echo $proposal['id']; ?>">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Excel (.xlsx)
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?action=export_word&id=<?php echo $proposal['id']; ?>">
                                <i class="bi bi-file-earmark-word"></i> Word (.docx)
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?action=export_pptx&id=<?php echo $proposal['id']; ?>">
                                <i class="bi bi-file-earmark-slides"></i> PowerPoint (.pptx)
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Event Name:</strong> <?php echo e($proposal['event_name'] ?? 'N/A'); ?><br>
                        <strong>Customer Name:</strong> <?php echo e($proposal['customer_name'] ?? 'N/A'); ?><br>
                        <strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($proposal['created_at'])); ?>
                    </div>
                    <?php if (isset($proposal['username'])): ?>
                    <div class="col-md-6 text-end">
                        <strong>Created By:</strong> <?php echo e($proposal['username']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($proposal['notes'])): ?>
                <div class="alert alert-info">
                    <strong>Notes:</strong><br>
                    <?php echo nl2br(e($proposal['notes'])); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-list"></i> Products</h5>
                <?php if (isAdmin()): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>
                <?php endif; ?>
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
                                <th>Quantity</th>
                                <th>Total</th>
                                <?php if (isAdmin()): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandTotal = 0;
                            foreach ($items as $item): 
                                $margin = $item['custom_margin'] ?? $item['margin_percentage'];
                                $total = $item['final_price'] * $item['quantity'];
                                $grandTotal += $total;
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo e($item['image_url']); ?>" alt="<?php echo e($item['sku']); ?>" 
                                         class="img-thumbnail" style="max-width: 50px; max-height: 50px;"
                                         onerror="this.style.display='none'">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($item['sku']); ?></td>
                                <td class="text-start"><?php echo e($item['description']); ?></td>
                                <td>$<?php echo formatCurrency($item['unit_price']); ?></td>
                                <td>$<?php echo formatCurrency($item['landing_cost']); ?></td>
                                <td><?php echo formatPercentage($margin); ?></td>
                                <td>$<?php echo formatCurrency($item['final_price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="text-end"><strong>$<?php echo formatCurrency($total); ?></strong></td>
                                <?php if (isAdmin()): ?>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-warning btn-sm" 
                                                onclick="editItem(<?php echo $item['id']; ?>, <?php echo $item['quantity']; ?>, <?php echo $margin; ?>)"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="<?php echo BASE_URL; ?>index.php?action=proposal_remove_product&proposal_id=<?php echo $proposal['id']; ?>&item_id=<?php echo $item['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to remove this product from the proposal?');"
                                           title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-success">
                                <th colspan="<?php echo isAdmin() ? '9' : '8'; ?>" class="text-end">GRAND TOTAL:</th>
                                <th class="text-end">$<?php echo formatCurrency($grandTotal); ?></th>
                                <?php if (isAdmin()): ?>
                                <th></th>
                                <?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if (isAdmin()): ?>
        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Product to Proposal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=proposal_add_product">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Product</label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="">Select a product...</option>
                                    <?php foreach ($allProducts as $product): ?>
                                        <?php 
                                        // Check if product already in proposal
                                        $inProposal = false;
                                        foreach ($items as $item) {
                                            if ($item['product_id'] == $product['id']) {
                                                $inProposal = true;
                                                break;
                                            }
                                        }
                                        if (!$inProposal):
                                        ?>
                                        <option value="<?php echo $product['id']; ?>">
                                            <?php echo e($product['sku']); ?> - <?php echo e($product['description']); ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="custom_margin" class="form-label">Custom Margin % (optional)</label>
                                <input type="number" class="form-control" id="custom_margin" name="custom_margin" 
                                       step="0.01" min="0" placeholder="Leave empty to use product default">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Item Modal -->
        <div class="modal fade" id="editItemModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=proposal_update_item">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                        <input type="hidden" name="item_id" id="edit_item_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_custom_margin" class="form-label">Custom Margin % (optional)</label>
                                <input type="number" class="form-control" id="edit_custom_margin" name="custom_margin" 
                                       step="0.01" min="0" placeholder="Leave empty to use product default">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        function editItem(itemId, quantity, margin) {
            document.getElementById('edit_item_id').value = itemId;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_custom_margin').value = margin || '';
            const modal = new bootstrap.Modal(document.getElementById('editItemModal'));
            modal.show();
        }
        </script>
        <?php endif; ?>
        
        <div class="mt-3">
            <a href="<?php echo BASE_URL; ?>index.php?action=proposals" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Proposals
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

