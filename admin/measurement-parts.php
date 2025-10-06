<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();
$pageTitle = 'Measurement Parts';

// Handle form submission for adding/editing measurement parts
if ($_POST) {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $name = sanitize($_POST['name']);
                    $description = sanitize($_POST['description']);
                    $gender = sanitize($_POST['gender']);
                    $unit = sanitize($_POST['unit']);
                    $sort_order = (int)$_POST['sort_order'];
                    $status = sanitize($_POST['status']);
                    
                    $stmt = $mysqli->prepare("INSERT INTO measurement_part (name, description, gender, unit, sort_order, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("ssssiss", $name, $description, $gender, $unit, $sort_order, $status);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Measurement part created successfully!");
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $name = sanitize($_POST['name']);
                    $description = sanitize($_POST['description']);
                    $gender = sanitize($_POST['gender']);
                    $unit = sanitize($_POST['unit']);
                    $sort_order = (int)$_POST['sort_order'];
                    $status = sanitize($_POST['status']);
                    
                    $stmt = $mysqli->prepare("UPDATE measurement_part SET name = ?, description = ?, gender = ?, unit = ?, sort_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("ssssissi", $name, $description, $gender, $unit, $sort_order, $status, $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Measurement part updated successfully!");
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    $stmt = $mysqli->prepare("UPDATE measurement_part SET deleted_at = NOW() WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    showSuccess("Measurement part deleted successfully!");
                    break;
            }
        }
        header("Location: measurement-parts.php");
        exit();
    } catch(Exception $e) {
        showError("Error: " . $e->getMessage());
    }
}

// Get all measurement parts
try {
    $result = $mysqli->query("SELECT * FROM measurement_part WHERE deleted_at IS NULL ORDER BY sort_order, name");
    $parts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $parts[] = $row;
        }
    }
} catch(Exception $e) {
    $parts = [];
    showError("Error fetching measurement parts: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- Parts Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-ruler-combined"></i> Measurement Parts 
                <span class="badge bg-primary ms-2"><?= count($parts) ?> Parts</span>
            </h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPartModal">
                <i class="fas fa-plus"></i> Add Part
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($parts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-ruler-combined fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No measurement parts found</h5>
                <p class="text-muted">Create measurement parts to define what measurements to take</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPartModal">
                    <i class="fas fa-plus"></i> Add First Measurement Part
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Gender</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parts as $part): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?= $part['sort_order'] ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($part['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($part['description']) ?></td>
                                <td>
                                    <?php
                                    $gender_colors = [
                                        'Male' => 'primary',
                                        'Female' => 'danger',
                                        'Both' => 'success'
                                    ];
                                    $color = $gender_colors[$part['gender']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= $part['gender'] ?>
                                    </span>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($part['unit']) ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $part['status'] == 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($part['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editPart(<?= $part['id'] ?>, '<?= htmlspecialchars($part['name']) ?>', '<?= htmlspecialchars($part['description']) ?>', '<?= $part['gender'] ?>', '<?= $part['unit'] ?>', <?= $part['sort_order'] ?>, '<?= $part['status'] ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deletePart(<?= $part['id'] ?>, '<?= htmlspecialchars($part['name']) ?>')">
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

<!-- Add Part Modal -->
<div class="modal fade" id="addPartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Add Measurement Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Part Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="Both">Both</option>
                                <option value="Male">Male Only</option>
                                <option value="Female">Female Only</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <select class="form-select" id="unit" name="unit">
                                <option value="inches">Inches</option>
                                <option value="cm">Centimeters</option>
                                <option value="feet">Feet</option>
                            </select>
                        </div>
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
                    <button type="submit" class="btn btn-primary">Create Part</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Part Modal -->
<div class="modal fade" id="editPartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Measurement Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="editName" class="form-label">Part Name *</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editSortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="editSortOrder" name="sort_order" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editGender" class="form-label">Gender</label>
                            <select class="form-select" id="editGender" name="gender">
                                <option value="Both">Both</option>
                                <option value="Male">Male Only</option>
                                <option value="Female">Female Only</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editUnit" class="form-label">Unit</label>
                            <select class="form-select" id="editUnit" name="unit">
                                <option value="inches">Inches</option>
                                <option value="cm">Centimeters</option>
                                <option value="feet">Feet</option>
                            </select>
                        </div>
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
                    <button type="submit" class="btn btn-primary">Update Part</button>
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

<?php 
$additionalJS = '
<script>
    function editPart(id, name, description, gender, unit, sortOrder, status) {
        document.getElementById("editId").value = id;
        document.getElementById("editName").value = name;
        document.getElementById("editDescription").value = description;
        document.getElementById("editGender").value = gender;
        document.getElementById("editUnit").value = unit;
        document.getElementById("editSortOrder").value = sortOrder;
        document.getElementById("editStatus").value = status;
        
        new bootstrap.Modal(document.getElementById("editPartModal")).show();
    }

    function deletePart(id, name) {
        if (confirm(`Are you sure you want to delete the measurement part "${name}"? This action cannot be undone.`)) {
            document.getElementById("deleteId").value = id;
            document.getElementById("deleteForm").submit();
        }
    }
</script>
';

include 'includes/footer.php'; 
?>