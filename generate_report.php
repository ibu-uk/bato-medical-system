<?php
// Start session
session_start();

// Include database configuration
require_once 'config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $patientId = sanitize($_POST['patient_id']);
    $doctorId = sanitize($_POST['doctor_id']);
    $reportDate = sanitize($_POST['report_date']);
    $generatedBy = sanitize($_POST['generated_by']);
    
    // Get test data
    $testTypeIds = isset($_POST['test_type_id']) ? $_POST['test_type_id'] : [];
    $testValues = isset($_POST['test_value']) ? $_POST['test_value'] : [];
    $testFlags = isset($_POST['test_flag']) ? $_POST['test_flag'] : [];
    $testRemarks = isset($_POST['test_remarks']) ? $_POST['test_remarks'] : [];
    
    // Insert report into database
    $reportQuery = "INSERT INTO reports (patient_id, doctor_id, report_date, generated_by, created_at) 
                   VALUES ('$patientId', '$doctorId', '$reportDate', '$generatedBy', NOW())";
    $reportId = executeInsert($reportQuery);
    
    // Insert test results
    if ($reportId && count($testTypeIds) > 0) {
        for ($i = 0; $i < count($testTypeIds); $i++) {
            if (!empty($testTypeIds[$i]) && isset($testValues[$i])) {
                $testTypeId = sanitize($testTypeIds[$i]);
                $testValue = sanitize($testValues[$i]);
                $testFlag = isset($testFlags[$i]) ? sanitize($testFlags[$i]) : null;
                $testRemark = isset($testRemarks[$i]) ? sanitize($testRemarks[$i]) : null;
                
                $testQuery = "INSERT INTO report_tests (report_id, test_type_id, test_value, flag, remarks) 
                             VALUES ('$reportId', '$testTypeId', '$testValue', '$testFlag', '$testRemark')";
                executeQuery($testQuery);
            }
        }
    }
    
    // Redirect to view the report
    header("Location: view_report.php?id=$reportId");
    exit;
}
?>
