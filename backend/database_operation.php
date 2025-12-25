<?php 
require_once "classes.php";
  class DBOperations{  
    private $connection;
    private static ?DBOperations $instance = null;
    public static function getInstance(): DBOperations{
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __clone(){
        throw new \Exception('Not implemented');
    }
    private function __construct(){
        $this->connection = $this->connect_to_db();
        
    }
    function connect_to_db(){
        $db_username="brid";
        $db_password = "chirp";
        $servername = "localhost";
        $db_name = "Mizoragi_Car_Rental_DB";
        $connection = Null;
        
        $connection = new mysqli($servername, $db_username, $db_password, $db_name);
        if($connection -> connect_error){
            die("Connection Failed :( ".$connection ->connect_error);
        }
        
        return $connection;
    }
    private function get_last_insert_id(){
        return $this->connection->insert_id;
    }
    function execute_select_query(string $sql_query, string $parameter_types='', array $parameters=[], $returnResultSetObject=false){

        $prepared_statement = $this->connection->prepare($sql_query);
        if($prepared_statement == false){
            error_log("MYSQLi prepare staement failed".$this->connection->error);
        }
        
        if(!empty($parameters) && !empty($parameter_types)){
            //binding parameters that arent empty
            $prepared_statement->bind_param($parameter_types, ...$parameters);
        }

        $execution_success = $prepared_statement->execute();
         if(!$execution_success){
            //error stuff
            error_log("MYSQLi query execute failed: ".$this->connection->error);
            $prepared_statement->close();
        }

        $result_set = $prepared_statement->get_result();
        if(!$returnResultSetObject){
            
            $data = $result_set->fetch_all(MYSQLI_ASSOC);
            
            //closing stuff
            $prepared_statement->close();
            $result_set->free();
            return $data;
        }
        else{
            //closing stuff
            $prepared_statement->close();
            return $result_set;
        }       
    }

    //for the inserts, updates and deletes
    function execute_data_manipulation_query(string $sql_query, string $parameter_types='', array $parameters=[]){
        $operation_success = false;
        $prepared_statement = $this->connection->prepare($sql_query);
        if($prepared_statement == false){
            error_log("MYSQLi prepare staement failed".$this->connection->error);
        }
        
        if(!empty($parameters) && !empty($parameter_types)){
            //binding parameters that arent empty
            $prepared_statement->bind_param($parameter_types, ...$parameters);
        }

        $execution_success = $prepared_statement->execute();
         if(!$execution_success){
            //error stuff
            error_log("MYSQLi query execute failed: ".$this->connection->error);
            }
        else{
            $operation_success = true;
        }
            $prepared_statement->close();
            return $operation_success;
    }

    //car functions
    function add_car_to_database(Car $car){
        $sql_query="INSERT INTO cars(VIN, plate_number, manufacturerId, modelId, typeId, colour, num_seats, tow_capacity_kg, is_available) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $param_types =  "ssiisidi";
        $params = [$car->getVIN(),
            $car->getNumberPlate(),
            $car->getManufacturer()->getManufacturerId(), 
            $car->getModel()->getModelId(),
            $car->getType()->getTypeId(),
            $car->getColor(),
            $car->getNumberOfSeats(),
            $car->getTowCapacityKG(),
            true ];
        $operation_success = $this->execute_data_manipulation_query($sql_query, $param_types, $params);
        if(!$operation_success){
            echo "Car addition failed<br>";
        }
        else{
            echo "Car added successfully<br>";
        }
    }
    function remove_car_from_database(Car $car){
        //since we can't remove this thing without losing customer records we'll just set it to unavailable without having a rental record
        $sql_query = "UPDATE cars SET isavailable=0 WHERE VIN=? OR platenumber=?";
        $param_types="ss";
        $params=[$car->getVIN(), $car->getNumberPlate()];
        $operation_success =$this->execute_data_manipulation_query($sql_query, $param_types, $params);
        if(!$operation_success){
            echo "Car removal failed<br>";
        }
        else{
            echo "car removal successful<br>";
        }
    }
    function update_car_details(Car $old_car_details, Car $new_car_details){
        $sql_query="UPDATE cars 
        SET VIN = ?, 
        plate_number = ?,
        manufacturerId = ?, 
        modelId = ?, 
        typeId = ?, 
        colour = ?, 
        num_seats = ?, 
        tow_capacity_kg = ?, 
        
        WHERE VIN=? OR plate_number=?";
        $param_types = "ssiisidss";
        $params=[$new_car_details->getVIN(),
            $new_car_details->getNumberPlate(),
            $new_car_details->getManufacturer()->getManufacturerId(), 
            $new_car_details->getModel()->getModelId(),
            $new_car_details->getType()->getTypeId(),
            $new_car_details->getColor(),
            $new_car_details->getNumberOfSeats(),
            $new_car_details->getTowCapacityKG(),
            $old_car_details->getVIN(),
            $old_car_details->getNumberPlate()];
        $operation_success = $this->execute_data_manipulation_query($sql_query, $param_types, $params);
        if(!$operation_success){
            echo "Car update failed<br>";
        }
        else{
            echo "Car updated successfully<br>";
        }
    }

    function get_car(string $number_plate="", string $vin=""){

    }

    //model functions
    function get_model(int $model_id): Model{
        $sql_query = "SELECT * FROM CarModels WHERE modelID = ?";
        $param_types="i";
        $params = [$model_id];

        $data = $this->execute_select_query($sql_query, $param_types, $params);
        $manufacturer_id = $data[0]["manufacturerId"];
        $model_name = $data[0]["model_name"];
        $year = $data[0]["year"];
        $model = new Model($model_id, $manufacturer_id, $year, $model_name);
        return $model;
    }

    //manufacturer functions
    function get_manufacturer(?int $manufacturer_id=null, ?string $manu_name=null):Manufacturer{
        $sql_query = "SELECT * FROM Manufacturers ";
        $params = [];
        $param_types = "";
        if(!empty($manu_name)){
            $sql_query.="WHERE name=?";
            $param_types.="s";
            $params[] = $manu_name;
        }
        if(!empty($manufacturer_id)){
            $sql_query.="WHERE manID = ?";
            $param_types.= "i";
            $params[]=$manufacturer_id;
        }
        $data = $this->execute_select_query($sql_query, $param_types, $params);
        $manufacturer = new Manufacturer($data[0]["manId"], $data[0]["name"]);
        return $manufacturer;
    }

    //car type functions
    
    //user functions

    //function will return a boolean, true if the login was successfult, false otherwise
    function login($password, $username="", $email="", $phoneNumber=""){
        //only one will be used
        $sql = "SELECT userId, password FROM Users Where ";
        $params = [];
        $param_types = "s";
        if(!empty($username)){
            $sql.="username=?";
            $params[0] = $username;
        }
        if(!empty($email)){
            $sql.="email=?";
            $params[0]=$email;
        }
        if(!empty($phoneNumber)){
            $sql.="phoneNumber=?";
            $params[0] = $phoneNumber;
        }
        $data = $this->execute_select_query($sql, $param_types, $params);
        $password_hashed =$data[0]["password"];
        $user_id = $data[0]["userId"];
        $login_status = password_verify($password, $password_hashed);
        return ["login_status" => $login_status, "user_id"=>$user_id];
    }

    function fetch_user_details(int $user_id, $username="", $email="", $phoneNumber=""):User{
        $sql_query = "SELECT * FROM Users WHERE ";
        $flag_set = false;
        $param_types = "";
        $params = [];
        if(!empty($user_id)){
            if($flag_set){
                $sql_query.="AND userId=? ";
            }
            else{
                $sql_query.="userId=? ";
            }
            $params[] = $user_id;
            $param_types.="i";
        }
        if(!empty($username)){
            if($flag_set){
                $sql_query.="AND username=? ";
            }
            else{
                $sql_query.="username=? ";
            }
            $params[] = $username;
            $param_types.="s";

        }
        if(!empty($email)){
            if($flag_set){
                $sql_query.="AND email=? ";
            }
            else{
                $sql_query.="email=? ";
            }
            $param_types.="s";
            $params[] = $email;

        }
        if(!empty($phoneNumber)){
            if($flag_set){
                $sql_query.="AND phone_number=? ";
            }
            else{
                $sql_query.="phone_number=? ";
            }
            $param_types.="s";
            $params[] = $phoneNumber;
        }
        $data = $this->execute_select_query($sql_query, $param_types, $params);

        $user = new User($data[0]["userId"], $data[0]["email"], $data[0]["phone_number"], $data[0]["type_of_user"]);
        return $user;
    }

    //returns associative array with a boolean flag for stating if a user was successfuly added to the db and the user id if it was added successfully
    function create_user(User $user, string $username, string $password){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Users(username, email, phone_number, password, type_of_user) VALUES (?, ?, ?, ?, ?)";
        $param_types="sssss";
        $params=[$username, $user->getEmail(), $user->getPhoneNumber(), $hashed_password, $user->getUserType()];
        $operation_success = $this->execute_data_manipulation_query($sql, $param_types, $params);
        if($operation_success){
            return ["user_creation_success"=>$operation_success, "new_user_id"=>$this->get_last_insert_id()];
        }
        else{
            return ["user_creation_success"=>$operation_success, "new_user_id"=>$this->get_last_insert_id()];
        }
    }
    //customer functions
    function fetch_customer_details(?User $user_details=null, int $user_id=0, $username="", $email="", $phoneNumber=""): Customer{
        if(empty($user_details)){
            $user_details = $this->fetch_user_details($user_id, $username, $email, $phoneNumber);
        }
        $sql_query = "SELECT * FROM CustomerDetails WHERE userId=?";
        $params = [$user_details->getUserID()];
        $param_types = "i";
        $data = $this->execute_select_query($sql_query, $param_types, $params);
        $physical_address = $data[0]["physical_address"];
        $persornal_id_hash = $data[0]["id_document_hash"];
        $next_of_kin_contact = $data[0]["next_of_kin_contact"];
        $customer = new Customer($user_details, $physical_address, $persornal_id_hash, $next_of_kin_contact);
        return $customer;
        
    }
    
    function update_customer_details(Customer $old_customer_details, Customer $new_customer_details){

    }
}
?>