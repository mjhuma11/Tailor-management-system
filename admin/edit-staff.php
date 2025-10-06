<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Get staff ID from URL
$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$staff_id) {
    showError("Invalid staff ID");
    header("Location: staff.php");
    exit();
}

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

// Get staff member details
try {
    $stmt = $mysqli->prepare("
        SELECT s.*, st.title as staff_type_title 
        FROM staff s 
        LEFT JOIN staff_types st ON s.staff_type_id = st.id 
        WHERE s.id = ? AND s.deleted_at IS NULL
    ");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    $stmt->close();
    
    if (!$staff) {
        showError("Staff member not found");
        header("Location: staff.php");
        exit();
    }
} catch(Exception $e) {
    showError("Error fetching staff details: " . $e->getMessage());
    header("Location: staff.php");
    exit();
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
        $status = sanitize($_POST['status']);
        
        $stmt = $mysqli->prepare("
            UPDATE staff SET 
                staff_type_id = ?, fullname = ?, address = ?, gender = ?, 
                phone = ?, salary = ?, hire_date = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "issssdsi", 
            $staff_type_id, $fullname, $address, $gender, 
            $phone, $salary, $hire_date, $status, $staff_id
        );
        $stmt->execute();
        $stmt->close();
        
        showSuccess("Staff member updated successfully!");
        header("Location: staff.php");
        exit();
        
    } catch(Exception $e) {
        showError("Error updating staff member: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff - The Stitch House Admin</title>
    
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
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Header -->
            <div class="admin-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">EDIT STAFF MEMBER</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="staff.php">Staff</a></li>
                                <li class="breadcrumb-item active">Edit Staff</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="staff.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Back to Staff
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if ($flashMessages): ?>
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?= $type == 'error' ? 'danger' : $type ?> alert-dismissible fade show mx-4 mt-3">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Edit Staff Form -->
            <div class="admin-content">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-edit"></i> Edit Staff Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="fullname" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" 
                                                   value="<?= htmlspecialchars($staff['fullname']) ?>" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone Number *</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?= htmlspecialchars($staff['phone']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address *</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($staff['address']) ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="gender" class="form-label">Gender *</label>
                                            <select class="form-select" id="gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male" <?= $staff['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                                <option value="Female" <?= $staff['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                                <option value="Other" <?= $staff['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="hire_date" class="form-label">Hire Date *</label>
                                            <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                                   value="<?= $staff['hire_date'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="staff_type_id" class="form-label">Staff Type</label>
                                            <select class="form-select" id="staff_type_id" name="staff_type_id">
                                                <option value="">Select Staff Type</option>
                                                <?php foreach ($staffTypes as $type): ?>
                                                    <option value="<?= $type['id'] ?>" 
                                                            data-salary="<?= $type['base_salary'] ?>"
                                                            <?= $staff['staff_type_id'] == $type['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($type['title']) ?> - Base: <?= formatCurrency($type['base_salary']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="salary" class="form-label">Salary *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="salary" name="salary" 
                                                       step="0.01" min="0" value="<?= $staff['salary'] ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active" <?= $staff['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $staff['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            <option value="terminated" <?= $staff['status'] == 'terminated' ? 'selected' : '' ?>>Terminated</option>
                                        </select>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="staff.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Staff Member
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Staff Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle"></i> Staff Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-id-badge text-primary"></i>
                                        <strong>Employee ID:</strong> <?= htmlspecialchars($staff['employee_id']) ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar text-success"></i>
                                        <strong>Hired:</strong> <?= formatDate($staff['hire_date']) ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-info"></i>
                                        <strong>Created:</strong> <?= formatDate($staff['created_at']) ?>
                                    </li>
                                    <li>
                                        <i class="fas fa-edit text-warning"></i>
                                        <strong>Last Updated:</strong> <?= formatDate($staff['updated_at']) ?>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Current Staff Type -->
                        <?php if ($staff['staff_type_title']): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-user-tag"></i> Current Staff Type
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <span class="badge bg-info fs-6"><?= htmlspecialchars($staff['staff_type_title']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
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
            
            if (baseSalary && confirm('Do you want to update the salary to the base salary for this staff type?')) {
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