<?php
session_start(); // Start the session to ensure cookie functionality

// 🟢 NEW: Check for the user_id cookie
if (!isset($_COOKIE['user_id'])) {
    // If the cookie is not set, alert the user and redirect to the login page
    // We use JavaScript for the alert/redirect to ensure the user sees the message.
    echo "<script>alert('You need to be logged in to update your details.'); window.location.href = './frontend/login.html';</script>";
    exit;
}

// 🟢 Store the user ID from the cookie if it exists
$user_id = $_COOKIE['user_id'];

// NOTE: You can now use $user_id to fetch the customer's existing data 
// and pre-fill the form fields below if needed.
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <link href="styles.css" rel="stylesheet" type="text/css"/>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Customer Details Update</h2>
        </div>
        <form id="customer_registration" action="./backend/customer_update.php" method="post">
            
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

            <div class="form-control">
                <label for="username">Username:</label>
                <input type="text" name="username" id="txtUserName" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <label for="email">Email:</label>
                <input type="email" name="email" id="txtEmail" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <label for="phone_number">Phone Number:</label>
                <input type="tel" name="phone_number" id="phone" placeholder="+26876123456" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <label for="password">Password:</label>
                <input type="password" name="password" id="textPassword" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <label for="password_confirmation">Confirm Password:</label>
                <input type="password" name="password_confirmation" id="textConfirmPassword" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <label for="id_number">ID Number:</label>
                <input type="text" name="id_number" id="id_number" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <label for="physical_address">Physical Address:</label>
                <input type="text" name="physical_address" id="physical_address" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <label for="next_of_kin_contact">Next Of Kin Contact:</label>
                <input type="tel" name="next_of_kin_contact" id="next_of_kin_contact" placeholder="+26876123456" required>
                <small>error message</small>
            </div>
            
            <div class="form-control">
                <button type="submit">Update Your Details</Details></button>
            </div>
        </form>
    </div>
    <script src="validateCustomer.js"></script>
</body>
</html>