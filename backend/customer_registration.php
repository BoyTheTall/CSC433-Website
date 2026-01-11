<?php 
    session_start(); 
    require_once "config.php";
    $db_conn = DBOperations::getInstance();

    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    $phone_number = validate_phone_number($_POST["phone_number"]);
    $password = $_POST["password"];
    $password_confirmation = $_POST["password_confirmation"];
    $user_type = "N";
    $new_user = new User(0, $email, $phone_number, $user_type);

    $physical_address = filter_input(INPUT_POST, "physical_address", FILTER_SANITIZE_SPECIAL_CHARS);
    $id_number = filter_input(INPUT_POST, "physical_address", FILTER_SANITIZE_SPECIAL_CHARS);
    $id_number_prepared = prep_id_num($id_number);
    $hashed_id = password_hash($id_number, PASSWORD_DEFAULT);
    $next_of_kin_contact = validate_phone_number($_POST["next_of_kin_contact"]);
    
    if ($password == $password_confirmation){
        // NOTE: Corrected $user to $new_user based on variable definition above
        $operation_results = $db_conn->create_user($new_user, $username, $password);
        if($operation_results["user_creation_success"]){
            echo "user created successfully<br>";
            $new_user_id = $operation_results["new_user_id"];
            $new_user = new User($new_user_id, $email, $phone_number, $user_type);
            $new_customer = new Customer($new_user, $physical_address, $hashed_id, $next_of_kin_contact);
            $customer_creation_success = $db_conn->add_new_customer($new_customer);

           
            $_SESSION["user_id"] = $new_user_id;
            $_SESSION["username"] = $username;
            
            // Set client-side cookies for JavaScript access (30 days)
            setcookie("user_id", $new_user_id, time() + (86400 * 30), "/");
            setcookie("username", $username, time() + (86400 * 30), "/");
            
            // Redirect to the main search page
            $main_page_url = "../vehicle_search_page.php"; // REDIRECT TARGET
            header('Location: ' . $main_page_url);
            exit;
            
        }
        else{
            echo "user creation failed";
        }
    }
?>