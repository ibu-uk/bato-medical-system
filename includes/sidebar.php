<!-- Sidebar -->
<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-file-medical"></i> Medical Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'prescriptions.php' || basename($_SERVER['PHP_SELF']) == 'add_prescription.php' ? 'active' : ''; ?>" href="prescriptions.php">
                    <i class="fas fa-prescription"></i> Prescriptions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'nurse_treatments.php' || basename($_SERVER['PHP_SELF']) == 'add_nurse_treatment.php' ? 'active' : ''; ?>" href="nurse_treatments.php">
                    <i class="fas fa-user-nurse"></i> Nurse Treatments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_doctors.php' ? 'active' : ''; ?>" href="manage_doctors.php">
                    <i class="fas fa-user-md"></i> Doctors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_test_types.php' ? 'active' : ''; ?>" href="manage_test_types.php">
                    <i class="fas fa-vial"></i> Test Types
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_patient.php' ? 'active' : ''; ?>" href="add_patient.php">
                    <i class="fas fa-user-plus"></i> Add Patient
                </a>
            </li>
        </ul>
    </div>
</div>
