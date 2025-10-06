<?php
require_once 'includes/config.php';
requireAuth();

// Get customer ID from URL
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

if (!$customer_id) {
    showError("Invalid customer ID");
    header("Location: measurements.php");
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
        header("Location: measurements.php");
        exit();
    }
} catch(Exception $e) {
    showError("Error fetching customer: " . $e->getMessage());
    header("Location: measurements.php");
    exit();
}

// Get customer measurements
try {
    $stmt = $mysqli->prepare("
        SELECT m.*, mp.name as part_name, mp.unit, mp.description, u.name as taken_by_name
        FROM measurement m 
        JOIN measurement_part mp ON m.measurement_part_id = mp.id 
        LEFT JOIN users u ON m.taken_by = u.id
        WHERE m.customer_id = ? 
        ORDER BY mp.sort_order, mp.name
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $measurements = [];
    while ($row = $result->fetch_assoc()) {
        $measurements[] = $row;
    }
    $stmt->close();
} catch(Exception $e) {
    $measurements = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Measurement Report - <?= htmlspecialchars($customer['fullname']) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { font-size: 12px; }
            .card { border: 1px solid #000 !important; box-shadow: none !important; }
            .table { font-size: 11px; }
        }
        
        .print-only { display: none; }
        
        .measurement-report {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .company-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .measurement-table th {
            background: #e9ecef;
            font-weight: 600;
        }
        
        .signature-section {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Print Controls -->
        <div class="no-print mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="measurements.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Measurements
                    </a>
                    <a href="customer-measurements.php?customer_id=<?= $customer_id ?>" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-edit"></i> Edit Measurements
                    </a>
                </div>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>
        </div>

        <!-- Report Content -->
        <div class="measurement-report">
            <!-- Company Header -->
            <div class="company-header">
                <h1>The Stitch House</h1>
                <p class="mb-0">Customer Measurement Report</p>
                <small class="text-muted">Perfect Fit, Perfect Style</small>
            </div>

            <!-- Customer Information -->
            <div class="customer-info">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3">Customer Information</h4>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?= htmlspecialchars($customer['fullname']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Customer ID:</strong></td>
                                <td><?= htmlspecialchars($customer['customer_code'] ?? $customer['id']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><?= htmlspecialchars($customer['phone']) ?></td>
                            </tr>
                            <?php if ($customer['email']): ?>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?= htmlspecialchars($customer['email']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Gender:</strong></td>
                                <td><?= $customer['gender'] ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-3">Report Information</h4>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Report Date:</strong></td>
                                <td><?= date('F d, Y') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Measurements:</strong></td>
                                <td><?= count($measurements) ?></td>
                            </tr>
                            <?php if (!empty($measurements)): ?>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td><?= formatDate($measurements[0]['measurement_date']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Measurements Table -->
            <?php if (empty($measurements)): ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <h5>No Measurements Available</h5>
                    <p>This customer does not have any measurements recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-ruler-combined"></i> Customer Measurements
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped measurement-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Measurement Part</th>
                                    <th style="width: 15%;">Value</th>
                                    <th style="width: 10%;">Unit</th>
                                    <th style="width: 30%;">Description</th>
                                    <th style="width: 15%;">Date Taken</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($measurements as $measurement): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($measurement['part_name']) ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6"><?= $measurement['value'] ?></span>
                                        </td>
                                        <td class="text-center"><?= $measurement['unit'] ?></td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($measurement['description']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= formatDate($measurement['measurement_date']) ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Measurement Summary -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Measurement Notes</h6>
                            </div>
                            <div class="card-body">
                                <?php 
                                $notes = array_filter(array_column($measurements, 'notes'));
                                if (!empty($notes)): 
                                ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach (array_unique($notes) as $note): ?>
                                            <li><i class="fas fa-sticky-note text-warning"></i> <?= htmlspecialchars($note) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No special notes recorded.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Measurement History</h6>
                            </div>
                            <div class="card-body">
                                <?php 
                                $taken_by = array_filter(array_unique(array_column($measurements, 'taken_by_name')));
                                if (!empty($taken_by)): 
                                ?>
                                    <p><strong>Measured by:</strong></p>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($taken_by as $person): ?>
                                            <li><i class="fas fa-user text-primary"></i> <?= htmlspecialchars($person) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No measurement history available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center">
                            <div style="border-top: 1px solid #333; width: 200px; margin: 0 auto;"></div>
                            <small class="text-muted mt-2 d-block">Customer Signature</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center">
                            <div style="border-top: 1px solid #333; width: 200px; margin: 0 auto;"></div>
                            <small class="text-muted mt-2 d-block">Tailor Signature</small>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <small class="text-muted">
                        Report generated on <?= date('F d, Y \a\t g:i A') ?> | The Stitch House Management System
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>