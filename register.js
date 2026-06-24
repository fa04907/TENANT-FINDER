document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("#registerForm").addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent default form submission

        let formData = new FormData(this);

        fetch("register.php", { // Ensure this matches your actual PHP file
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Show success/failure message
            if (data.status === "success") {
                setTimeout(() => {
                    window.location.href = "login.html"; // Redirect to login page
                }, 2000);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("❌ An unexpected error occurred.");
        });
    });
});
