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
            <div class="card-header">
                <h5><i class="bi bi-list"></i> Products</h5>
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
                                <td><?php echo e($item['description']); ?></td>
                                <td>$<?php echo formatCurrency($item['unit_price']); ?></td>
                                <td>$<?php echo formatCurrency($item['landing_cost']); ?></td>
                                <td><?php echo formatPercentage($margin); ?></td>
                                <td>$<?php echo formatCurrency($item['final_price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><strong>$<?php echo formatCurrency($total); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-success">
                                <th colspan="8" class="text-end">GRAND TOTAL:</th>
                                <th>$<?php echo formatCurrency($grandTotal); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="<?php echo BASE_URL; ?>index.php?action=proposals" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Proposals
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

