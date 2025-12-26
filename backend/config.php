<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }
    require_once "database_operation.php";

    function validate_phone_number(string $phone_number){
        return $phone_number;
    }
    function prep_id_num(string $id_num){
        return strtoupper(trim($id_num));
    }
?>