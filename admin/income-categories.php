<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Handle form submission for adding/editing income categories
if ($_POST) {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $name = sanitize($_POST['name']);
                    $description = sanitize($_POST['description']);
                    $status = sanitize($_POST['status']);
                    
                    $stmt = $mysqli->prepare("INSERT INTO inc_cat (name, description, status, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("sss", $name, $description, $status);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Income category created successfully!");
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $name = sanitize($_POST['name']);
                    $description = sanitize($_POST['description']);
                    $status = sanitize($_POST['status']);
                    
                    $stmt = $mysqli->prepare("UPDATE inc_cat SET name = ?, description = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("sssi", $name, $description, $status, $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Income category updated successfully!");
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    $stmt = $mysqli->prepare("DELETE FROM inc_cat WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Income category deleted successfully!");
                    break;
            }
        }
        header("Location: income-categories.php");
        exit();
    } catch(Exception $e) {
        showError("Error: " . $e->getMessage());
    }
}

// Get all income categories
try {
    $result = $mysqli->query("SELECT * FROM inc_cat ORDER BY name");
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
} catch(Exception $e) {
    $categories = [];
    showError("Error fetching income categories: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Categories - The Stitch House Admin</title>
    
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
                                    <a class="nav-link" href="income.php">View Income</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="add-income.php">Add Income</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" href="income-categories.php">Categories</a>
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
                        <h1 class="content-title">Income Categories</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="income.php">Income</a></li>
                                <li class="breadcrumb-item active">Categories</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
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

            <!-- Categories Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags"></i> Income Categories 
                        <span class="badge bg-success ms-2"><?= count($categories) ?> Categories</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($categories)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No income categories found</h5>
                            <p class="text-muted">Create categories to organize your income types</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus"></i> Add First Category
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($category['description']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $category['status'] == 'active' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($category['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= formatDate($category['created_at']) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>', '<?= htmlspecialchars($category['description']) ?>', '<?= $category['status'] ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Income Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Income Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editId">
                        
                        <div class="mb-3">
                            <label for="editName" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editCategory(id, name, description, status) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editStatus').value = status;
            
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }

        function deleteCategory(id, name) {
            if (confirm(`Are you sure you want to delete the category "${name}"? This action cannot be undone.`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>