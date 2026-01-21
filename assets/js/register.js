$(document).ready(function() {
    $('#registerBtn').click(function(e) {
        e.preventDefault(); // Prevent default form behavior if it was a submit button

        // Basic Validation
        var username = $('#username').val().trim();
        var email = $('#email').val().trim();
        var password = $('#password').val().trim();

        if (username === '' || email === '' || password === '') {
            $('#message-box').removeClass('alert-success').addClass('alert-danger').text('Please fill in all fields.').fadeIn();
            return;
        }

        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            dataType: 'json',
            data: {
                username: username,
                email: email,
                password: password
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#message-box').removeClass('alert-danger').addClass('alert-success').text(response.message).fadeIn();
                    // Optional: Redirect to login after delay
                    setTimeout(function() {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    $('#message-box').removeClass('alert-success').addClass('alert-danger').text(response.message).fadeIn();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: " + error);
                $('#message-box').removeClass('alert-success').addClass('alert-danger').text('An error occurred while processing your request.').fadeIn();
            }
        });
    });
});
