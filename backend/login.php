<?php
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
            $phone_number = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS); //change later to a proper phone number snaitising function

        $login_results = $db_conn->login($password, $username, $email, $phone_number);
        if($login_results["login_status"]){
            $_SESSION["userID"] = $login_results["user_id"];
            echo "login successful";
        }
        else{
            echo "Login Failed please try again";
        }
  

?>