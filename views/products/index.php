<?php
$pageTitle = 'Products';
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-grid"></i> Products</h2>
    <?php if (isAdmin()): ?>
    <div>
        <a href="<?php echo BASE_URL; ?>index.php?action=product_form" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Product
        </a>
        <a href="<?php echo BASE_URL; ?>index.php?action=import" class="btn btn-success">
            <i class="bi bi-upload"></i> Import Excel
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters & Search</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>index.php">
            <input type="hidden" name="action" value="products">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">SKU</label>
                    <input type="text" class="form-control" name="search_sku" value="<?php echo e($_GET['search_sku'] ?? ''); ?>" placeholder="Search SKU">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="search_description" value="<?php echo e($_GET['search_description'] ?? ''); ?>" placeholder="Search description">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Margin % (Min)</label>
                    <input type="number" step="0.01" class="form-control" name="margin_min" value="<?php echo e($_GET['margin_min'] ?? ''); ?>" placeholder="Min">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Margin % (Max)</label>
                    <input type="number" step="0.01" class="form-control" name="margin_max" value="<?php echo e($_GET['margin_max'] ?? ''); ?>" placeholder="Max">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Landing Cost (Min)</label>
                    <input type="number" step="0.01" class="form-control" name="landing_cost_min" value="<?php echo e($_GET['landing_cost_min'] ?? ''); ?>" placeholder="Min">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Landing Cost (Max)</label>
                    <input type="number" step="0.01" class="form-control" name="landing_cost_max" value="<?php echo e($_GET['landing_cost_max'] ?? ''); ?>" placeholder="Max">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Final Price (Min)</label>
                    <input type="number" step="0.01" class="form-control" name="final_price_min" value="<?php echo e($_GET['final_price_min'] ?? ''); ?>" placeholder="Min">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Final Price (Max)</label>
                    <input type="number" step="0.01" class="form-control" name="final_price_max" value="<?php echo e($_GET['final_price_max'] ?? ''); ?>" placeholder="Max">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" name="sort_by">
                        <option value="id" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'id') ? 'selected' : ''; ?>>ID</option>
                        <option value="sku" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'sku') ? 'selected' : ''; ?>>SKU</option>
                        <option value="final_price" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'final_price') ? 'selected' : ''; ?>>Final Price</option>
                        <option value="landing_cost" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'landing_cost') ? 'selected' : ''; ?>>Landing Cost</option>
                        <option value="margin_percentage" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'margin_percentage') ? 'selected' : ''; ?>>Margin %</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <select class="form-select" name="sort_order">
                        <option value="DESC" <?php echo (isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') ? 'selected' : ''; ?>>Descending</option>
                        <option value="ASC" <?php echo (isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') ? 'selected' : ''; ?>>Ascending</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Apply Filters
                    </button>
                    <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Products List</h5>
        <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=add_multiple_to_cart" id="bulkAddForm" style="display: inline;">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="product_ids" id="selected_product_ids">
            <button type="submit" class="btn btn-success btn-sm" id="bulkAddBtn" disabled>
                <i class="bi bi-cart-plus"></i> Add Selected to Proposal (<span id="selected_count">0</span>)
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll" title="Select All">
                        </th>
                        <th width="120">Image</th>
                        <th>SKU</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Unit Price</th>
                        <th>Landing Cost</th>
                        <th>Margin %</th>
                        <th>Final Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="10" class="text-center">No products found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>" 
                                   onchange="updateSelectedCount()">
                        </td>
                        <td>
                            <?php if (!empty($product['image_url'])): ?>
                            <img src="<?php echo e($product['image_url']); ?>" alt="<?php echo e($product['sku']); ?>" 
                                 class="product-image-thumbnail" 
                                 onclick="viewImage('<?php echo e($product['image_url']); ?>', '<?php echo e($product['sku']); ?>')"
                                 style="cursor: pointer; width: 100px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'80\'%3E%3Crect fill=\'%23ddd\' width=\'100\' height=\'80\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center product-image-placeholder" 
                                 style="width: 100px; height: 80px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;"
                                 onclick="viewImage('', '<?php echo e($product['sku']); ?>')">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($product['sku']); ?></td>
                        <td><?php echo e($product['description']); ?></td>
                        <td><?php echo e($product['category_name'] ?? 'N/A'); ?></td>
                        <td>$<?php echo formatCurrency($product['unit_price']); ?></td>
                        <td>$<?php echo formatCurrency($product['landing_cost']); ?></td>
                        <td><?php echo formatPercentage($product['margin_percentage']); ?></td>
                        <td><strong>$<?php echo formatCurrency($product['final_price']); ?></strong></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>index.php?action=product_show&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (isAdmin()): ?>
                                <a href="<?php echo BASE_URL; ?>index.php?action=product_form&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>index.php?action=product_delete&id=<?php echo $product['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                   class="btn btn-danger" title="Delete" 
                                   onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        $baseUrl = BASE_URL . 'index.php?action=products&' . http_build_query(array_filter([
            'search_sku' => $_GET['search_sku'] ?? '',
            'search_description' => $_GET['search_description'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'margin_min' => $_GET['margin_min'] ?? '',
            'margin_max' => $_GET['margin_max'] ?? '',
            'landing_cost_min' => $_GET['landing_cost_min'] ?? '',
            'landing_cost_max' => $_GET['landing_cost_max'] ?? '',
            'final_price_min' => $_GET['final_price_min'] ?? '',
            'final_price_max' => $_GET['final_price_max'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'id',
            'sort_order' => $_GET['sort_order'] ?? 'DESC'
        ]));
        echo generatePagination($page, $totalPages, $baseUrl);
        ?>
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="modal fade" id="imageViewerModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageViewerTitle">Product Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="viewerImage" src="" alt="" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<script>
// Image viewer function
function viewImage(imageUrl, sku) {
    const modal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
    const img = document.getElementById('viewerImage');
    const title = document.getElementById('imageViewerTitle');
    
    if (imageUrl) {
        img.src = imageUrl;
        img.alt = sku;
        title.textContent = 'Product Image - ' + sku;
    } else {
        img.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'400\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'400\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3ENo Image Available%3C/text%3E%3C/svg%3E';
        title.textContent = 'No Image - ' + sku;
    }
    
    modal.show();
}

// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

// Update selected count
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selected_count').textContent = count;
    document.getElementById('bulkAddBtn').disabled = count === 0;
    
    // Update hidden input with selected IDs
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    document.getElementById('selected_product_ids').value = selectedIds.join(',');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

