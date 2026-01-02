let userName = document.getElementById("txtUserName");
let email = document.getElementById("txtEmail");
let phone = document.getElementById("phone");
let password = document.getElementById("textPassword");
let confirmPassword = document.getElementById("textConfirmPassword");
let idNumber = document.getElementById("id_number");
let physicalAddress = document.getElementById("physical_address");
let nextOfKin = document.getElementById("next_of_kin_contact");
let form = document.getElementById("customer_registration");

function validateInput() {
    let isValid = true;
    
    // Check for empty username
    if(userName.value.trim() === "") {
        onError(userName, "Username cannot be empty");
        isValid = false;
    } else {
        onSuccess(userName);
    }
    
    // Check for valid email
    if(email.value.trim() === "") {
        onError(email, "Email cannot be empty");
        isValid = false;
    } else {
        if(!validEmail(email.value.trim())) {
            onError(email, "Invalid email");
            isValid = false;
        } else {
            onSuccess(email);
        }
    }
    
    // Check for phone number
    if(phone.value.trim() === "") {
        onError(phone, "Phone number cannot be empty");
        isValid = false;
    } else {
        if(!validPhone(phone.value.trim())) {
            onError(phone, "Invalid phone number. Format: +26876123456");
            isValid = false;
        } else {
            onSuccess(phone);
        }
    }
    
    // Check for password (WITH REGEX)
    if(password.value.trim() === "") {
        onError(password, "Password cannot be empty");
        isValid = false;
    } else {
        if(!validPassword(password.value)) {
            onError(password, "Password must be 8+ chars with uppercase, lowercase, number & special char");
            isValid = false;
        } else {
            onSuccess(password);
        }
    }
    
    // Check for confirm password
    if(confirmPassword.value.trim() === "") {
        onError(confirmPassword, "Please confirm your password");
        isValid = false;
    } else {
        if(password.value.trim() !== confirmPassword.value.trim()) {
            onError(confirmPassword, "Passwords do not match");
            isValid = false;
        } else {
            onSuccess(confirmPassword);
        }
    }
    
    // Check for ID number
    if(idNumber.value.trim() === "") {
        onError(idNumber, "ID number cannot be empty");
        isValid = false;
    } else {
        onSuccess(idNumber);
    }
    
    // Check for physical address
    if(physicalAddress.value.trim() === "") {
        onError(physicalAddress, "Physical address cannot be empty");
        isValid = false;
    } else {
        onSuccess(physicalAddress);
    }
    
    // Check for next of kin contact
    if(nextOfKin.value.trim() === "") {
        onError(nextOfKin, "Next of kin contact cannot be empty");
        isValid = false;
    } else {
        if(!validPhone(nextOfKin.value.trim())) {
            onError(nextOfKin, "Invalid phone number. Format: +26876123456");
            isValid = false;
        } else {
            onSuccess(nextOfKin);
        }
    }
    
    return isValid;
}

form.addEventListener("submit", (event) => {
    event.preventDefault();
    if(validateInput()) {
        form.submit();
    }
});

function onSuccess(input) {
    let parent = input.parentElement;
    let messageElement = parent.querySelector("small");
    messageElement.style.visibility = "hidden";
    messageElement.innerText = "";
    parent.classList.remove("error");
    parent.classList.add("success");
}

function onError(input, message) {
    let parent = input.parentElement;
    let messageElement = parent.querySelector("small");
    messageElement.style.visibility = "visible";
    messageElement.innerText = message;
    parent.classList.add("error");
    parent.classList.remove("success");
}

// Email validation
function validEmail(email) {
    return /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email);
}

// Phone validation
function validPhone(phone) {
    phone = phone.replace(/\s/g, '');
    const mobile_phone_pattern = /^\+2687[689][0-9]{6}$/;
    return mobile_phone_pattern.test(phone);
}

function validPassword(password) {
    // Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
    const strongPasswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/;
    return strongPasswordPattern.test(password);
}