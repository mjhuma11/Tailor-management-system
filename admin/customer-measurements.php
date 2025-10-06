<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Get customer ID from URL
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

if (!$customer_id) {
    showError("Invalid customer ID");
    header("Location: customers.php");
    exit();
}

// Get customer details
try {
    $stmt = $mysqli->prepare("SELECT * FROM customer WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();
    
    if (!$customer) {
        showError("Customer not found");
        header("Location: customers.php");
        exit();
    }
} catch(Exception $e) {
    showError("Error fetching customer: " . $e->getMessage());
    header("Location: customers.php");
    exit();
}

// Handle form submission for adding/updating measurements
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'save_measurements') {
    try {
        $measurement_date = sanitize($_POST['measurement_date']);
        $notes = sanitize($_POST['notes']);
        
        // Process each measurement part
        foreach ($_POST['measurements'] as $part_id => $value) {
            if (!empty($value) && is_numeric($value)) {
                $part_id = (int)$part_id;
                $value = (float)$value;
                
                // Check if measurement already exists
                $stmt = $mysqli->prepare("SELECT id FROM measurement WHERE customer_id = ? AND measurement_part_id = ?");
                $stmt->bind_param("ii", $customer_id, $part_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $existing = $result->fetch_assoc();
                $stmt->close();
                
                if ($existing) {
                    // Update existing measurement
                    $stmt = $mysqli->prepare("UPDATE measurement SET value = ?, notes = ?, measurement_date = ?, taken_by = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("dssii", $value, $notes, $measurement_date, $_SESSION['user_id'], $existing['id']);
                } else {
                    // Insert new measurement
                    $stmt = $mysqli->prepare("INSERT INTO measurement (customer_id, measurement_part_id, value, notes, measurement_date, taken_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("iidssi", $customer_id, $part_id, $value, $notes, $measurement_date, $_SESSION['user_id']);
                }
                $stmt->execute();
                $stmt->close();
            }
        }
        
        showSuccess("Measurements saved successfully!");
        header("Location: customer-measurements.php?customer_id=$customer_id");
        exit();
        
    } catch(Exception $e) {
        showError("Error saving measurements: " . $e->getMessage());
    }
}

// Get measurement parts based on customer gender
$gender_filter = $customer['gender'];
try {
    $stmt = $mysqli->prepare("SELECT * FROM measurement_part WHERE (gender = ? OR gender = 'Both') AND status = 'active' AND deleted_at IS NULL ORDER BY sort_order, name");
    $stmt->bind_param("s", $gender_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    $measurementParts = [];
    while ($row = $result->fetch_assoc()) {
        $measurementParts[] = $row;
    }
    $stmt->close();
} catch(Exception $e) {
    $measurementParts = [];
    showError("Error fetching measurement parts: " . $e->getMessage());
}

// Get existing measurements for this customer
try {
    $stmt = $mysqli->prepare("
        SELECT m.*, mp.name as part_name, mp.unit, u.name as taken_by_name
        FROM measurement m 
        JOIN measurement_part mp ON m.measurement_part_id = mp.id 
        LEFT JOIN users u ON m.taken_by = u.id
        WHERE m.customer_id = ? 
        ORDER BY mp.sort_order, mp.name
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingMeasurements = [];
    while ($row = $result->fetch_assoc()) {
        $existingMeasurements[$row['measurement_part_id']] = $row;
    }
    $stmt->close();
} catch(Exception $e) {
    $existingMeasurements = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Measurements - The Stitch House Admin</title>
    
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
                        <h1 class="page-title">CUSTOMER MEASUREMENTS</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="customers.php">Customers</a></li>
                                <li class="breadcrumb-item active">Measurements</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="customers.php" class="btn btn-outline-primary">
                            <i class="fas fa-users"></i> Back to Customers
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

            <!-- Customer Info -->
            <div class="admin-content">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4 class="card-title mb-2">
                                            <i class="fas fa-user"></i> <?= htmlspecialchars($customer['fullname']) ?>
                                        </h4>
                                        <p class="card-text mb-0">
                                            <i class="fas fa-venus-mars"></i> <?= $customer['gender'] ?>
                                            <span class="ms-3"><i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?></span>
                                            <?php if ($customer['email']): ?>
                                                <span class="ms-3"><i class="fas fa-envelope"></i> <?= htmlspecialchars($customer['email']) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-light text-dark fs-6">
                                            Customer ID: <?= $customer['customer_code'] ?? $customer['id'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Measurements Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-ruler-combined"></i> Take Measurements
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($measurementParts)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-ruler-combined fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No measurement parts available</h5>
                                        <p class="text-muted">Please add measurement parts first</p>
                                        <a href="measurement-parts.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Measurement Parts
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="save_measurements">
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="measurement_date" class="form-label">Measurement Date *</label>
                                                <input type="date" class="form-control" id="measurement_date" name="measurement_date" 
                                                       value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="notes" class="form-label">General Notes</label>
                                                <input type="text" class="form-control" id="notes" name="notes" 
                                                       placeholder="Any special notes about these measurements">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <?php foreach ($measurementParts as $part): ?>
                                                <div class="col-md-6 mb-3">
                                                    <label for="measurement_<?= $part['id'] ?>" class="form-label">
                                                        <?= htmlspecialchars($part['name']) ?>
                                                        <span class="text-muted">(<?= $part['unit'] ?>)</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="measurement_<?= $part['id'] ?>" 
                                                               name="measurements[<?= $part['id'] ?>]" 
                                                               step="0.25" 
                                                               min="0"
                                                               value="<?= isset($existingMeasurements[$part['id']]) ? $existingMeasurements[$part['id']]['value'] : '' ?>"
                                                               placeholder="Enter measurement">
                                                        <span class="input-group-text"><?= $part['unit'] ?></span>
                                                    </div>
                                                    <?php if ($part['description']): ?>
                                                        <div class="form-text"><?= htmlspecialchars($part['description']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-4">
                                            <a href="customers.php" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Measurements
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Measurement History -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-history"></i> Measurement History
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($existingMeasurements)): ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-ruler fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No measurements recorded yet</p>
                                    </div>
                                <?php else: ?>
                                    <div class="measurement-history">
                                        <?php foreach ($existingMeasurements as $measurement): ?>
                                            <div class="measurement-item mb-3 p-2 border rounded">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?= htmlspecialchars($measurement['part_name']) ?></strong>
                                                        <div class="text-primary fs-5"><?= $measurement['value'] ?> <?= $measurement['unit'] ?></div>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= formatDate($measurement['measurement_date']) ?>
                                                    </small>
                                                </div>
                                                <?php if ($measurement['taken_by_name']): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> <?= htmlspecialchars($measurement['taken_by_name']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-tools"></i> Quick Actions
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="add-order.php?customer_id=<?= $customer_id ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Create Order
                                    </a>
                                    <a href="orders.php?customer_id=<?= $customer_id ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-list"></i> View Orders
                                    </a>
                                    <a href="measurement-parts.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-cog"></i> Manage Parts
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-save functionality (optional)
        document.addEventListener('DOMContentLoaded', function() {
            const measurementInputs = document.querySelectorAll('input[name^="measurements"]');
            
            measurementInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    // You can add auto-save functionality here if needed
                    console.log('Measurement updated:', this.name, this.value);
                });
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const measurementInputs = document.querySelectorAll('input[name^="measurements"]');
            let hasValue = false;
            
            measurementInputs.forEach(input => {
                if (input.value && input.value.trim() !== '') {
                    hasValue = true;
                }
            });
            
            if (!hasValue) {
                e.preventDefault();
                alert('Please enter at least one measurement value.');
                return false;
            }
        });
    </script>
</body>
</html>