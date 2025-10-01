$(document).ready(function() {
    $("#loginForm").submit(function(e) {
        e.preventDefault();
        let formData = {
            email: $("input[name='email']").val(),
            password: $("input[name='password']").val()
        };
        $.ajax({
            url: "php/login.php",
            method: "POST",
            data: formData,
            success: function(response) {
                let res = JSON.parse(response);
                if(res.status === "success") {
                    localStorage.setItem("session_token", res.token); // store session token
                    window.location.href = "profile.html"; // redirect
                } else {
                    $("#msg").html(res.message);
                }
            },
            error: function() {
                $("#msg").html("Error occurred during login.");
            }
        });
    });
});
