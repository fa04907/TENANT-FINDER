// Show/Hide Owner Fields in Registration
function showAdditionalFields() {
    var userType = document.getElementById("userType");
    var ownerFields = document.getElementById("ownerFields");

    if (userType && ownerFields) {
        ownerFields.style.display = (userType.value === "owner") ? "block" : "none";
    }
}

// Ensure fields update when user type changes
document.addEventListener("DOMContentLoaded", function () {
    showAdditionalFields();
    var userTypeSelect = document.getElementById("userType");
    if (userTypeSelect) {
        userTypeSelect.addEventListener("change", showAdditionalFields);
    }
});

// Filter Properties by Type & Location with Smooth Transition
function filterProperties() {
    var type = document.getElementById("propertyType").value.toLowerCase();
    var location = document.getElementById("location").value.toLowerCase();
    var properties = document.querySelectorAll(".property-item");

    properties.forEach(property => {
        var propType = property.getAttribute("data-type").toLowerCase();
        var propLocation = property.getAttribute("data-location").toLowerCase();
        var matchesType = type === "all" || propType.includes(type);
        var matchesLocation = location === "" || propLocation.includes(location);

        if (matchesType && matchesLocation) {
            property.style.display = "block"; // Ensure it's visible
            setTimeout(() => {
                property.style.opacity = "1"; // Fade in smoothly
            }, 100);
        } else {
            property.style.opacity = "0"; // Start fade-out
            setTimeout(() => property.style.display = "none", 200);
        }
    });
}

// Attach event listener for filtering
document.addEventListener("DOMContentLoaded", function () {
    var filterBtn = document.getElementById("filterButton"); // Ensure you have a button with this ID
    if (filterBtn) {
        filterBtn.addEventListener("click", filterProperties);
    }
});

// Confirm Before Property Withdrawal
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".withdraw-btn").forEach(button => {
        button.addEventListener("click", function () {
            if (confirm("Are you sure you want to withdraw this property? You will receive a 75% refund.")) {
                alert("Property withdrawn successfully.");
                // Here, you can add an API call or a backend request to process the withdrawal
            }
        });
    });
});
