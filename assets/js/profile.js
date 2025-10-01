$(document).ready(function(){
    const token = localStorage.getItem('session_token');
    if(!token){
        alert("You must login first!");
        window.location.href = "login.html";
        return;
    }

    // Fetch profile data
    $.ajax({
        url: "php/profile.php",
        type: "GET",
        data: { token: token },
        success: function(response){
            const res = JSON.parse(response);
            if(res.status === "success"){
                const profile = res.profile || {};
                $("input[name='age']").val(profile.age || "");
                $("input[name='dob']").val(profile.dob || "");
                $("input[name='contact']").val(profile.contact || "");
            } else {
                alert(res.message);
                localStorage.removeItem('session_token');
                window.location.href = "login.html";
            }
        },
        error: function(){
            alert("Error fetching profile");
        }
    });

    // Handle form submission
    $("#profileForm").submit(function(e){
        e.preventDefault();

        const age = $("input[name='age']").val();
        const dob = $("input[name='dob']").val();
        const contact = $("input[name='contact']").val();

        $.ajax({
            url: "php/profile.php",
            type: "POST",
            data: { token: token, age: age, dob: dob, contact: contact },
            success: function(response){
                const res = JSON.parse(response);
                $("#msg").html(res.message);
            },
            error: function(){
                $("#msg").html("Error updating profile.");
            }
        });
    });
});
