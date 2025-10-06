<?php
/**
 * Admin Sidebar Template
 * The Stitch House - Common Sidebar for All Admin Pages
 */
?>
<nav class="admin-sidebar">
    <div class="sidebar-header">
        <h4 class="sidebar-title">The Stitch House Admin</h4>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'add-order.php' ? 'active' : '' ?>" href="add-order.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Add Order</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                    <i class="fas fa-list-alt"></i>
                    <span>View/Edit Orders</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>" href="customers.php">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#staffMenu">
                    <i class="fas fa-user-tie"></i>
                    <span>Staff Management</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="staffMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : '' ?>" href="staff.php">View Staff</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'add-staff.php' ? 'active' : '' ?>" href="add-staff.php">Add Staff</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'staff-types.php' ? 'active' : '' ?>" href="staff-types.php">Staff Types</a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#incomeMenu">
                    <i class="fas fa-coins"></i>
                    <span>Income Management</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="incomeMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'income.php' ? 'active' : '' ?>" href="income.php">View Income</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'add-income.php' ? 'active' : '' ?>" href="add-income.php">Add Income</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'income-categories.php' ? 'active' : '' ?>" href="income-categories.php">Categories</a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#measurementMenu">
                    <i class="fas fa-ruler"></i>
                    <span>Measurements</span>
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="measurementMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'measurements.php' ? 'active' : '' ?>" href="measurements.php">View Measurements</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'measurement-parts.php' ? 'active' : '' ?>" href="measurement-parts.php">Measurement Parts</a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="admin-details">
                <strong><?php echo htmlspecialchars($currentAdmin['name'] ?? 'Admin'); ?></strong>
                <small class="text-muted d-block"><?php echo htmlspecialchars($currentAdmin['role'] ?? 'Administrator'); ?></small>
            </div>
        </div>
        <a href="logout.php" class="btn btn-sm btn-outline-light mt-2">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>