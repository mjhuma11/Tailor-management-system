<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Get staff types for dropdown
try {
    $result = $mysqli->query("SELECT id, title, base_salary FROM staff_types ORDER BY title");
    $staffTypes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $staffTypes[] = $row;
        }
    }
} catch(Exception $e) {
    $staffTypes = [];
}

// Handle form submission
if ($_POST) {
    try {
        $staff_type_id = !empty($_POST['staff_type_id']) ? (int)$_POST['staff_type_id'] : null;
        $fullname = sanitize($_POST['fullname']);
        $address = sanitize($_POST['address']);
        $gender = sanitize($_POST['gender']);
        $phone = sanitize($_POST['phone']);
        $salary = sanitize($_POST['salary']);
        $hire_date = sanitize($_POST['hire_date']);
        
        // Generate employee ID
        do {
            $employee_id = 'EMP' . date('Y') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $check_stmt = $mysqli->prepare("SELECT id FROM staff WHERE employee_id = ?");
            $check_stmt->bind_param("s", $employee_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $check_stmt->close();
        } while ($check_result->num_rows > 0);
        
        $stmt = $mysqli->prepare("
            INSERT INTO staff (
                staff_type_id, fullname, address, gender, phone, salary, 
                hire_date, employee_id, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        
        $stmt->bind_param(
            "issssdss", 
            $staff_type_id, $fullname, $address, $gender, $phone, 
            $salary, $hire_date, $employee_id
        );
        $stmt->execute();
        $stmt->close();
        
        showSuccess("Staff member added successfully! Employee ID: $employee_id");
        header("Location: staff.php");
        exit();
        
    } catch(Exception $e) {
        showError("Error adding staff member: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff - The Stitch House Admin</title>
    
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
                                    <a class="nav-link active" href="add-staff.php">Add Staff</a>
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
                        <h1 class="content-title">Add New Staff Member</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="staff.php">Staff</a></li>
                                <li class="breadcrumb-item active">Add Staff</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="staff.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View Staff
                        </a>
                        <a href="staff-types.php" class="btn btn-outline-secondary ms-2">
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

            <!-- Add Staff Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-plus"></i> Staff Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fullname" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address *</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="gender" class="form-label">Gender *</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="hire_date" class="form-label">Hire Date *</label>
                                        <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="staff_type_id" class="form-label">Staff Type</label>
                                        <select class="form-select" id="staff_type_id" name="staff_type_id">
                                            <option value="">Select Staff Type</option>
                                            <?php foreach ($staffTypes as $type): ?>
                                                <option value="<?= $type['id'] ?>" data-salary="<?= $type['base_salary'] ?>">
                                                    <?= htmlspecialchars($type['title']) ?> - Base: <?= formatCurrency($type['base_salary']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            <a href="staff-types.php" class="text-decoration-none">
                                                <i class="fas fa-plus"></i> Add New Staff Type
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="salary" class="form-label">Salary *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="salary" name="salary" 
                                                   step="0.01" min="0" required>
                                        </div>
                                        <div class="form-text">Monthly salary amount</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="staff.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Add Staff Member
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
                                <i class="fas fa-info-circle"></i> Employee Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-id-badge text-primary"></i>
                                    <strong>Employee ID:</strong> Auto-generated
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar text-success"></i>
                                    <strong>Status:</strong> Active (default)
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-user-circle text-info"></i>
                                    <strong>Avatar:</strong> Can be added later
                                </li>
                                <li>
                                    <i class="fas fa-key text-warning"></i>
                                    <strong>User Account:</strong> Can be linked later
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Staff Types Summary -->
                    <?php if (!empty($staffTypes)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-user-tag"></i> Available Staff Types
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($staffTypes as $type): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($type['title']) ?></strong>
                                            </div>
                                            <span class="badge bg-primary"><?= formatCurrency($type['base_salary']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tag fa-2x text-muted mb-3"></i>
                                <h6 class="text-muted">No Staff Types Available</h6>
                                <p class="text-muted small">Create staff types first to categorize employees</p>
                                <a href="staff-types.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add Staff Type
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
        // Auto-fill salary when staff type is selected
        document.getElementById('staff_type_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const baseSalary = selectedOption.getAttribute('data-salary');
            
            if (baseSalary) {
                document.getElementById('salary').value = baseSalary;
            }
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            let formattedValue = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            if (value.length <= 10) {
                this.value = formattedValue;
            }
        });
    </script>
</body>
</html>