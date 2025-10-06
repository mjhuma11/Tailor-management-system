<?php
/**
 * Measurement Parts API
 * Returns measurement parts based on gender filter
 */

require_once '../includes/config.php';
requireAuth();

header('Content-Type: application/json');

try {
    $gender = isset($_GET['gender']) ? sanitize($_GET['gender']) : 'Both';
    
    // Get measurement parts
    $stmt = $mysqli->prepare("
        SELECT id, name, description, gender, unit, sort_order 
        FROM measurement_part 
        WHERE (gender = ? OR gender = 'Both') 
        AND status = 'active' 
        AND deleted_at IS NULL 
        ORDER BY sort_order, name
    ");
    $stmt->bind_param("s", $gender);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $parts = [];
    while ($row = $result->fetch_assoc()) {
        $parts[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $parts,
        'count' => count($parts)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>