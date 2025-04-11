
// Add this to your $(document).ready(function() {

$(document).ready(function() {
    // Previous code remains the same until the new additions...

    // View notes button click
    $(document).on('click', '.view-notes', function() {
        var applicantId = $(this).data('id');
        
        $.get('resume.php?view_id=' + applicantId, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                var notesContent = data.notes || 'No notes available';
                $('#notesContent').text(notesContent);
                $('#notesModal').show();
            }
        }, 'json');
    });

    // View questions button click
    $(document).on('click', '.view-questions', function() {
        var applicantId = $(this).data('id');
        
        $.get('resume.php?view_id=' + applicantId, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                $('#question1').text(data.q1);
                $('#question2').text(data.q2);
                $('#question3').text(data.q3);
                $('#question4').text(data.q4);
                $('#question5').text(data.q5);
                $('#questionsModal').show();
            }
        }, 'json');
    });

    // Kebab menu handling
    $(document).on('click', '.kebab-icon', function(e) {
        e.stopPropagation();
        $(this).siblings('.kebab-dropdown').toggle();
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.kebab-menu').length) {
            $('.kebab-dropdown').hide();
        }
    });

    // View button in kebab menu
    $(document).on('click', '.view-btn', function() {
        var applicantId = $(this).data('id');
        $('.kebab-dropdown').hide();
        
        $.get('resume.php?view_id=' + applicantId, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                $('#detail-name').text(data.full_name);
                $('#detail-course').text(data.program_course);
                $('#detail-school').text(data.school);
                $('#detail-contact').text(data.contact_number);
                $('#detail-email').text(data.email);
                $('#detail-hours').text(data.ojt_hours);
                $('#detail-role').text(data.roles);
                $('#detail-status').text(data.status).attr('class', 'status-badge status-' + data.status);
                $('#detail-notes').text(data.notes || 'No notes available');
                $('#detail-resume-link').attr('href', data.resume_path);
                
                // Add questions to view modal
                $('#detail-questions').html(`
                    <h3>Questions:</h3>
                    <div class="question-item">
                        <h4>Why do you want to join our company?</h4>
                        <p>${data.q1}</p>
                    </div>
                    <div class="question-item">
                        <h4>What skills can you bring to our team?</h4>
                        <p>${data.q2}</p>
                    </div>
                    <div class="question-item">
                        <h4>Describe a challenging project you worked on</h4>
                        <p>${data.q3}</p>
                    </div>
                    <div class="question-item">
                        <h4>How do you handle teamwork?</h4>
                        <p>${data.q4}</p>
                    </div>
                    <div class="question-item">
                        <h4>Where do you see yourself in 5 years?</h4>
                        <p>${data.q5}</p>
                    </div>
                `);
                
                $('#viewModal').show();
            }
        }, 'json');
    });

// Profile Dropdown
document.addEventListener("DOMContentLoaded", function () {
    const userContainer = document.getElementById("userContainer");
    const userDropdown = document.getElementById("userDropdown");

    userContainer.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent dropdown from closing when clicking inside
        userDropdown.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!userContainer.contains(event.target) && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove("show");
        }
    });
});
$(document).ready(function() {
    // User dropdown toggle
    $('#userIcon').click(function() {
        $('#userDropdown').toggle();
    });

    // Logout modal handling
    $('#logoutLink').click(function(e) {
        e.preventDefault();
        $('#logoutModal').show();
    });

    $('#logoutCancel').click(function() {
        $('#logoutModal').hide();
    });

    $('#logoutConfirm').click(function() {
        window.location.href = '../../../login/logout.php';
    });

    // Close modals when clicking the X button
    $('.close-modal').click(function() {
        $(this).closest('.modal').hide();
    });

    // Close modals when clicking outside
    $(window).click(function(event) {
        if ($(event.target).hasClass('modal')) {
            $('.modal').hide();
        }
    });

    // Search functionality
    $('#searchButton').click(function() {
        filterApplications();
    });

    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            filterApplications();
        }
    });

    // Filter functionality
    $('#statusFilter').change(function() {
        filterApplications();
    });

    // View application details
    $(document).on('click', '.view-resume', function(e) {
        e.preventDefault();
        var resumeUrl = $(this).attr('href');
        window.open(resumeUrl, '_blank');
    });

    // Edit button click
    $(document).on('click', '.edit-btn', function() {
        var applicantId = $(this).data('id');
        $('#applicantId').val(applicantId);
        
        // Fetch current status and notes
        $.get('resume.php?view_id=' + applicantId, function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                $('#statusSelect').val(data.status);
                $('#notes').val(data.notes || '');
                $('#editModal').show();
            }
        }, 'json');
    });

    // Delete button click
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this application?')) {
            var applicantId = $(this).data('id');
            
            $.ajax({
                url: 'resume.php',
                type: 'POST',
                data: { delete_id: applicantId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('tr[data-id="' + applicantId + '"]').remove();
                        checkEmptyTable();
                    } else {
                        alert('Error: ' + (response.error || 'Failed to delete'));
                    }
                },
                error: function() {
                    alert('Error: Failed to communicate with server');
                }
            });
        }
    });

    // Status form submission
    $('#statusForm').submit(function(e) {
        e.preventDefault();
        var applicantId = $('#applicantId').val();
        var status = $('#statusSelect').val();
        var notes = $('#notes').val();
        
        $.ajax({
            url: 'resume.php',
            type: 'POST',
            data: {
                update_id: applicantId,
                status: status,
                notes: notes
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the status in the table
                    var statusCell = $('tr[data-id="' + applicantId + '"] .status-cell');
                    statusCell.html('<span class="status-badge status-' + status + '">' + status + '</span>');
                    $('#editModal').hide();
                } else {
                    alert('Error: ' + (response.error || 'Failed to update'));
                }
            },
            error: function() {
                alert('Error: Failed to communicate with server');
            }
        });
    });

    // Check if table is empty and show message
    function checkEmptyTable() {
        if ($('#resumeTableBody tr:not(.no-data)').length === 0) {
            $('#resumeTableBody').html('<tr><td colspan="10" class="no-data">No applications found</td></tr>');
        }
    }

    // Filter applications based on search and status
    function filterApplications() {
        var searchText = $('#searchInput').val().toLowerCase();
        var statusFilter = $('#statusFilter').val();
        
        $('#resumeTableBody tr').each(function() {
            var $row = $(this);
            if ($row.hasClass('no-data')) return true;
            
            var rowText = $row.text().toLowerCase();
            var rowStatus = $row.find('.status-badge').text();
            
            var matchesSearch = searchText === '' || rowText.indexOf(searchText) > -1;
            var matchesStatus = statusFilter === '' || rowStatus === statusFilter;
            
            if (matchesSearch && matchesStatus) {
                $row.show();
            } else {
                $row.hide();
            }
        });
        
        // Check if any rows are visible
        var visibleRows = $('#resumeTableBody tr:visible:not(.no-data)').length;
        if (visibleRows === 0) {
            if ($('#resumeTableBody .no-data').length === 0) {
                $('#resumeTableBody').append('<tr><td colspan="10" class="no-data">No matching applications found</td></tr>');
            }
        } else {
            $('#resumeTableBody .no-data').remove();
        }
    }
});
});