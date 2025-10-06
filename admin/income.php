<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($pdo);
$flashMessages = getFlashMessages();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

// Build query
$where_conditions = ["i.deleted_at IS NULL"];
$params = [];

if ($search) {
    $where_conditions[] = "(i.title LIKE ? OR i.client_name LIKE ? OR i.receipt_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($category_filter) {
    $where_conditions[] = "i.inc_cat_id = ?";
    $params[] = $category_filter;
}

if ($date_from) {
    $where_conditions[] = "i.income_date >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "i.income_date <= ?";
    $params[] = $date_to;
}

$where_clause = implode(" AND ", $where_conditions);

try {
    // Get total count and sum
    $count_query = "
        SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as total_amount 
        FROM income i 
        LEFT JOIN inc_cat ic ON i.inc_cat_id = ic.id 
        WHERE $where_clause
    ";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $summary = $stmt->fetch();
    $total_records = $summary['total'];
    $total_amount = $summary['total_amount'];
    $total_pages = ceil($total_records / $limit);

    // Get income records
    $query = "
        SELECT i.*, ic.name as category_name, u.name as added_by_name
        FROM income i 
        LEFT JOIN inc_cat ic ON i.inc_cat_id = ic.id 
        LEFT JOIN users u ON i.added_by = u.id
        WHERE $where_clause 
        ORDER BY i.income_date DESC, i.created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $income_records = $stmt->fetchAll();

    // Get categories for filter
    $stmt = $pdo->query("SELECT id, name FROM inc_cat WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();

} catch(PDOException $e) {
    showError("Error fetching income records: " . $e->getMessage());
    $income_records = [];
    $categories = [];
    $total_pages = 0;
    $total_amount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Management - The Stitch House Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <link href="assets/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h4 class="sidebar-title">The Stitch House</h4>
            </div>
            
            <div class="sidebar-menu">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="collapse" data-bs-target="#incomeMenu">
                            <i class="fas fa-coins"></i>
                            <span>Income Management</span>
                        </a>
                        <div class="collapse show" id="incomeMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link active" href="income.php">View Income</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="add-income.php">Add Income</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="income-categories.php">Categories</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-footer">
                <div class="admin-user-info">
                    <div class="admin-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="admin-details">
                        <span class="admin-name"><?= htmlspecialchars($currentAdmin['name']) ?></span>
                        <small class="admin-role"><?= ucfirst($currentAdmin['role']) ?></small>
                    </div>
                    <a href="logout.php" class="logout-btn" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Top Bar -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="content-title">Income Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Income</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="add-income.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Income
                        </a>
                        <a href="income-categories.php" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if ($flashMessages): ?>
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?= $type == 'error' ? 'danger' : $type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1">Total Income</h5>
                                    <h3 class="mb-0"><?= formatCurrency($total_amount) ?></h3>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-coins fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1">Total Records</h5>
                                    <h3 class="mb-0"><?= $total_records ?></h3>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-receipt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="income.php" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Title, client, or receipt">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="income.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Income Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave"></i> Income Records
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($income_records)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-coins fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No income records found</h5>
                            <p class="text-muted">Start by adding your first income record</p>
                            <a href="add-income.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Income
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Receipt #</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($income_records as $record): ?>
                                        <tr>
                                            <td>
                                                <strong><?= formatDate($record['income_date']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= formatDate($record['created_at'], 'H:i') ?></small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($record['title']) ?></strong>
                                                <?php if ($record['description']): ?>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars(substr($record['description'], 0, 50)) ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($record['category_name']): ?>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($record['category_name']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">No Category</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($record['client_name'] ?: 'N/A') ?>
                                            </td>
                                            <td>
                                                <strong class="text-success"><?= formatCurrency($record['amount']) ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $payment_icons = [
                                                    'cash' => 'fas fa-money-bill-wave text-success',
                                                    'bank' => 'fas fa-university text-primary',
                                                    'card' => 'fas fa-credit-card text-info',
                                                    'cheque' => 'fas fa-money-check text-warning'
                                                ];
                                                $icon = $payment_icons[$record['payment_method']] ?? 'fas fa-question';
                                                ?>
                                                <i class="<?= $icon ?>"></i> <?= ucfirst($record['payment_method']) ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($record['receipt_number'] ?: 'N/A') ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit-income.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteIncome(<?= $record['id'] ?>, '<?= htmlspecialchars($record['title']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Income pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function deleteIncome(id, title) {
            if (confirm(`Are you sure you want to delete the income record "${title}"? This action cannot be undone.`)) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-income.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Set date_to to today if date_from is selected but date_to is empty
        document.getElementById('date_from').addEventListener('change', function() {
            const dateTo = document.getElementById('date_to');
            if (this.value && !dateTo.value) {
                dateTo.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>