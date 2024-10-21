// script.js

// script.js

// Toggle Dark Mode
const toggleThemeButton = document.getElementById('toggleTheme');
const body = document.body;

toggleThemeButton.addEventListener('click', function() {
    body.classList.toggle('dark-mode');
});

// Countdown Timer
var eventDate = new Date("Nov 8, 2024 00:00:00").getTime();

var countdownFunction = setInterval(function() {
    var now = new Date().getTime();
    var distance = eventDate - now;

    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById("days").innerHTML = days;
    document.getElementById("hours").innerHTML = hours;
    document.getElementById("minutes").innerHTML = minutes;
    document.getElementById("seconds").innerHTML = seconds;

    if (distance < 0) {
        clearInterval(countdownFunction);
        document.getElementById("countdown").innerHTML = "Event has started!";
    }
}, 1000);

// Toggle Mobile Menu
function toggleMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// Function to confirm event deletion
function confirmDelete(eventId) {
    const confirmation = confirm("Are you sure you want to delete this event?");
    if (confirmation) {
        // Logic to delete the event (use AJAX or form submission in PHP)
        window.location.href = `delete_event.php?id=${eventId}`;
    }
}

// Tambahkan ini di dalam tag <script> di index.php
function showPassword(input) {
    const originalType = input.getAttribute('type');
    input.setAttribute('type', 'text');

    setTimeout(() => {
        input.setAttribute('type', originalType);
    }, 500); // Ubah kembali menjadi password setelah 500 ms
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