<?php
$pageTitle = 'Dashboard';
include __DIR__ . '/layout/header.php';

// Get statistics
$conn = getDBConnection();

$productCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$proposalCount = $conn->query("SELECT COUNT(*) as count FROM proposals")->fetch_assoc()['count'];
$categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

$userId = $_SESSION['user_id'];
$isAdmin = isAdmin();

if ($isAdmin) {
    $myProposalCount = $proposalCount;
} else {
    // Use direct query to avoid get_result() compatibility issues
    $countResult = $conn->query("SELECT COUNT(*) as count FROM proposals WHERE user_id = " . intval($userId));
    if ($countResult) {
        $countRow = $countResult->fetch_assoc();
        $myProposalCount = $countRow['count'];
        $countResult->close();
    } else {
        $myProposalCount = 0;
    }
}
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Products</h6>
                        <h2><?php echo $productCount; ?></h2>
                    </div>
                    <i class="bi bi-grid" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="<?php echo BASE_URL; ?>index.php?action=products" class="text-white text-decoration-none">
                    View all <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">My Proposals</h6>
                        <h2><?php echo $myProposalCount; ?></h2>
                    </div>
                    <i class="bi bi-file-earmark-text" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="<?php echo BASE_URL; ?>index.php?action=proposals" class="text-white text-decoration-none">
                    View all <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <?php if ($isAdmin): ?>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Proposals</h6>
                        <h2><?php echo $proposalCount; ?></h2>
                    </div>
                    <i class="bi bi-files" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                <a href="<?php echo BASE_URL; ?>index.php?action=proposals" class="text-white text-decoration-none">
                    View all <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Categories</h6>
                        <h2><?php echo $categoryCount; ?></h2>
                    </div>
                    <i class="bi bi-tags" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>index.php?action=proposal_builder" class="btn btn-primary btn-lg">
                        <i class="bi bi-cart-plus"></i> Build New Proposal
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?action=products" class="btn btn-outline-primary">
                        <i class="bi bi-grid"></i> Browse Products
                    </a>
                    <?php if ($isAdmin): ?>
                    <a href="<?php echo BASE_URL; ?>index.php?action=import" class="btn btn-outline-success">
                        <i class="bi bi-upload"></i> Import Excel
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?action=settings" class="btn btn-outline-secondary">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history"></i> Recent Proposals</h5>
            </div>
            <div class="card-body">
                <?php
                $recentProposals = [];
                
                if ($isAdmin) {
                    $recentSql = "SELECT p.*, u.username FROM proposals p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5";
                    $result = $conn->query($recentSql);
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $recentProposals[] = $row;
                        }
                        $result->close();
                    }
                } else {
                    // Use direct query for non-admin to avoid get_result() compatibility issues
                    $recentSql = "SELECT * FROM proposals WHERE user_id = " . intval($userId) . " ORDER BY created_at DESC LIMIT 5";
                    $result = $conn->query($recentSql);
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $recentProposals[] = $row;
                        }
                        $result->close();
                    }
                }
                
                if (empty($recentProposals)):
                ?>
                <p class="text-muted">No recent proposals.</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentProposals as $proposal): ?>
                    <a href="<?php echo BASE_URL; ?>index.php?action=proposal_view&id=<?php echo $proposal['id']; ?>" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo e($proposal['event_name'] ?? 'Untitled Proposal'); ?></h6>
                            <small><?php echo date('M d, Y', strtotime($proposal['created_at'])); ?></small>
                        </div>
                        <p class="mb-1"><?php echo e($proposal['customer_name'] ?? 'N/A'); ?></p>
                        <?php if ($isAdmin && isset($proposal['username'])): ?>
                        <small>By: <?php echo e($proposal['username']); ?></small>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

