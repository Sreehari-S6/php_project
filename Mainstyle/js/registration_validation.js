function validateName(field) {
    const error = document.getElementById("nameError");
    const namePattern = /^[A-Za-z\s]+$/;

    if (field.value.trim() === "") {
        error.textContent = "Name is required.";
    } else if (!namePattern.test(field.value.trim())) {
        error.textContent = "Name can contain only letters and spaces.";
    } else {
        error.textContent = "";
    }
}

function validateEmail(field) {
    const error = document.getElementById("emailError");
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const email = field.value.trim();

    if (!pattern.test(email)) {
        error.textContent = "Enter a valid email.";
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "check_email.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            if (xhr.responseText.trim() === "exists") {
                error.textContent = "Email already exists.";
            } else {
                error.textContent = "";
            }
        }
    };
    xhr.send("email=" + encodeURIComponent(email));
}

function validatephone(field) {
    const phone = field.value.trim();
    const error = document.getElementById("phoneError");
    const phonePattern = /^\d{10}$/;

    if (!phonePattern.test(phone)) {
        error.textContent = "Please enter a valid 10-digit phone number.";
    } else {
        error.textContent = "";
    }
}

function validatePassword(field) {
    const error = document.getElementById("passwordFieldError");
    error.textContent = field.value.length < 6 ? "Password must be at least 6 characters." : "";
}

function validateConfirmPassword(field) {
    const error = document.getElementById("passwordError");
    const pwd = document.getElementById("password").value;
    error.textContent = pwd !== field.value ? "Passwords do not match." : "";
}

function validateForm() {
    validateName(document.querySelector('[name="name"]'));
    validateEmail(document.querySelector('[name="email"]'));
    validatephone(document.querySelector('[name="phone"]'));
    validatePassword(document.getElementById("password"));
    validateConfirmPassword(document.getElementById("confirm_password"));

    return document.querySelectorAll(".error:empty").length === 5;
}
