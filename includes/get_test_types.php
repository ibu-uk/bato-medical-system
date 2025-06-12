<?php
// Include database configuration
require_once '../config/database.php';

// Get all test types
$query = "SELECT id, name, unit, normal_range FROM test_types ORDER BY name";
$result = executeQuery($query);

$tests = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tests[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'unit' => $row['unit'],
            'normal_range' => $row['normal_range']
        );
    }
    
    echo json_encode(array(
        'success' => true,
        'tests' => $tests
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'No test types found'
    ));
}
?>
