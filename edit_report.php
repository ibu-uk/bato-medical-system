<?php
// Start session
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

// Only admin can access
if (!hasRole(['admin'])) {
    header('Location: reports.php');
    exit;
}

// Get report ID
if (!isset($_GET['id'])) {
    header('Location: reports.php');
    exit;
}
$report_id = intval($_GET['id']);

// Fetch report
$query = "SELECT * FROM reports WHERE id = $report_id";
$result = executeQuery($query);
if (!$result || $result->num_rows == 0) {
    $_SESSION['error'] = "Report not found.";
    header('Location: reports.php');
    exit;
}
$report = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_date = sanitize($_POST['report_date']);
    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $update_query = "UPDATE reports SET report_date='$report_date', patient_id=$patient_id, doctor_id=$doctor_id WHERE id=$report_id";
    if (executeQuery($update_query)) {
        // Handle test results
        $test_rows = isset($_POST['test_row']) ? $_POST['test_row'] : [];
        $existing_ids = [];
        foreach ($test_rows as $row) {
            $test_type_id = intval($row['test_type_id']);
            $test_value = sanitize($row['test_value']);
            $flag = isset($row['flag']) ? sanitize($row['flag']) : '';
            if (!empty($row['report_test_id'])) {
                // Update existing
                $report_test_id = intval($row['report_test_id']);
                $existing_ids[] = $report_test_id;
                executeQuery("UPDATE report_tests SET test_type_id=$test_type_id, test_value='$test_value', flag='$flag' WHERE id=$report_test_id");
            } else {
                // Insert new
                executeQuery("INSERT INTO report_tests (report_id, test_type_id, test_value, flag) VALUES ($report_id, $test_type_id, '$test_value', '$flag')");
            }
        }
        // Delete removed test results
        if (count($existing_ids) > 0) {
            $ids_str = implode(',', array_map('intval', $existing_ids));
            executeQuery("DELETE FROM report_tests WHERE report_id=$report_id AND id NOT IN ($ids_str)");
        } else {
            executeQuery("DELETE FROM report_tests WHERE report_id=$report_id");
        }
        $_SESSION['success'] = "Report updated successfully.";
        header('Location: reports.php');
        exit;
    } else {
        $error = "Failed to update report. ";
        $error .= "<br>Submitted date: " . htmlspecialchars($report_date);
        $error .= "<br>SQL: " . htmlspecialchars($update_query);
    }
}

// Fetch patients and doctors for dropdowns
$patients = executeQuery("SELECT id, name FROM patients ORDER BY name");
$doctors = executeQuery("SELECT id, name FROM doctors ORDER BY name");

// Fetch existing test results for this report
$test_results = [];
$tests_query = "SELECT rt.id as report_test_id, rt.test_type_id, rt.test_value, tt.name as test_name, tt.unit, tt.normal_range FROM report_tests rt JOIN test_types tt ON rt.test_type_id = tt.id WHERE rt.report_id = $report_id";
$tests_result = executeQuery($tests_query);
if ($tests_result && $tests_result->num_rows > 0) {
    while ($row = $tests_result->fetch_assoc()) {
        $test_results[] = $row;
    }
} elseif ($tests_result === false) {
    $error = 'Failed to fetch test results.';
}

