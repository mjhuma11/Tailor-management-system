<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();
$pageTitle = 'Customers Management';

// Handle customer actions
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'delete' && isset($_POST['customer_id'])) {
        try {
            $stmt = $mysqli->prepare("UPDATE customer SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param("i", $_POST['customer_id']);
            $stmt->execute();
            $stmt->close();
            showSuccess('Customer deactivated successfully!');
        } catch(Exception $e) {
            showError('Error deactivating customer: ' . $e->getMessage());
        }
        header("Location: customers.php");
        exit();
    }
}

// Pagination and search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query conditions
$whereClause = "WHERE status = 'active'";
$params = [];

if ($search) {
    $whereClause .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam];
}

try {
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM customer " . $whereClause;
    if (!empty($params)) {
        $stmt = $mysqli->prepare($countQuery);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalRecords = $result->fetch_assoc()['total'];
        $stmt->close();
    } else {
        $result = $mysqli->query($countQuery);
        $totalRecords = $result->fetch_assoc()['total'];
    }
    
    $totalPages = ceil($totalRecords / $limit);

    // Get customers
    $query = "SELECT * FROM customer " . $whereClause . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $customers = [];
    
    if (!empty($params)) {
        $stmt = $mysqli->prepare($query);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        $stmt->close();
    } else {
        $result = $mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }

} catch(Exception $e) {
    showError("Error fetching customers: " . $e->getMessage());
    $customers = [];
    $totalPages = 0;
}

include 'includes/header.php';
?>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="customers.php" class="row g-3">
            <div class="col-md-8">
                <label for="search" class="form-label">Search Customers</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Customer name, email, or phone">
            </div>
            
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="customers.php" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-users"></i> Customers List 
                <span class="badge bg-primary ms-2"><?= $totalRecords ?> Total</span>
            </h5>
            <a href="add-customer.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Customer
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($customers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No customers found</h5>
                <p class="text-muted">Start by adding your first customer</p>
                <a href="add-customer.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Customer
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Gender</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($customer['fullname']) ?></strong>
                                        <?php if ($customer['customer_code']): ?>
                                            <br>
                                            <small class="text-muted">ID: <?= htmlspecialchars($customer['customer_code']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-phone text-primary"></i> <?= htmlspecialchars($customer['phone']) ?>
                                        <?php if ($customer['email']): ?>
                                            <br>
                                            <i class="fas fa-envelope text-info"></i> <?= htmlspecialchars($customer['email']) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($customer['address'] ?? 'No address') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $customer['gender'] == 'Male' ? 'primary' : ($customer['gender'] == 'Female' ? 'danger' : 'success') ?>">
                                        <?= htmlspecialchars($customer['gender']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= formatDate($customer['created_at']) ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit-customer.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Customer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="customer-measurements.php?customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-success" title="Take Measurements">
                                            <i class="fas fa-ruler-combined"></i>
                                        </a>
                                        <a href="customer-orders.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-info" title="View Orders">
                                            <i class="fas fa-shopping-bag"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['fullname']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Customers pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
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

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="customer_id" id="deleteCustomerId">
</form>

<?php 
$additionalJS = '
<script>
    function deleteCustomer(id, name) {
        if (confirm(`Are you sure you want to deactivate customer "${name}"? This will hide them from the active list but preserve their data.`)) {
            document.getElementById("deleteCustomerId").value = id;
            document.getElementById("deleteForm").submit();
        }
    }
</script>
';

include 'includes/footer.php'; 
?>