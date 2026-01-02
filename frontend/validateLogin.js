let username = document.getElementById("username");
let password = document.getElementById("password");
let idType = document.getElementById("unique_id_type_used");
let form = document.getElementById("login_form");

function validateLoginInput() {
    let isValid = true;
    
    // Check for empty username/email/phone
    if(username.value.trim() === "") {
        onError(username, "This field cannot be empty");
        isValid = false;
    } else {
        // Validate based on selected ID type
        let selectedType = idType.value;
        
        if(selectedType === "email") {
            if(!validEmail(username.value.trim())) {
                onError(username, "Invalid email format");
                isValid = false;
            } else {
                onSuccess(username);
            }
        } else if(selectedType === "phone number") {
            if(!validPhone(username.value.trim())) {
                onError(username, "Invalid phone number. Format: +26876123456");
                isValid = false;
            } else {
                onSuccess(username);
            }
        } else if(selectedType === "username") {
            onSuccess(username);
        } else {
            onError(idType, "Please select ID type");
            isValid = false;
        }
    }
    
    // Check for empty password
    if(password.value.trim() === "") {
        onError(password, "Password cannot be empty");
        isValid = false;
    } else {
        onSuccess(password);
    }
    
    return isValid;
}

form.addEventListener("submit", (event) => {
    event.preventDefault();
    if(validateLoginInput()) {
        form.submit(); 
    }
});

function onSuccess(input) {
    let parent = input.parentElement;
    let messageElement = parent.querySelector("small");
    if(messageElement) {
        messageElement.style.visibility = "hidden";
        messageElement.innerText = "";
    }
    parent.classList.remove("error");
    parent.classList.add("success");
}

function onError(input, message) {
    let parent = input.parentElement;
    let messageElement = parent.querySelector("small");
    if(messageElement) {
        messageElement.style.visibility = "visible";
        messageElement.innerText = message;
    }
    parent.classList.add("error");
    parent.classList.remove("success");
}

function validEmail(email) {
    return /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email);
}

function validPhone(phone) {
    phone = phone.replace(/\s/g,'');
    const mobile_phone_pattern = /^\+2687[689][0-9]{6}$/;
    return mobile_phone_pattern.test(phone);
}