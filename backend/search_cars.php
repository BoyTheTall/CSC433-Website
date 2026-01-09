

<?php 
    header('Content-Type: application/json');
    require_once "config.php";
    require_once "classes.php";
    require_once "database_operation.php";
 
    $db_conn = DBOperations::getInstance();
    function search_for_cars(DBOperations $db_conn){
        $model_id = $_GET["model"];
        $manufacturer_id = $_GET["manufacturer"];
        $vehicle_type = $_GET["vehicle_type"];
        $colour = $_GET["vehicle_colour"];
        $year = $_GET["vehicle_year"];
        $number_of_seats = $_GET["number_of_seats"];
        
        $operation_results = $db_conn->search($manufacturer_id, $vehicle_type, $model_id, $colour, null, $year, $number_of_seats);
        echo json_encode($operation_results["cars"]);
    }
    search_for_cars($db_conn);
    
    exit;

?>