<?php
session_start(); 
require_once "config.php";
$db_conn = DBOperations::getInstance();

        $username="";
        $email="";
        $phone_number="";
        $password = $_POST["password"];
        $id_type = $_POST["unique_id_type_used"];
        if($id_type=="username")
            $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
        
        if($id_type=="email")
            $email=filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);

        if($id_type=="phone number")
            $phone_number = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS); 

        $login_results = $db_conn->login($password, $username, $email, $phone_number);
        if($login_results["login_status"]){
            
            
            $display_username = $login_results["username"] ?? $login_results["user_id"];
            
            
            $_SESSION["user_id"] = $login_results["user_id"];
            $_SESSION["username"] = $display_username;
            
            // Client-side storage (Cookies for JavaScript access)
            setcookie("user_id",  $login_results["user_id"], time() + (86400 * 30), "/");
            setcookie("username", $display_username, time() + (86400 * 30), "/");
            
            echo "login successful";
            
            
            $main_page_url = "../vehicle_search_page.php"; 
            header('Location: ' . $main_page_url);
            exit;
        }
        else{
            echo "Login Failed please try again";
        }
  

?>