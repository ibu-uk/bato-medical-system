<?php
// Start session
session_start();

// Include database configuration
require_once 'config/database.php';

// Check if report ID is provided
if (!isset($_GET['id'])) {
    header("Location: reports.php");
    exit;
}

$reportId = sanitize($_GET['id']);

// Delete report tests first (foreign key constraint)
$deleteTestsQuery = "DELETE FROM report_tests WHERE report_id = '$reportId'";
executeQuery($deleteTestsQuery);

// Delete the report
$deleteReportQuery = "DELETE FROM reports WHERE id = '$reportId'";
executeQuery($deleteReportQuery);

// Redirect back to reports page
header("Location: reports.php?deleted=1");
exit;
?>
