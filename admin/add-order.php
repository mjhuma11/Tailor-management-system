<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();
$pageTitle = 'Add Order';

// Get customers for dropdown
try {
    $result = $mysqli->query("SELECT id, fullname, phone FROM customer WHERE status = 'active' ORDER BY fullname");
    $customers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
} catch(Exception $e) {
    $customers = [];
}

// Handle form submission
if ($_POST) {
    try {
        $customer_id = sanitize($_POST['customer_id']);
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $cloth_type = sanitize($_POST['cloth_type']);
        $fabric_details = sanitize($_POST['fabric_details']);
        $received_date = sanitize($_POST['received_date']);
        $promised_date = sanitize($_POST['promised_date']);
        $amount_charged = sanitize($_POST['amount_charged']);
        $priority = sanitize($_POST['priority']);
        $special_instructions = sanitize($_POST['special_instructions']);
        
        // Generate order number
        $order_number = 'ORD' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt = $mysqli->prepare("
            INSERT INTO `order` (
                customer_id, order_number, title, description, cloth_type, 
                fabric_details, received_date, promised_date, received_by, 
                amount_charged, priority, special_instructions, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param("isssssssidss", $customer_id, $order_number, $title, $description, $cloth_type,
            $fabric_details, $received_date, $promised_date, $_SESSION['user_id'],
            $amount_charged, $priority, $special_instructions);
        $stmt->execute();
        $stmt->close();
        
        showSuccess("Order #$order_number created successfully!");
        header("Location: orders.php");
        exit();
        
    } catch(Exception $e) {
        showError("Error creating order: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<!-- Add Order Form -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus-circle"></i> Order Information
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_id" class="form-label">Customer *</label>
                    <select class="form-select" id="customer_id" name="customer_id" required>
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>">
                                <?= htmlspecialchars($customer['fullname']) ?> - <?= htmlspecialchars($customer['phone']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        <a href="add-customer.php" class="text-decoration-none">
                            <i class="fas fa-plus"></i> Add New Customer
                        </a>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Order Title *</label>
                    <input type="text" class="form-control" id="title" name="title" required 
                           placeholder="e.g., Wedding Dress, Business Suit">
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"
                          placeholder="Detailed description of the order..."></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="cloth_type" class="form-label">Cloth Type *</label>
                    <input type="text" class="form-control" id="cloth_type" name="cloth_type" required
                           placeholder="e.g., Silk, Cotton, Wool, Satin">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="fabric_details" class="form-label">Fabric Details</label>
                    <input type="text" class="form-control" id="fabric_details" name="fabric_details"
                           placeholder="Color, pattern, quality details...">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="received_date" class="form-label">Received Date *</label>
                    <input type="date" class="form-control" id="received_date" name="received_date" 
                           value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="promised_date" class="form-label">Promised Date *</label>
                    <input type="date" class="form-control" id="promised_date" name="promised_date" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="amount_charged" class="form-label">Amount Charged *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="amount_charged" name="amount_charged" 
                               step="0.01" min="0" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">Priority</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="special_instructions" class="form-label">Special Instructions</label>
                <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3"
                          placeholder="Any special requirements or instructions..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Order
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
$additionalJS = '
<script>
    // Set minimum promised date to tomorrow
    document.addEventListener("DOMContentLoaded", function() {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById("promised_date").min = tomorrow.toISOString().split("T")[0];
    });
</script>
';

include 'includes/footer.php'; 
?>