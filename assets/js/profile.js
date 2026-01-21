$(document).ready(function () {
    // Check Session Token
    var token = localStorage.getItem('session_token');

    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    // Logout Functionality
    $('#logoutBtn').click(function () {
        localStorage.removeItem('session_token');
        window.location.href = 'login.html';
    });

    // Fetch Profile Data
    $.ajax({
        url: 'php/profile.php',
        type: 'POST', // Using POST for getting data too, passing token securely
        dataType: 'json',
        data: {
            action: 'fetch',
            token: token
        },
        success: function (response) {
            $('#loading').hide();
            if (response.status === 'success') {
                $('#profileForm').fadeIn();

                // Populate fields if data exists
                if (response.data) {
                    $('#age').val(response.data.age);
                    $('#dob').val(response.data.dob);
                    $('#contact').val(response.data.contact);
                    $('#address').val(response.data.address);
                }
            } else {
                alert('Session expired or invalid. Please login again.');
                localStorage.removeItem('session_token');
                window.location.href = 'login.html';
            }
        },
        error: function (xhr, status, error) {
            console.error("Fetch Error: " + error);
            $('#loading').hide();
            $('#message-box').addClass('alert-danger').text('Failed to load profile.').show();
        }
    });

    // Update Profile Data
    $('#updateBtn').click(function () {
        var age = $('#age').val();
        var dob = $('#dob').val();
        var contact = $('#contact').val();
        var address = $('#address').val();

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update',
                token: token,
                age: age,
                dob: dob,
                contact: contact,
                address: address
            },
            success: function (response) {
                if (response.status === 'success') {
                    $('#message-box').removeClass('alert-danger').addClass('alert-success').text('Profile updated successfully!').fadeIn();
                    setTimeout(function () { $('#message-box').fadeOut(); }, 3000);
                } else {
                    $('#message-box').removeClass('alert-success').addClass('alert-danger').text(response.message).fadeIn();
                }
            },
            error: function (xhr, status, error) {
                console.error("Update Error: " + error);
                $('#message-box').removeClass('alert-success').addClass('alert-danger').text('An error occurred while updating.').fadeIn();
            }
        });
    });
});
