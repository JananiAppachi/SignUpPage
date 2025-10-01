$(document).ready(function() {
    $("#registerForm").submit(function(e) {
        e.preventDefault(); // prevent normal form submission
        let formData = {
            username: $("input[name='username']").val(),
            email: $("input[name='email']").val(),
            password: $("input[name='password']").val()
        };
        $.ajax({
            url: "php/register.php",
            method: "POST",
            data: formData,
            success: function(response) {
                $("#msg").html(response);
                $("#registerForm")[0].reset();
            },
            error: function() {
                $("#msg").html("Error occurred during registration.");
            }
        });
    });
});
