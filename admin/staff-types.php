<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Handle form submission for adding/editing staff types
if ($_POST) {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $title = sanitize($_POST['title']);
                    $description = sanitize($_POST['description']);
                    $base_salary = sanitize($_POST['base_salary']);
                    
                    $stmt = $mysqli->prepare("INSERT INTO staff_types (title, description, base_salary, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("ssd", $title, $description, $base_salary);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Staff type created successfully!");
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $title = sanitize($_POST['title']);
                    $description = sanitize($_POST['description']);
                    $base_salary = sanitize($_POST['base_salary']);
                    
                    $stmt = $mysqli->prepare("UPDATE staff_types SET title = ?, description = ?, base_salary = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("ssdi", $title, $description, $base_salary, $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Staff type updated successfully!");
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    $stmt = $mysqli->prepare("DELETE FROM staff_types WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Staff type deleted successfully!");
                    break;
            }
        }
        header("Location: staff-types.php");
        exit();
    } catch(Exception $e) {
        showError("Error: " . $e->getMessage());
    }
}

// Get all staff types
try {
    $result = $mysqli->query("SELECT * FROM staff_types ORDER BY title");
    $staffTypes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $staffTypes[] = $row;
        }
    }
} catch(Exception $e) {
    $staffTypes = [];
    showError("Error fetching staff types: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Types - The Stitch House Admin</title>
    
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
                    
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="fas fa-users"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-list-alt"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="collapse" data-bs-target="#staffMenu">
                            <i class="fas fa-user-tie"></i>
                            <span>Staff Management</span>
                        </a>
                        <div class="collapse show" id="staffMenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="staff.php">View Staff</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="add-staff.php">Add Staff</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" href="staff-types.php">Staff Types</a>
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
                        <h1 class="content-title">Staff Types Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="staff.php">Staff</a></li>
                                <li class="breadcrumb-item active">Staff Types</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffTypeModal">
                            <i class="fas fa-plus"></i> Add Staff Type
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

            <!-- Staff Types Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tag"></i> Staff Types 
                        <span class="badge bg-primary ms-2"><?= count($staffTypes) ?> Types</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($staffTypes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No staff types found</h5>
                            <p class="text-muted">Create staff types to categorize your employees</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffTypeModal">
                                <i class="fas fa-plus"></i> Add First Staff Type
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Base Salary</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staffTypes as $type): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($type['title']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($type['description']) ?></td>
                                            <td>
                                                <strong><?= formatCurrency($type['base_salary']) ?></strong>
                                            </td>
                                            <td><?= formatDate($type['created_at']) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editStaffType(<?= $type['id'] ?>, '<?= htmlspecialchars($type['title']) ?>', '<?= htmlspecialchars($type['description']) ?>', <?= $type['base_salary'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteStaffType(<?= $type['id'] ?>, '<?= htmlspecialchars($type['title']) ?>')">
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

    <!-- Add Staff Type Modal -->
    <div class="modal fade" id="addStaffTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Staff Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="base_salary" class="form-label">Base Salary *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="base_salary" name="base_salary" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Staff Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Staff Type Modal -->
    <div class="modal fade" id="editStaffTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Staff Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editId">
                        
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editBaseSalary" class="form-label">Base Salary *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="editBaseSalary" name="base_salary" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Staff Type</button>
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
        function editStaffType(id, title, description, baseSalary) {
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editDescription').value = description;
            document.getElementById('editBaseSalary').value = baseSalary;
            
            new bootstrap.Modal(document.getElementById('editStaffTypeModal')).show();
        }

        function deleteStaffType(id, title) {
            if (confirm(`Are you sure you want to delete the staff type "${title}"? This action cannot be undone.`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>