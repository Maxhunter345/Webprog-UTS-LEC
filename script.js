// script.js

// Function to confirm event deletion
function confirmDelete(eventId) {
    const confirmation = confirm("Are you sure you want to delete this event?");
    if (confirmation) {
        // Logic to delete the event (use AJAX or form submission in PHP)
        window.location.href = `delete_event.php?id=${eventId}`;
    }
}

// Form validation for registration
function validateRegistrationForm() {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    
    if (email === "" || password === "") {
        alert("Email and Password must be filled out.");
        return false;
    }

    // Email validation (basic)
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }

    return true;  // Proceed with form submission
}

// Example for using confirmDelete
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function() {
        const eventId = this.dataset.eventId;
        confirmDelete(eventId);
    });
});

// Registration form validation on submission
const registrationForm = document.querySelector('form[action="register.php"]');
if (registrationForm) {
    registrationForm.addEventListener('submit', function(event) {
        if (!validateRegistrationForm()) {
            event.preventDefault();  // Prevent form submission if validation fails
        }
    });
}
