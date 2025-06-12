// Main JavaScript for Bato Medical Report System

$(document).ready(function() {
    // Patient search functionality
    $('#patient_search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        let matchFound = false;
        
        // Reset all options first
        $('#patient option').each(function() {
            $(this).removeClass('highlighted-option');
            $(this).show();
        });
        
        if (searchTerm.length > 0) {
            // Filter and highlight matching options
            $('#patient option:not(:first)').each(function() {
                const searchText = $(this).data('search-text').toLowerCase();
                
                if (searchText.includes(searchTerm)) {
                    $(this).addClass('highlighted-option');
                    matchFound = true;
                } else {
                    $(this).hide();
                }
            });
            
            // If there's only one match, select it
            const visibleOptions = $('#patient option:visible:not(:first)');
            if (visibleOptions.length === 1) {
                visibleOptions.prop('selected', true);
                $('#patient').trigger('change');
            }
        }
    });
    
    // Clear search button
    $('#clear_search').click(function() {
        $('#patient_search').val('');
        $('#patient option').each(function() {
            $(this).removeClass('highlighted-option');
            $(this).show();
        });
    });
    
    // Patient selection - auto-fill patient details
    $('#patient').change(function() {
        const patientId = $(this).val();
        if (patientId) {
            // Get Civil ID from data attribute
            const civilId = $('option:selected', this).data('civil-id');
            $('#civil_id').val(civilId);
            
            // Fetch additional patient details via AJAX
            $.ajax({
                url: 'includes/get_patient_details.php',
                type: 'POST',
                data: { patient_id: patientId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#mobile').val(response.mobile);
                    }
                }
            });
        } else {
            // Clear fields if no patient selected
            $('#civil_id').val('');
            $('#mobile').val('');
        }
    });

    // Doctor selection - auto-fill doctor position
    $('#doctor').change(function() {
        const position = $('option:selected', this).data('position');
        $('#doctor_position').val(position || '');
    });

    // Add Test button functionality
    $('#addTestBtn').click(function() {
        addTestRow();
    });

    // Initial test row
    addTestRow();

    // Form validation before submission
    $('#reportForm').submit(function(e) {
        // Check if at least one test is added
        if ($('.test-row').length === 0) {
            alert('Please add at least one test result.');
            e.preventDefault();
            return false;
        }
        
        // Check if all test values are filled
        let isValid = true;
        $('.test-value').each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            alert('Please fill in all test values.');
            e.preventDefault();
            return false;
        }
    });
});

// Function to add a new test row
function addTestRow() {
    // Create a unique ID for this test row
    const rowId = 'test-' + Date.now();
    
    // Create the HTML for the test row
    const html = `
        <div class="test-row" id="${rowId}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Test Type</label>
                    <select class="form-select test-type" name="test_type_id[]" required>
                        <option value="">-- Select Test --</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Value</label>
                    <div class="input-group">
                        <input type="text" class="form-control test-value" name="test_value[]" required>
                        <select class="form-select flag-select" name="test_flag[]" style="max-width: 100px;">
                            <option value="">--</option>
                            <option value="HIGH" style="color: red;">HIGH</option>
                            <option value="LOW" style="color: red;">LOW</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Unit</label>
                    <input type="text" class="form-control test-unit" name="test_unit[]" readonly>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Normal Range</label>
                    <input type="text" class="form-control test-range" name="test_range[]" readonly>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-test" onclick="removeTestRow('${rowId}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control test-remarks" name="test_remarks[]" rows="2" placeholder="Enter any remarks or notes here"></textarea>
                </div>
            </div>
        </div>
    `;
    
    // Append the row to the container
    $('#testsContainer').append(html);
    
    // Load test types for the new row
    loadTestTypes($(`#${rowId} .test-type`));
    
    // Set up change event for the test type dropdown
    $(`#${rowId} .test-type`).change(function() {
        const testId = $(this).val();
        const row = $(this).closest('.test-row');
        
        if (testId) {
            // Fetch test details via AJAX
            $.ajax({
                url: 'includes/get_test_details.php',
                type: 'POST',
                data: { test_id: testId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        row.find('.test-unit').val(response.unit);
                        row.find('.test-range').val(response.normal_range);
                    }
                }
            });
        } else {
            // Clear fields if no test selected
            row.find('.test-unit').val('');
            row.find('.test-range').val('');
        }
    });
}

// Function to remove a test row
function removeTestRow(rowId) {
    $(`#${rowId}`).remove();
}

// Function to load test types
function loadTestTypes(selectElement) {
    $.ajax({
        url: 'includes/get_test_types.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Clear existing options except the first one
                selectElement.find('option:not(:first)').remove();
                
                // Add new options
                $.each(response.tests, function(index, test) {
                    selectElement.append(`<option value="${test.id}">${test.name}</option>`);
                });
            }
        }
    });
}
