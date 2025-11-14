<?php
$pageTitle = 'Proposals';
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Proposals</h2>
    <a href="<?php echo BASE_URL; ?>index.php?action=proposal_builder" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Proposal
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($proposals)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No proposals found. 
            <a href="<?php echo BASE_URL; ?>index.php?action=proposal_builder">Create your first proposal</a>.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Name</th>
                        <th>Customer Name</th>
                        <?php if ($isAdmin): ?>
                        <th>Created By</th>
                        <?php endif; ?>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proposals as $proposal): ?>
                    <tr>
                        <td>#<?php echo $proposal['id']; ?></td>
                        <td><?php echo e($proposal['event_name'] ?? 'N/A'); ?></td>
                        <td><?php echo e($proposal['customer_name'] ?? 'N/A'); ?></td>
                        <?php if ($isAdmin): ?>
                        <td><?php echo e($proposal['username'] ?? 'N/A'); ?></td>
                        <?php endif; ?>
                        <td><?php echo date('Y-m-d H:i', strtotime($proposal['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo BASE_URL; ?>index.php?action=proposal_view&id=<?php echo $proposal['id']; ?>" 
                                   class="btn btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <div class="btn-group btn-group-sm">
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
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        $baseUrl = BASE_URL . 'index.php?action=proposals';
        echo generatePagination($page, $totalPages, $baseUrl);
        ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

