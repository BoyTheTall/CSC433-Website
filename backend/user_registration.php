<?php 
    require_once "config.php";
    $db_conn = DBOperations::getInstance();

    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    $phone_number = validate_phone_number($_POST["phone_number"]);
    $password = $_POST["password"];
    $password_confirmation = $_POST["password_confirmation"];
    $user_type = $_POST["user_type"];
    $new_user = new User(0, $email, $phone_number, $user_type);
    if ($password == $password_confirmation){
        $operation_results = $db_conn->create_user($user, $username, $password);
        if($operation_results["user_creation_success"]){
            echo "user created successfully<br>";
        }
        else{
            echo "user creation failed";
        }
    }
?>