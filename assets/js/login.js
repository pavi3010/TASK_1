$(document).ready(function () {
    $('#loginBtn').click(function (e) {
        e.preventDefault();

        var email = $('#email').val().trim();
        var password = $('#password').val().trim();

        if (email === '' || password === '') {
            $('#message-box').removeClass('alert-success').addClass('alert-danger').text('Please enter email and password.').fadeIn();
            return;
        }

        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            dataType: 'json',
            data: {
                email: email,
                password: password
            },
            success: function (response) {
                if (response.status === 'success') {
                    // STORE SESSION TOKEN IN LOCALSTORAGE (Requirement)
                    localStorage.setItem('session_token', response.token);

                    $('#message-box').removeClass('alert-danger').addClass('alert-success').text('Login successful! Redirecting...').fadeIn();

                    setTimeout(function () {
                        window.location.href = 'profile.html';
                    }, 1000);
                } else {
                    $('#message-box').removeClass('alert-success').addClass('alert-danger').text(response.message).fadeIn();
                }
            },
            error: function (xhr, status, error) {
                console.error("Error: " + error);
                $('#message-box').removeClass('alert-success').addClass('alert-danger').text('An error occurred during login.').fadeIn();
            }
        });
    });
});
