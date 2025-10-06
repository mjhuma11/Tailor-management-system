<?php
/**
 * Setup Script for The Stitch House Admin Panel
 * 
 * This script creates demo admin user and sample data
 * Run this once to set up the admin panel
 */

require_once 'includes/config.php';

try {
    echo "<h2>Setting up The Stitch House Admin Panel...</h2>";
    
    // Check if admin user already exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@stitch.com'");
    $adminExists = $stmt->fetch()['count'] > 0;
    
    if (!$adminExists) {
        // Create default admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, username, password, role, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            'Administrator',
            'admin@stitch.com',
            'admin',
            $adminPassword,
            'admin',
            'active'
        ]);
        
        echo "<p>✅ Admin user created successfully!</p>";
        echo "<p><strong>Email:</strong> admin@stitch.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
    } else {
        echo "<p>ℹ️ Admin user already exists.</p>";
    }
    
    // Add sample customers if none exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer");
    $customerCount = $stmt->fetch()['count'];
    
    if ($customerCount == 0) {
        $sampleCustomers = [
            [
                'John Doe',
                '123 Main Street, Downtown',
                '+1-555-0123',
                'New York',
                'john.doe@email.com',
                'Regular customer, prefers formal wear',
                'Male',
                '1985-03-15'
            ],
            [
                'Sarah Johnson',
                '456 Oak Avenue, Midtown',
                '+1-555-0456',
                'New York',
                'sarah.johnson@email.com',
                'Bridal customer, getting married in December',
                'Female',
                '1990-07-22'
            ],
            [
                'Michael Brown',
                '789 Pine Road, Uptown',
                '+1-555-0789',
                'New York',
                'michael.brown@email.com',
                'Business executive, needs quick turnaround',
                'Male',
                '1978-11-08'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO customer (fullname, address, phone, city, email, comment, gender, date_of_birth, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        
        foreach ($sampleCustomers as $customer) {
            $stmt->execute($customer);
        }
        
        echo "<p>✅ Sample customers added successfully!</p>";
    }
    
    // Add sample orders if none exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `order`");
    $orderCount = $stmt->fetch()['count'];
    
    if ($orderCount == 0) {
        // Get customer IDs
        $stmt = $pdo->query("SELECT id FROM customer LIMIT 3");
        $customers = $stmt->fetchAll();
        
        if (!empty($customers)) {
            $sampleOrders = [
                [
                    $customers[0]['id'],
                    'ORD-001',
                    'Custom Business Suit',
                    'Navy blue business suit with personalized fitting',
                    'Wool Blend',
                    'Premium wool blend fabric, navy blue color',
                    date('Y-m-d'),
                    date('Y-m-d', strtotime('+7 days')),
                    450.00,
                    150.00,
                    'received',
                    'normal'
                ],
                [
                    $customers[1]['id'],
                    'ORD-002',
                    'Wedding Dress',
                    'Custom wedding dress with intricate beading',
                    'Silk',
                    'Pure silk fabric with hand-sewn beading details',
                    date('Y-m-d'),
                    date('Y-m-d', strtotime('+14 days')),
                    850.00,
                    300.00,
                    'in_progress',
                    'high'
                ],
                [
                    $customers[2]['id'],
                    'ORD-003',
                    'Casual Shirt Alteration',
                    'Hemming and sleeve adjustment for casual shirt',
                    'Cotton',
                    'Blue cotton casual shirt, sleeve shortening needed',
                    date('Y-m-d'),
                    date('Y-m-d', strtotime('+3 days')),
                    35.00,
                    35.00,
                    'ready',
                    'low'
                ]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO `order` (customer_id, order_number, title, description, cloth_type, fabric_details, 
                                   received_date, promised_date, amount_charged, amount_paid, order_status, priority, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            foreach ($sampleOrders as $order) {
                $stmt->execute($order);
            }
            
            echo "<p>✅ Sample orders added successfully!</p>";
        }
    }
    
    // Add sample income records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM income");
    $incomeCount = $stmt->fetch()['count'];
    
    if ($incomeCount == 0) {
        // Get income category ID
        $stmt = $pdo->query("SELECT id FROM inc_cat WHERE name = 'Tailoring Services' LIMIT 1");
        $incomeCategory = $stmt->fetch();
        
        if ($incomeCategory) {
            $sampleIncome = [
                [
                    $incomeCategory['id'],
                    'Payment for Business Suit',
                    'Full payment received for custom business suit',
                    date('Y-m-d'),
                    450.00,
                    'INV-001',
                    'John Doe',
                    'cash'
                ],
                [
                    $incomeCategory['id'],
                    'Wedding Dress Deposit',
                    'Advance payment for wedding dress',
                    date('Y-m-d'),
                    300.00,
                    'INV-002',
                    'Sarah Johnson',
                    'card'
                ],
                [
                    $incomeCategory['id'],
                    'Alteration Service',
                    'Payment for shirt alteration',
                    date('Y-m-d'),
                    35.00,
                    'INV-003',
                    'Michael Brown',
                    'cash'
                ]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO income (inc_cat_id, title, description, income_date, amount, receipt_number, client_name, payment_method, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            foreach ($sampleIncome as $income) {
                $stmt->execute($income);
            }
            
            echo "<p>✅ Sample income records added successfully!</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<p>You can now access the admin panel:</p>";
    echo "<p><a href='../login.html' class='btn btn-primary'>Go to Login</a></p>";
    echo "<p><a href='../index.php' class='btn btn-secondary'>View Website</a></p>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - The Stitch House Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Content will be inserted above -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>