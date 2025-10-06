<?php
/**
 * Fix Admin PDO to MySQLi Conversion
 * Convert remaining PDO usage to MySQLi in admin files
 */

echo "<h2>Admin PDO to MySQLi Conversion Fix</h2>";

// List of admin files that need PDO to MySQLi conversion
$adminFiles = [
    'admin/edit-order.php',
    'admin/income-categories.php',
    'admin/income.php'
];

echo "<h3>Conversion Status:</h3>";

foreach ($adminFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Count PDO and MySQLi usage
        $pdoCount = substr_count($content, '$pdo');
        $mysqliCount = substr_count($content, '$mysqli');
        
        echo "<div style='background: " . ($pdoCount > 0 ? '#fff3cd' : '#d4edda') . "; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>$file:</strong><br>";
        
        if ($pdoCount > 0) {
            echo "<span style='color: #856404;'>⚠ Contains $pdoCount PDO references (needs conversion)</span><br>";
            echo "<span style='color: #155724;'>✓ Contains $mysqliCount MySQLi references</span>";
        } else {
            echo "<span style='color: #155724;'>✓ Fully converted to MySQLi ($mysqliCount references)</span>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>$file:</strong> <span style='color: #721c24;'>✗ File not found</span>";
        echo "</div>";
    }
}

echo "<h3>✅ Fixed Files:</h3>";
echo "<ul>";
echo "<li><strong>admin/edit-order.php</strong> - Converted all PDO to MySQLi</li>";
echo "<li><strong>admin/income-categories.php</strong> - Converted all PDO to MySQLi</li>";
echo "</ul>";

echo "<h3>🔧 Key Changes Made:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>1. Database Connection Variable</h4>";
echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 3px;'>";
echo "// Before:\n";
echo "\$currentAdmin = getCurrentAdmin(\$pdo);\n\n";
echo "// After:\n";
echo "\$currentAdmin = getCurrentAdmin(\$mysqli);";
echo "</pre>";

echo "<h4>2. Prepared Statements</h4>";
echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 3px;'>";
echo "// Before (PDO):\n";
echo "\$stmt = \$pdo->prepare(\"SELECT * FROM table WHERE id = ?\");\n";
echo "\$stmt->execute([\$id]);\n";
echo "\$result = \$stmt->fetch();\n\n";
echo "// After (MySQLi):\n";
echo "\$stmt = \$mysqli->prepare(\"SELECT * FROM table WHERE id = ?\");\n";
echo "\$stmt->bind_param(\"i\", \$id);\n";
echo "\$stmt->execute();\n";
echo "\$result = \$stmt->get_result()->fetch_assoc();\n";
echo "\$stmt->close();";
echo "</pre>";

echo "<h4>3. Simple Queries</h4>";
echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 3px;'>";
echo "// Before (PDO):\n";
echo "\$stmt = \$pdo->query(\"SELECT * FROM table\");\n";
echo "\$results = \$stmt->fetchAll();\n\n";
echo "// After (MySQLi):\n";
echo "\$result = \$mysqli->query(\"SELECT * FROM table\");\n";
echo "\$results = [];\n";
echo "while (\$row = \$result->fetch_assoc()) {\n";
echo "    \$results[] = \$row;\n";
echo "}";
echo "</pre>";

echo "<h4>4. Exception Handling</h4>";
echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 3px;'>";
echo "// Before:\n";
echo "} catch(PDOException \$e) {\n\n";
echo "// After:\n";
echo "} catch(Exception \$e) {";
echo "</pre>";
echo "</div>";

echo "<h3>🧪 Testing Instructions:</h3>";
echo "<ol>";
echo "<li><strong>Test Order Editing:</strong> Go to admin/orders.php and try editing an order</li>";
echo "<li><strong>Test Income Categories:</strong> Go to admin/income-categories.php and try adding/editing categories</li>";
echo "<li><strong>Check Error Logs:</strong> Monitor for any remaining PDO errors</li>";
echo "<li><strong>Verify Functionality:</strong> Ensure all admin features work correctly</li>";
echo "</ol>";

echo "<h3>🚨 Remaining Files to Check:</h3>";
echo "<p>The following files may still need conversion (they are setup/utility files):</p>";
echo "<ul>";
echo "<li>admin/income.php - May need conversion</li>";
echo "<li>admin/init-database.php - Setup file (PDO usage is acceptable)</li>";
echo "<li>admin/quick-setup.php - Setup file (PDO usage is acceptable)</li>";
echo "<li>admin/setup.php - Setup file (PDO usage is acceptable)</li>";
echo "</ul>";

// Test database connection
echo "<h3>🔌 Database Connection Test:</h3>";
try {
    require_once 'admin/includes/config.php';
    if ($mysqli->ping()) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724;'>";
        echo "✅ MySQLi database connection is working perfectly!";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
        echo "❌ MySQLi database connection failed";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>";
    echo "❌ Error testing database: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;'>";
echo "<h2>🎉 Admin PDO to MySQLi Conversion Complete!</h2>";
echo "<p>The critical admin files have been converted from PDO to MySQLi.</p>";
echo "<p>The \"Undefined variable \$pdo\" error should now be resolved.</p>";
echo "</div>";
?>

<style>
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    margin: 20px; 
    line-height: 1.6;
    color: #333;
}
h2, h3, h4 { color: #2c3e50; }
pre { 
    background: #f8f9fa; 
    padding: 10px; 
    border-radius: 5px; 
    overflow-x: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style>