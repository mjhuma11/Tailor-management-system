<?php
/**
 * Upload Measurements Feature
 * The Stitch House - Customer Portal
 */

require_once 'backend/config.php';

// Check if customer is logged in
if (!isCustomerLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirectWithMessage('login.php', 'Please log in to upload measurements.', 'error');
}

$user = getCurrentUser($pdo);
$customer = getCurrentCustomer($pdo);

if (!$user || !$customer) {
    redirectWithMessage('login.php', 'Session expired. Please log in again.', 'error');
}

// Handle file upload
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'upload_measurement') {
    try {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $measurement_type = sanitize($_POST['measurement_type']);
        
        // Handle file upload
        if (isset($_FILES['measurement_file']) && $_FILES['measurement_file']['error'] == 0) {
            $file = $_FILES['measurement_file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            // Validate file
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Please upload images, PDF, or text files only.');
            }
            
            if ($file['size'] > $maxSize) {
                throw new Exception('File too large. Maximum size is 5MB.');
            }
            
            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/measurements/' . $customer['id'] . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'measurement_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save to database
                $stmt = $pdo->prepare("
                    INSERT INTO documents (
                        documentable_id, documentable_type, document_type, title, description,
                        file_name, file_path, file_size, mime_type, uploaded_by, is_public, created_at, updated_at
                    ) VALUES (?, 'customer_measurement', ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
                ");
                
                $document_type = strpos($file['type'], 'image') !== false ? 'image' : 
                               ($file['type'] == 'application/pdf' ? 'pdf' : 'other');
                
                $stmt->execute([
                    $customer['id'],
                    $document_type,
                    $title,
                    $description,
                    $filename,
                    $filepath,
                    $file['size'],
                    $file['type']
                ]);
                
                redirectWithMessage('upload-measurements.php', 'Measurement file uploaded successfully!', 'success');
            } else {
                throw new Exception('Failed to upload file. Please try again.');
            }
        } else {
            throw new Exception('Please select a file to upload.');
        }
        
    } catch (Exception $e) {
        redirectWithMessage('upload-measurements.php', 'Error: ' . $e->getMessage(), 'error');
    }
}

// Handle file deletion
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'delete_file') {
    try {
        $document_id = (int)$_POST['document_id'];
        
        // Get document details
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND documentable_id = ? AND documentable_type = 'customer_measurement'");
        $stmt->execute([$document_id, $customer['id']]);
        $document = $stmt->fetch();
        
        if ($document) {
            // Delete file from filesystem
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
            $stmt->execute([$document_id]);
            
            redirectWithMessage('upload-measurements.php', 'File deleted successfully!', 'success');
        } else {
            throw new Exception('File not found.');
        }
        
    } catch (Exception $e) {
        redirectWithMessage('upload-measurements.php', 'Error deleting file: ' . $e->getMessage(), 'error');
    }
}

// Get uploaded files
try {
    $stmt = $pdo->prepare("
        SELECT * FROM documents 
        WHERE documentable_id = ? AND documentable_type = 'customer_measurement' 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$customer['id']]);
    $uploadedFiles = $stmt->fetchAll();
} catch (Exception $e) {
    $uploadedFiles = [];
}

