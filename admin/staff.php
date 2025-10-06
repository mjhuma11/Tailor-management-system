<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query
$where_conditions = ["s.deleted_at IS NULL"];
$params = [];

if ($search) {
    $where_conditions[] = "(s.fullname LIKE ? OR s.phone LIKE ? OR s.employee_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter) {
    $where_conditions[] = "s.status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(" AND ", $where_conditions);

try {
    // Get total count
    $count_query = "
        SELECT COUNT(*) as total 
        FROM staff s 
        LEFT JOIN staff_types st ON s.staff_type_id = st.id 
        WHERE $where_clause
    ";
    $stmt = $mysqli->prepare($count_query);
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $limit);
    $stmt->close();

    // Get staff
    $query = "
        SELECT s.*, st.title as staff_type_title, st.base_salary as base_salary
        FROM staff s 
        LEFT JOIN staff_types st ON s.staff_type_id = st.id 
        WHERE $where_clause 
        ORDER BY s.created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $mysqli->prepare($query);
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = [];
    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }
    $stmt->close();

} catch(Exception $e) {
    showError("Error fetching staff: " . $e->getMessage());
    $staff = [];
    $total_pages = 0;
}

// Handle quick status updates
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    try {
        $staff_id = (int)$_POST['staff_id'];
        $new_status = sanitize($_POST['new_status']);
        
        $stmt = $mysqli->prepare("UPDATE staff SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $new_status, $staff_id);
        $stmt->execute();
        $stmt->close();
        
        showSuccess("Staff status updated successfully!");
        header("Location: staff.php?" . http_build_query($_GET));
        exit();
        
    } catch(Exception $e) {
        showError("Error updating staff status: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - The Stitch House Admin</title>
    
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
                                    <a class="nav-link active" href="staff.php">View Staff</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="add-staff.php">Add Staff</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="staff-types.php">Staff Types</a>
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
                        <h1 class="content-title">Staff Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Staff</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="add-staff.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Staff
                        </a>
                        <a href="staff-types.php" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-user-tag"></i> Staff Types
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

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="staff.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Staff</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Name, phone, or employee ID">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status Filter</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="terminated" <?= $status_filter == 'terminated' ? 'selected' : '' ?>>Terminated</option>
                            </select>
                        </div>
                        
                        <div class="col-md-5 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="staff.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tie"></i> Staff List 
                        <span class="badge bg-primary ms-2"><?= $total_records ?> Total</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($staff)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No staff members found</h5>
                            <p class="text-muted">Start by adding your first staff member</p>
                            <a href="add-staff.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Staff
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Contact</th>
                                        <th>Position</th>
                                        <th>Salary</th>
                                        <th>Hire Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff as $employee): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="employee-avatar me-3">
                                                        <?php if ($employee['avatar']): ?>
                                                            <img src="<?= htmlspecialchars($employee['avatar']) ?>" 
                                                                 alt="Avatar" class="rounded-circle" width="40" height="40">
                                                        <?php else: ?>
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <?= strtoupper(substr($employee['fullname'], 0, 1)) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($employee['fullname']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">ID: <?= htmlspecialchars($employee['employee_id']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="fas fa-phone text-muted"></i> <?= htmlspecialchars($employee['phone']) ?>
                                                    <br>
                                                    <small class="text-muted"><?= ucfirst($employee['gender']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($employee['staff_type_title'] ?: 'No Type') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= formatCurrency($employee['salary']) ?></strong>
                                                <?php if ($employee['base_salary'] && $employee['salary'] != $employee['base_salary']): ?>
                                                    <br>
                                                    <small class="text-muted">Base: <?= formatCurrency($employee['base_salary']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= formatDate($employee['hire_date']) ?></td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'active' => 'success',
                                                    'inactive' => 'warning',
                                                    'terminated' => 'danger'
                                                ];
                                                $color = $status_colors[$employee['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?>">
                                                    <?= ucfirst($employee['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                            data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="edit-staff.php?id=<?= $employee['id'] ?>">
                                                                <i class="fas fa-edit"></i> Edit Staff
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="updateStatus(<?= $employee['id'] ?>, 'active')">
                                                                <i class="fas fa-check text-success"></i> Mark Active
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="updateStatus(<?= $employee['id'] ?>, 'inactive')">
                                                                <i class="fas fa-pause text-warning"></i> Mark Inactive
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="updateStatus(<?= $employee['id'] ?>, 'terminated')">
                                                                <i class="fas fa-times text-danger"></i> Terminate
                                                            </button>
                                                        </li>
                                                    </ul>
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
                                <nav aria-label="Staff pagination">
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

    <!-- Status Update Form (Hidden) -->
    <form id="statusUpdateForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="staff_id" id="statusStaffId">
        <input type="hidden" name="new_status" id="statusNewStatus">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function updateStatus(staffId, newStatus) {
            let confirmMessage = `Are you sure you want to mark this staff member as ${newStatus}?`;
            
            if (newStatus === 'terminated') {
                confirmMessage = 'Are you sure you want to terminate this staff member? This is a serious action.';
            }
            
            if (confirm(confirmMessage)) {
                document.getElementById('statusStaffId').value = staffId;
                document.getElementById('statusNewStatus').value = newStatus;
                document.getElementById('statusUpdateForm').submit();
            }
        }
    </script>
</body>
</html>