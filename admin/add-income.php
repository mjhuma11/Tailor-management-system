<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();
$pageTitle = 'Add Income';

// Get income categories for dropdown
try {
    $result = $mysqli->query("SELECT id, name FROM inc_cat WHERE status = 'active' ORDER BY name");
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
} catch(Exception $e) {
    $categories = [];
}

// Handle form submission
if ($_POST) {
    try {
        $inc_cat_id = !empty($_POST['inc_cat_id']) ? (int)$_POST['inc_cat_id'] : null;
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $income_date = sanitize($_POST['income_date']);
        $amount = sanitize($_POST['amount']);
        $receipt_number = sanitize($_POST['receipt_number']);
        $client_name = sanitize($_POST['client_name']);
        $payment_method = sanitize($_POST['payment_method']);
        
        $stmt = $mysqli->prepare("
            INSERT INTO income (
                inc_cat_id, title, description, income_date, amount, 
                receipt_number, client_name, payment_method, added_by, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->bind_param("isssdssssi", $inc_cat_id, $title, $description, $income_date, $amount,
            $receipt_number, $client_name, $payment_method, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
        
        showSuccess("Income record added successfully!");
        header("Location: income.php");
        exit();
        
    } catch(Exception $e) {
        showError("Error adding income record: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="collapse" data-bs-target="#incomeMenu">
                            <i class="fas fa-coins"></i>
                            <span>Income Management</span>
                        </a>
                        <div class="collapse show" id="incomeMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="income.php">View Income</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" href="add-income.php">Add Income</a>
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
                        <h1 class="content-title">Add Income Record</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="income.php">Income</a></li>
                                <li class="breadcrumb-item active">Add Income</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="income.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View Income
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

            <!-- Add Income Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-plus-circle"></i> Income Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="title" class="form-label">Income Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required
                                               placeholder="e.g., Order Payment, Service Fee">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="inc_cat_id" class="form-label">Category</label>
                                        <select class="form-select" id="inc_cat_id" name="inc_cat_id">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            <a href="income-categories.php" class="text-decoration-none">
                                                <i class="fas fa-plus"></i> Add New Category
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                              placeholder="Additional details about this income..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="amount" class="form-label">Amount *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="amount" name="amount" 
                                                   step="0.01" min="0" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="income_date" class="form-label">Income Date *</label>
                                        <input type="date" class="form-control" id="income_date" name="income_date" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="client_name" class="form-label">Client Name</label>
                                        <input type="text" class="form-control" id="client_name" name="client_name"
                                               placeholder="Customer or client name">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="receipt_number" class="form-label">Receipt Number</label>
                                        <input type="text" class="form-control" id="receipt_number" name="receipt_number"
                                               placeholder="Receipt or invoice number">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_method" class="form-label">Payment Method *</label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="card">Credit/Debit Card</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="income.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Add Income Record
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Quick Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Income Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-calendar text-primary"></i>
                                    <strong>Date:</strong> Today by default
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-user text-success"></i>
                                    <strong>Added by:</strong> <?= htmlspecialchars($currentAdmin['name']) ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-tags text-info"></i>
                                    <strong>Categories:</strong> Optional grouping
                                </li>
                                <li>
                                    <i class="fas fa-receipt text-warning"></i>
                                    <strong>Receipt:</strong> Optional tracking number
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Categories Summary -->
                    <?php if (!empty($categories)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-tags"></i> Available Categories
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($categories as $category): ?>
                                        <div class="list-group-item">
                                            <strong><?= htmlspecialchars($category['name']) ?></strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-tags fa-2x text-muted mb-3"></i>
                                <h6 class="text-muted">No Categories Available</h6>
                                <p class="text-muted small">Create categories to organize income types</p>
                                <a href="income-categories.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add Category
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-generate receipt number based on date and amount
        document.getElementById('amount').addEventListener('blur', function() {
            const receiptField = document.getElementById('receipt_number');
            
            if (!receiptField.value && this.value) {
                const date = new Date();
                const timestamp = date.getTime().toString().slice(-6);
                receiptField.value = 'INC' + timestamp;
            }
        });
    </script>
</body>
</html>