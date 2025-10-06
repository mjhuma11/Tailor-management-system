<?php
/**
 * Admin Header Template
 * The Stitch House - Common Header for All Admin Pages
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Panel' ?> - The Stitch House</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Admin CSS -->
    <link href="assets/admin.css" rel="stylesheet">
    
    <?php if (isset($additionalCSS)): ?>
        <?= $additionalCSS ?>
    <?php endif; ?>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-outline-secondary d-lg-none me-3 sidebar-toggle" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="page-title"><?= strtoupper($pageTitle ?? 'ADMIN PANEL') ?></h1>
                    </div>
                    <div class="header-actions">
                        <a href="../index.php" class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Website
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-4 mt-3">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="admin-content">