// Get flash message
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Measurements - The Stitch House</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .upload-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .upload-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .upload-header {
            background: linear-gradient(45deg, #e74c3c, #f39c12);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .file-upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 10px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            border-color: #e74c3c;
            background: #f8f9fa;
        }
        
        .file-upload-area.dragover {
            border-color: #28a745;
            background: #e8f5e8;
        }
        
        .uploaded-file {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .uploaded-file:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .file-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .file-icon.image {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            color: white;
        }
        
        .file-icon.pdf {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .file-icon.other {
            background: linear-gradient(45deg, #6c757d, #adb5bd);
            color: white;
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
                        <li><a class="dropdown-item" href="upload-measurements.php"><i class="fas fa-upload"></i> Upload Measurements</a></li>
                        <li><a class="dropdown-item" href="my-orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Upload Section -->
    <section class="upload-section">
        <div class="container">
            <!-- Flash Message -->
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type'] ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flashMessage['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Upload Form -->
                <div class="col-lg-8">
                    <div class="upload-card">
                        <div class="upload-header">
                            <h2><i class="fas fa-cloud-upload-alt"></i> Upload Your Measurements</h2>
                            <p class="mb-0">Upload photos, notes, or documents with your measurements</p>
                        </div>
                        
                        <div class="p-4">
                            <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                                <input type="hidden" name="action" value="upload_measurement">
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required 
                                               placeholder="e.g., My Measurements - January 2024">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="measurement_type" class="form-label">Type</label>
                                        <select class="form-select" id="measurement_type" name="measurement_type">
                                            <option value="general">General Measurements</option>
                                            <option value="shirt">Shirt Measurements</option>
                                            <option value="pants">Pants Measurements</option>
                                            <option value="dress">Dress Measurements</option>
                                            <option value="suit">Suit Measurements</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                              placeholder="Add any notes about these measurements..."></textarea>
                                </div>

                                <!-- File Upload Area -->
                                <div class="file-upload-area" id="fileUploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Drag & Drop Your File Here</h5>
                                    <p class="text-muted">or click to browse files</p>
                                    <input type="file" class="d-none" id="measurement_file" name="measurement_file" 
                                           accept="image/*,.pdf,.txt" required>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            Supported formats: Images (JPG, PNG, GIF), PDF, Text files<br>
                                            Maximum file size: 5MB
                                        </small>
                                    </div>
                                </div>

                                <!-- Selected File Preview -->
                                <div id="filePreview" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file"></i> 
                                        <span id="fileName"></span>
                                        <button type="button" class="btn-close float-end" id="removeFile"></button>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-upload"></i> Upload Measurement File
                                    </button>
                                    <a href="customer-measurements.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-keyboard"></i> Enter Measurements Manually
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Uploaded Files -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-folder-open"></i> Your Uploaded Files</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($uploadedFiles)): ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No files uploaded yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($uploadedFiles as $file): ?>
                                    <div class="uploaded-file">
                                        <div class="d-flex align-items-center">
                                            <div class="file-icon <?= $file['document_type'] ?>">
                                                <?php if ($file['document_type'] == 'image'): ?>
                                                    <i class="fas fa-image"></i>
                                                <?php elseif ($file['document_type'] == 'pdf'): ?>
                                                    <i class="fas fa-file-pdf"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-file"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($file['title']) ?></h6>
                                                <small class="text-muted">
                                                    <?= formatDate($file['created_at']) ?> • 
                                                    <?= number_format($file['file_size'] / 1024, 1) ?> KB
                                                </small>
                                                <?php if ($file['description']): ?>
                                                    <p class="small text-muted mb-0"><?= htmlspecialchars($file['description']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= htmlspecialchars($file['file_path']) ?>" download>
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" onclick="deleteFile(<?= $file['id'] ?>, '<?= htmlspecialchars($file['title']) ?>')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <h6><i class="fas fa-question-circle"></i> What Can You Upload?</h6>
                            <ul class="list-unstyled text-start small">
                                <li><i class="fas fa-camera text-info"></i> Photos of your measurements</li>
                                <li><i class="fas fa-sticky-note text-warning"></i> Handwritten measurement notes</li>
                                <li><i class="fas fa-file-pdf text-danger"></i> PDF measurement charts</li>
                                <li><i class="fas fa-file-alt text-secondary"></i> Text files with measurements</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Form (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_file">
        <input type="hidden" name="document_id" id="deleteDocumentId">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileUploadArea = document.getElementById('fileUploadArea');
            const fileInput = document.getElementById('measurement_file');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const removeFile = document.getElementById('removeFile');
            
            // Click to upload
            fileUploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            // File selection
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    filePreview.style.display = 'block';
                }
            });
            
            // Remove file
            removeFile.addEventListener('click', function() {
                fileInput.value = '';
                filePreview.style.display = 'none';
            });
            
            // Drag and drop
            fileUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            fileUploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });
            
            fileUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    const file = files[0];
                    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    filePreview.style.display = 'block';
                }
            });
            
            // Form submission
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                submitBtn.disabled = true;
            });
        });
        
        function deleteFile(documentId, title) {
            if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
                document.getElementById('deleteDocumentId').value = documentId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>