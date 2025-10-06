<?php
/**
 * Customer Self-Measurement Form
 * The Stitch House - Customer Portal
 */

require_once 'backend/config.php';

// Check if customer is logged in
if (!isCustomerLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirectWithMessage('login.php', 'Please log in to submit your measurements.', 'error');
}

$user = getCurrentUser($pdo);
$customer = getCurrentCustomer($pdo);

if (!$user || !$customer) {
    redirectWithMessage('login.php', 'Session expired. Please log in again.', 'error');
}

// Ensure gender is set with a default value
if (!isset($customer['gender']) || empty($customer['gender'])) {
    $customer['gender'] = 'Male'; // Default to Male if gender is not set
}

// Handle form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'submit_measurements') {
    try {
        $measurement_date = date('Y-m-d');
        $notes = sanitize($_POST['notes'] ?? '');
        $gender = $customer['gender'] ?? 'Male';
        
        // Define measurement fields based on gender
        $maleFields = [
            'neck' => 'Neck',
            'shoulder' => 'Shoulder', 
            'chest' => 'Chest',
            'waist' => 'Waist',
            'hip' => 'Hip',
            'sleeve_length' => 'Sleeve Length',
            'shirt_length' => 'Shirt Length',
            'pant_length' => 'Pant Length',
            'inseam' => 'Inseam',
            'thigh' => 'Thigh',
            'calf' => 'Calf'
        ];
        
        $femaleFields = [
            'shoulder' => 'Shoulder',
            'bust' => 'Bust',
            'waist' => 'Waist', 
            'hip' => 'Hip',
            'sleeve_length' => 'Sleeve Length',
            'armhole' => 'Armhole',
            'blouse_length' => 'Blouse Length',
            'dress_length' => 'Dress Length',
            'skirt_length' => 'Skirt Length',
            'around_neck' => 'Around Neck',
            'around_upper_arm' => 'Around Upper Arm'
        ];
        
        $fields = ($gender === 'Male') ? $maleFields : $femaleFields;
        
        // Process measurements
        $savedCount = 0;
        foreach ($fields as $fieldKey => $fieldName) {
            if (isset($_POST[$fieldKey]) && !empty($_POST[$fieldKey])) {
                $value = (float)$_POST[$fieldKey];
                
                // Get or create measurement part
                $stmt = $pdo->prepare("SELECT id FROM measurement_part WHERE name = ? AND (gender = ? OR gender = 'Both')");
                $stmt->execute([$fieldName, $gender]);
                $part = $stmt->fetch();
                
                if (!$part) {
                    // Create new measurement part
                    $stmt = $pdo->prepare("INSERT INTO measurement_part (name, description, gender, unit, sort_order, status, created_at, updated_at) VALUES (?, ?, ?, 'inches', 0, 'active', NOW(), NOW())");
                    $stmt->execute([$fieldName, "Customer submitted $fieldName measurement", $gender]);
                    $part_id = $pdo->lastInsertId();
                } else {
                    $part_id = $part['id'];
                }
                
                // Check if measurement exists
                $stmt = $pdo->prepare("SELECT id FROM measurement WHERE customer_id = ? AND measurement_part_id = ?");
                $stmt->execute([$customer['id'], $part_id]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update existing
                    $stmt = $pdo->prepare("UPDATE measurement SET value = ?, notes = ?, measurement_date = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$value, $notes, $measurement_date, $existing['id']]);
                } else {
                    // Insert new
                    $stmt = $pdo->prepare("INSERT INTO measurement (customer_id, measurement_part_id, value, notes, measurement_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$customer['id'], $part_id, $value, $notes, $measurement_date]);
                }
                $savedCount++;
            }
        }
        
        if ($savedCount > 0) {
            redirectWithMessage('customer-measurements.php', "Successfully saved $savedCount measurements!", 'success');
        } else {
            redirectWithMessage('customer-measurements.php', 'Please enter at least one measurement.', 'error');
        }
        
    } catch (Exception $e) {
        redirectWithMessage('customer-measurements.php', 'Error saving measurements: ' . $e->getMessage(), 'error');
    }
}

// Get existing measurements
try {
    $stmt = $pdo->prepare("
        SELECT m.*, mp.name as part_name 
        FROM measurement m 
        JOIN measurement_part mp ON m.measurement_part_id = mp.id 
        WHERE m.customer_id = ? 
        ORDER BY m.updated_at DESC
    ");
    $stmt->execute([$customer['id']]);
    $existingMeasurements = $stmt->fetchAll();
} catch (Exception $e) {
    $existingMeasurements = [];
}

// Get flash message
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Measurements - The Stitch House</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .measurement-form {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .measurement-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .measurement-header {
            background: linear-gradient(45deg, #e74c3c, #f39c12);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .measurement-field {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .measurement-field label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .measurement-input {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .measurement-input:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        
        .measurement-guide {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .guide-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        
        .guide-item:hover {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .guide-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #e74c3c, #f39c12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .body-diagram {
            max-width: 300px;
            margin: 0 auto;
            position: relative;
        }
        
        .measurement-point {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #e74c3c;
            border: 3px solid white;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .measurement-point:hover {
            transform: scale(1.2);
            background: #f39c12;
        }
        
        .measurement-tooltip {
            position: absolute;
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.9rem;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .measurement-tooltip.show {
            opacity: 1;
        }
        
        .existing-measurements {
            background: #e8f5e8;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .measurement-history-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="brand-text">The Stitch House</span>
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['name']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="customer-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="customer-measurements.php"><i class="fas fa-ruler-combined"></i> My Measurements</a></li>
                        <li><a class="dropdown-item" href="my-orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Measurement Form Section -->
    <section class="measurement-form">
        <div class="container">
            <!-- Flash Message -->
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type'] ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flashMessage['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Main Form -->
                <div class="col-lg-8">
                    <div class="measurement-card">
                        <div class="measurement-header">
                            <h2><i class="fas fa-ruler-combined"></i> Submit Your Measurements</h2>
                            <p class="mb-0">Help us create the perfect fit for you by providing your measurements</p>
                        </div>
                        
                        <div class="p-4">
                            <!-- Customer Info -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-user"></i> <?= htmlspecialchars($customer['fullname']) ?></h5>
                                    <p class="text-muted mb-0">Gender: <strong><?= isset($customer['gender']) ? $customer['gender'] : 'Not specified' ?></strong></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#measurementGuideModal">
                                        <i class="fas fa-question-circle"></i> Measurement Guide
                                    </button>
                                </div>
                            </div>

                            <!-- Existing Measurements -->
                            <?php if (!empty($existingMeasurements)): ?>
                                <div class="existing-measurements">
                                    <h6><i class="fas fa-history"></i> Your Current Measurements</h6>
                                    <div class="row">
                                        <?php foreach (array_slice($existingMeasurements, 0, 6) as $measurement): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="measurement-history-item">
                                                    <span><?= htmlspecialchars($measurement['part_name']) ?></span>
                                                    <strong><?= $measurement['value'] ?>"</strong>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($existingMeasurements) > 6): ?>
                                        <small class="text-muted">And <?= count($existingMeasurements) - 6 ?> more measurements...</small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Measurement Form -->
                            <form method="POST" action="" id="measurementForm">
                                <input type="hidden" name="action" value="submit_measurements">
                                
                                <?php if (isset($customer['gender']) && $customer['gender'] === 'Male'): ?>
                                    <!-- Male Measurements -->
                                    <h5 class="mb-3"><i class="fas fa-male"></i> Male Measurements</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="neck">Neck (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="neck" name="neck" step="0.25" min="0" placeholder="e.g., 15.5">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="shoulder">Shoulder (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="shoulder" name="shoulder" step="0.25" min="0" placeholder="e.g., 18.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="chest">Chest (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="chest" name="chest" step="0.25" min="0" placeholder="e.g., 40.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="waist">Waist (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="waist" name="waist" step="0.25" min="0" placeholder="e.g., 34.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="hip">Hip (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="hip" name="hip" step="0.25" min="0" placeholder="e.g., 38.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="sleeve_length">Sleeve Length (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="sleeve_length" name="sleeve_length" step="0.25" min="0" placeholder="e.g., 25.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="shirt_length">Shirt Length (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="shirt_length" name="shirt_length" step="0.25" min="0" placeholder="e.g., 30.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="pant_length">Pant Length (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="pant_length" name="pant_length" step="0.25" min="0" placeholder="e.g., 42.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="inseam">Inseam (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="inseam" name="inseam" step="0.25" min="0" placeholder="e.g., 32.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="thigh">Thigh (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="thigh" name="thigh" step="0.25" min="0" placeholder="e.g., 24.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="calf">Calf (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="calf" name="calf" step="0.25" min="0" placeholder="e.g., 16.0">
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Female Measurements -->
                                    <h5 class="mb-3"><i class="fas fa-female"></i> Female Measurements</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="shoulder">Shoulder (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="shoulder" name="shoulder" step="0.25" min="0" placeholder="e.g., 16.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="bust">Bust (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="bust" name="bust" step="0.25" min="0" placeholder="e.g., 36.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="waist">Waist (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="waist" name="waist" step="0.25" min="0" placeholder="e.g., 28.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="hip">Hip (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="hip" name="hip" step="0.25" min="0" placeholder="e.g., 38.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="sleeve_length">Sleeve Length (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="sleeve_length" name="sleeve_length" step="0.25" min="0" placeholder="e.g., 23.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="armhole">Armhole (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="armhole" name="armhole" step="0.25" min="0" placeholder="e.g., 18.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="blouse_length">Blouse Length (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="blouse_length" name="blouse_length" step="0.25" min="0" placeholder="e.g., 24.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="dress_length">Dress Length (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="dress_length" name="dress_length" step="0.25" min="0" placeholder="e.g., 40.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="skirt_length">Skirt Length (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="skirt_length" name="skirt_length" step="0.25" min="0" placeholder="e.g., 26.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="around_neck">Around Neck (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="around_neck" name="around_neck" step="0.25" min="0" placeholder="e.g., 14.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="measurement-field">
                                                <label for="around_upper_arm">Around Upper Arm (inches)</label>
                                                <input type="number" class="form-control measurement-input" id="around_upper_arm" name="around_upper_arm" step="0.25" min="0" placeholder="e.g., 12.0">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Notes -->
                                <div class="measurement-field">
                                    <label for="notes">Additional Notes (Optional)</label>
                                    <textarea class="form-control measurement-input" id="notes" name="notes" rows="3" placeholder="Any special requirements or notes about your measurements..."></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Submit My Measurements
                                    </button>
                                    <a href="customer-dashboard.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Guide -->
                    <div class="measurement-guide">
                        <h6><i class="fas fa-lightbulb"></i> Quick Tips</h6>
                        <div class="guide-item">
                            <div class="guide-icon">
                                <i class="fas fa-ruler"></i>
                            </div>
                            <div>
                                <strong>Use a measuring tape</strong>
                                <small class="d-block text-muted">Soft measuring tape works best</small>
                            </div>
                        </div>
                        <div class="guide-item">
                            <div class="guide-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div>
                                <strong>Wear fitted clothes</strong>
                                <small class="d-block text-muted">Or measure over undergarments</small>
                            </div>
                        </div>
                        <div class="guide-item">
                            <div class="guide-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <strong>Get help</strong>
                                <small class="d-block text-muted">Ask someone to help you measure</small>
                            </div>
                        </div>
                        <div class="guide-item">
                            <div class="guide-icon">
                                <i class="fas fa-redo"></i>
                            </div>
                            <div>
                                <strong>Measure twice</strong>
                                <small class="d-block text-muted">Double-check for accuracy</small>
                            </div>
                        </div>
                    </div>

                    <!-- Download Guide -->
                    <div class="card">
                        <div class="card-body text-center">
                            <h6><i class="fas fa-download"></i> Download Measurement Guide</h6>
                            <p class="text-muted small">Get our detailed measurement guide with illustrations</p>
                            <a href="assets/guides/measurement-guide.pdf" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> Download PDF Guide
                            </a>
                        </div>
                    </div>

                    <!-- Contact Help -->
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <h6><i class="fas fa-phone"></i> Need Help?</h6>
                            <p class="text-muted small">Our team is here to help you with measurements</p>
                            <a href="tel:+1234567890" class="btn btn-success btn-sm">
                                <i class="fas fa-phone"></i> Call Us
                            </a>
                            <a href="https://wa.me/1234567890" class="btn btn-success btn-sm ms-2" target="_blank">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Measurement Guide Modal -->
    <div class="modal fade" id="measurementGuideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-ruler-combined"></i> How to Take Your Measurements
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="body-diagram">
                                <img src="assets/images/<?= strtolower($customer['gender'] ?? 'male') ?>-body-diagram.png" 
                                     alt="<?= $customer['gender'] ?? 'Male' ?> Body Diagram" 
                                     class="img-fluid"
                                     onerror="this.src='assets/images/body-diagram-placeholder.png'">
                                
                                <!-- Interactive measurement points -->
                                <?php if (isset($customer['gender']) && $customer['gender'] === 'Male'): ?>
                                    <div class="measurement-point" style="top: 15%; left: 50%;" data-measurement="neck" data-tooltip="Neck: Measure around the base of your neck"></div>
                                    <div class="measurement-point" style="top: 25%; left: 20%;" data-measurement="shoulder" data-tooltip="Shoulder: Measure from shoulder point to shoulder point"></div>
                                    <div class="measurement-point" style="top: 35%; left: 50%;" data-measurement="chest" data-tooltip="Chest: Measure around the fullest part of your chest"></div>
                                    <div class="measurement-point" style="top: 50%; left: 50%;" data-measurement="waist" data-tooltip="Waist: Measure around your natural waistline"></div>
                                    <div class="measurement-point" style="top: 65%; left: 50%;" data-measurement="hip" data-tooltip="Hip: Measure around the fullest part of your hips"></div>
                                <?php else: ?>
                                    <div class="measurement-point" style="top: 25%; left: 20%;" data-measurement="shoulder" data-tooltip="Shoulder: Measure from shoulder point to shoulder point"></div>
                                    <div class="measurement-point" style="top: 35%; left: 50%;" data-measurement="bust" data-tooltip="Bust: Measure around the fullest part of your bust"></div>
                                    <div class="measurement-point" style="top: 50%; left: 50%;" data-measurement="waist" data-tooltip="Waist: Measure around your natural waistline"></div>
                                    <div class="measurement-point" style="top: 65%; left: 50%;" data-measurement="hip" data-tooltip="Hip: Measure around the fullest part of your hips"></div>
                                <?php endif; ?>
                                
                                <div class="measurement-tooltip" id="measurementTooltip"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Measurement Instructions:</h6>
                            <?php if (isset($customer['gender']) && $customer['gender'] === 'Male'): ?>
                                <ul class="list-unstyled">
                                    <li><strong>Neck:</strong> Measure around the base of your neck where a shirt collar would sit</li>
                                    <li><strong>Shoulder:</strong> Measure from the edge of one shoulder to the other</li>
                                    <li><strong>Chest:</strong> Measure around the fullest part of your chest, under your arms</li>
                                    <li><strong>Waist:</strong> Measure around your natural waistline</li>
                                    <li><strong>Hip:</strong> Measure around the fullest part of your hips</li>
                                    <li><strong>Sleeve Length:</strong> Measure from shoulder to wrist with arm slightly bent</li>
                                    <li><strong>Inseam:</strong> Measure from crotch to ankle along the inside of your leg</li>
                                </ul>
                            <?php else: ?>
                                <ul class="list-unstyled">
                                    <li><strong>Shoulder:</strong> Measure from the edge of one shoulder to the other</li>
                                    <li><strong>Bust:</strong> Measure around the fullest part of your bust</li>
                                    <li><strong>Waist:</strong> Measure around your natural waistline</li>
                                    <li><strong>Hip:</strong> Measure around the fullest part of your hips</li>
                                    <li><strong>Sleeve Length:</strong> Measure from shoulder to wrist with arm slightly bent</li>
                                    <li><strong>Armhole:</strong> Measure around your armpit area</li>
                                    <li><strong>Dress Length:</strong> Measure from shoulder to desired hem length</li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="assets/guides/measurement-guide.pdf" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download Full Guide
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Interactive measurement points
        document.addEventListener('DOMContentLoaded', function() {
            const measurementPoints = document.querySelectorAll('.measurement-point');
            const tooltip = document.getElementById('measurementTooltip');
            
            measurementPoints.forEach(point => {
                point.addEventListener('mouseenter', function() {
                    const tooltipText = this.getAttribute('data-tooltip');
                    const measurement = this.getAttribute('data-measurement');
                    
                    tooltip.textContent = tooltipText;
                    tooltip.classList.add('show');
                    
                    // Position tooltip
                    const rect = this.getBoundingClientRect();
                    const containerRect = this.parentElement.getBoundingClientRect();
                    
                    tooltip.style.left = (rect.left - containerRect.left + 25) + 'px';
                    tooltip.style.top = (rect.top - containerRect.top - 35) + 'px';
                    
                    // Highlight corresponding input field
                    const inputField = document.getElementById(measurement);
                    if (inputField) {
                        inputField.style.borderColor = '#f39c12';
                        inputField.style.boxShadow = '0 0 0 0.2rem rgba(243, 156, 18, 0.25)';
                    }
                });
                
                point.addEventListener('mouseleave', function() {
                    tooltip.classList.remove('show');
                    
                    // Remove highlight from input field
                    const measurement = this.getAttribute('data-measurement');
                    const inputField = document.getElementById(measurement);
                    if (inputField) {
                        inputField.style.borderColor = '';
                        inputField.style.boxShadow = '';
                    }
                });
                
                point.addEventListener('click', function() {
                    const measurement = this.getAttribute('data-measurement');
                    const inputField = document.getElementById(measurement);
                    if (inputField) {
                        inputField.focus();
                        inputField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            });
            
            // Form validation
            document.getElementById('measurementForm').addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input[type="number"]');
                let hasValue = false;
                
                inputs.forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        hasValue = true;
                    }
                });
                
                if (!hasValue) {
                    e.preventDefault();
                    alert('Please enter at least one measurement value.');
                    return false;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving Measurements...';
                submitBtn.disabled = true;
            });
            
            // Auto-save to localStorage
            const inputs = document.querySelectorAll('.measurement-input');
            inputs.forEach(input => {
                // Load saved value
                const savedValue = localStorage.getItem('measurement_' + input.name);
                if (savedValue) {
                    input.value = savedValue;
                }
                
                // Save on change
                input.addEventListener('change', function() {
                    localStorage.setItem('measurement_' + this.name, this.value);
                });
            });
            
            // Clear localStorage on successful submit
            if (window.location.search.includes('success')) {
                inputs.forEach(input => {
                    localStorage.removeItem('measurement_' + input.name);
                });
            }
        });
    </script>
</body>
</html>