// Fetch all test types for dropdowns
$all_test_types = [];
$test_types_query = "SELECT id, name, unit, normal_range FROM test_types ORDER BY name";
$test_types_result = executeQuery($test_types_query);
if ($test_types_result && $test_types_result->num_rows > 0) {
    while ($row = $test_types_result->fetch_assoc()) {
        $all_test_types[] = $row;
    }
} elseif ($test_types_result === false) {
    $error = 'Failed to fetch test types.';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Medical Report</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="report_date" class="form-label">Report Date</label>
            <input type="date" class="form-control" id="report_date" name="report_date" value="<?php echo htmlspecialchars($report['report_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="patient_id" class="form-label">Patient</label>
            <select class="form-control" id="patient_id" name="patient_id" required>
                <?php while ($p = $patients->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php if ($p['id'] == $report['patient_id']) echo 'selected'; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="doctor_id" class="form-label">Doctor</label>
            <select class="form-control" id="doctor_id" name="doctor_id" required>
                <?php while ($d = $doctors->fetch_assoc()): ?>
                    <option value="<?php echo $d['id']; ?>" <?php if ($d['id'] == $report['doctor_id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Test Results Section -->
        <h5 class="mt-4">Test Results</h5>
        <table class="table table-bordered" id="test-results-table">
            <thead>
                <tr>
                    <th style="width: 30%">Test Name</th>
                    <th style="width: 20%">Result</th>
                    <th style="width: 10%">Flag</th>
                    <th style="width: 15%">Unit</th>
                    <th style="width: 25%">Reference Range</th>
                    <th style="width: 10%"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($test_results as $idx => $tr): ?>
                <tr>
                    <td>
                        <input type="hidden" name="test_row[<?php echo $idx; ?>][report_test_id]" value="<?php echo $tr['report_test_id']; ?>">
                        <select class="form-select test-type-select" name="test_row[<?php echo $idx; ?>][test_type_id]" required>
                            <option value="">Select Test</option>
                            <?php foreach ($all_test_types as $tt): ?>
                                <option value="<?php echo $tt['id']; ?>" data-unit="<?php echo htmlspecialchars($tt['unit']); ?>" data-range="<?php echo htmlspecialchars($tt['normal_range']); ?>" <?php if ((string)$tt['id'] === (string)$tr['test_type_id']) echo 'selected'; ?>><?php echo htmlspecialchars($tt['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" class="form-control" name="test_row[<?php echo $idx; ?>][test_value]" value="<?php echo htmlspecialchars($tr['test_value']); ?>" required></td>
                    <td>
                        <select class="form-select" name="test_row[<?php echo $idx; ?>][flag]">
                            <option value="" <?php if (empty($tr['flag'])) echo 'selected'; ?>>Normal</option>
                            <option value="High" <?php if ($tr['flag'] === 'High') echo 'selected'; ?>>High</option>
                            <option value="Low" <?php if ($tr['flag'] === 'Low') echo 'selected'; ?>>Low</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control unit-field" value="<?php echo htmlspecialchars($tr['unit']); ?>" readonly></td>
                    <td><input type="text" class="form-control range-field" value="<?php echo htmlspecialchars($tr['normal_range']); ?>" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-test-row"><i class="fa fa-trash"></i></button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="reports.php" class="btn btn-secondary">Cancel</a>
    </form>

    <!-- Template row for JS cloning (hidden) -->
    <table style="display:none"><tbody>
        <tr id="test-row-template">
            <td>
                <input type="hidden" name="TEMPLATE[report_test_id]" value="">
                <select class="form-select test-type-select" name="TEMPLATE[test_type_id]" required>
                    <option value="">Select Test</option>
                    <?php foreach ($all_test_types as $tt): ?>
                        <option value="<?php echo $tt['id']; ?>" data-unit="<?php echo htmlspecialchars($tt['unit']); ?>" data-range="<?php echo htmlspecialchars($tt['normal_range']); ?>"><?php echo htmlspecialchars($tt['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="text" class="form-control" name="TEMPLATE[test_value]" value="" required></td>
            <td>
                <select class="form-select" name="TEMPLATE[flag]">
                    <option value="">Normal</option>
                    <option value="High">High</option>
                    <option value="Low">Low</option>
                </select>
            </td>
            <td><input type="text" class="form-control unit-field" value="" readonly></td>
            <td><input type="text" class="form-control range-field" value="" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-test-row"><i class="fa fa-trash"></i></button></td>
        </tr>
    </tbody></table>

</div>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Add new test row
        let rowIdx = <?php echo count($test_results); ?>;
        $('#add-test-row').click(function() {
            let $tpl = $('#test-row-template').clone().removeAttr('id');
            let html = $tpl.html().replace(/TEMPLATE/g, 'test_row['+rowIdx+']');
            // Wrap in <tr> so jQuery parses it as a row
            let $row = $('<tr>' + html + '</tr>').children();
            $('#test-results-table tbody').append($row);
            // When user selects a test, autofill unit/range
            $row.find('.test-type-select').on('change', function() {
                var $select = $(this);
                var unit = $select.find('option:selected').data('unit') || '';
                var range = $select.find('option:selected').data('range') || '';
                $select.closest('tr').find('.unit-field').val(unit);
                $select.closest('tr').find('.range-field').val(range);
            });
            // Trigger change to autofill if a value is already selected
            $row.find('.test-type-select').trigger('change');
            rowIdx++;
        });
        // Remove test row
        $(document).on('click', '.remove-test-row', function() {
            $(this).closest('tr').remove();
        });
        // Auto-fill unit and range on test type change (event delegation for all current and future rows)
        $(document).on('change', '.test-type-select', function() {
            var $select = $(this);
            var unit = $select.find('option:selected').data('unit') || '';
            var range = $select.find('option:selected').data('range') || '';
            $select.closest('tr').find('.unit-field').val(unit);
            $select.closest('tr').find('.range-field').val(range);
        });
        // Trigger autofill for existing rows on load
        $('.test-type-select').each(function() {
            $(this).trigger('change');
        });
    });
    </script>
</body>
</html>
