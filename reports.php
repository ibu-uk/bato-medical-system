<?php
// Start session
session_start();

// Include database configuration
require_once 'config/database.php';

// Get all reports
$query = "SELECT r.id, r.report_date, r.created_at, p.name as patient_name, p.civil_id, d.name as doctor_name
          FROM reports r
          JOIN patients p ON r.patient_id = p.id
          JOIN doctors d ON r.doctor_id = d.id
          ORDER BY r.created_at DESC";
$reports = executeQuery($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Bato Medical Report System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bato Medical Report System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_doctors.php">Doctors</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Medical Reports</h4>
                        <a href="index.php" class="btn btn-light">
                            <i class="fas fa-plus"></i> New Report
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reportsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient Name</th>
                                        <th>Civil ID</th>
                                        <th>Report Date</th>
                                        <th>Doctor</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($reports && $reports->num_rows > 0) {
                                        while ($row = $reports->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>{$row['id']}</td>";
                                            echo "<td>{$row['patient_name']}</td>";
                                            echo "<td>{$row['civil_id']}</td>";
                                            echo "<td>" . date('Y-m-d', strtotime($row['report_date'])) . "</td>";
                                            echo "<td>{$row['doctor_name']}</td>";
                                            echo "<td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>";
                                            echo "<td>
                                                <a href='view_report.php?id={$row['id']}' class='btn btn-sm btn-info me-1' title='View'>
                                                    <i class='fas fa-eye'></i>
                                                </a>
                                                <button class='btn btn-sm btn-danger' onclick='deleteReport({$row['id']})' title='Delete'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-3 mt-4">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> Bato Medical Report System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this report? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#reportsTable').DataTable({
                order: [[5, 'desc']]
            });
        });
        
        function deleteReport(id) {
            $('#confirmDelete').attr('href', 'delete_report.php?id=' + id);
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
