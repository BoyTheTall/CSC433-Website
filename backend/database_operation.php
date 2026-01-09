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
            error_log("MYSQLi prepare statement failed".$this->connection->error);
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
            $car->getModel()->getNumberOfSeats(),
            $car->getModel()->getTowCapacityKG(),
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
            $new_car_details->getModel()->getNumberOfSeats(),
            $new_car_details->getModel()->getTowCapacityKG(),
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
        $sql = "SELECT Cars.VIN, Cars.plate_number, Cars.manufacturerId AS car_man_id, Cars.modelId As Car_model_id, Cars.typeId AS car_type_id, Cars.colour, Cars.is_available, CarTypes.type_name, CarTypes.description as car_type_description, CarModels.model_name, CarModels.year, CarModels.num_seats, CarModels.tow_capacity_kg, manufacturers.name as manufacturer_name, RentalRates.daily_rate, RentalRates.effective_date 
        FROM Cars 
        INNER JOIN CarModels ON Cars.modelId=CarModels.modelId
        INNER JOIN Manufacturers ON Cars.manufacturerId=Manufacturers.manID
        INNER JOIN CarTypes ON Cars.typeID=CarTypes.typeID
        INNER JOIN RentalRates ON Cars.modelId=RentalRates.modelId ";
        $param_types="";
        $params = [];
        $flag_set = false;
        if(isset($number_plate)){
            $sql.="WHERE Cars.plate_number = ?";
            $param_types.="s";
            $params[] = $number_plate;
        }
        if(isset($vin)){
            if($flag_set){
                $sql.=" AND Cars.VIN=?";
            }
            else{
                $sql.=" WHERE Cars.VIN=?";
            }
            $param_types.= "s";
            $params[] = $vin;            
        }
        $data = $this->execute_select_query($sql, $param_types, $params);
        //Model
        $model_id = $data[0]["Car_model_Id"];
        $manufacturer_id = $data[0]["car_man_id"];
        $year = $data[0]["year"];
        $model_name = $data[0]["model_name"];
        $number_of_seats = $data[0]["num_seats"];
        $tow_capacicty = $data[0]["tow_capacity_kg"];

        $model = new Model($model_id, $manufacturer_id, $year, $model_name, $number_of_seats, $tow_capacicty);

        //manufacturer
        $manufacturer_name = $data[0]["manufacturer_name"];
        $manufacturer = new Manufacturer($manufacturer_id, $manufacturer_name);

        //Car Type
        $type_id = $data[0]["car_type_id"];
        $type_name = $data[0]["type_name"];
        $type_description = $data[0]["car_type_description"];
        $car_type = new Cartype($type_id, $type_name, $type_description);

        //car info
        $number_plate = $data[0]["plate_number"];
        $VIN = $data[0]["VIN"];
        $colour = $data[0]["colour"];
        $rental_rate = $data[0]["daily_rate"];

        $car = new Car($number_plate, $VIN, $manufacturer, $model, $car_type, $colour, $rental_rate);
        return ["operation_success"=> true, "car" => $car];
    }

    //will return an array of cars. it'll assume that it will get the IDs since we'll AJAX this shit
    function search(?string $manufacturer_id=null, ?string $type_id=null, ?string $model_id=null, ?string $colour=null, ?bool $is_available= null, ?string $year=null, ?string $number_of_seats=null, ?string $tow_capacity=null){
        $sql = "SELECT Cars.VIN, Cars.plate_number, Cars.manufacturerId AS car_man_id, Cars.modelId As Car_model_id, Cars.typeId AS car_type_id, Cars.colour, Cars.is_available, CarTypes.type_name, CarTypes.description as car_type_description, CarModels.model_name, CarModels.year, CarModels.num_seats, CarModels.tow_capacity_kg, manufacturers.name as manufacturer_name, RentalRates.daily_rate, RentalRates.effective_date, carmodels.primary_image_url AS car_image 
        FROM Cars 
        INNER JOIN CarModels ON Cars.modelId=CarModels.modelId
        INNER JOIN Manufacturers ON Cars.manufacturerId=Manufacturers.manID
        INNER JOIN CarTypes ON Cars.typeID=CarTypes.typeID
        INNER JOIN RentalRates ON Cars.modelId=RentalRates.modelId ";

        $params = [];
        $param_types = "";
        $flag_set = false;
        if(!empty($manufacturer_id)){
            $flag_set = true;
            $sql.= "WHERE Cars.manufacturerId = ? ";
            $params[] = $manufacturer_id;
            $param_types.="i";
        }
        if(!empty($type_id)){
            if($flag_set==true){
                $sql.= "AND Cars.typeId=? ";
            }
            else{
                $sql.= "WHERE Cars.typeId=? ";
            }
            $param_types.="i";
            $params[]= $type_id;
        }
        if(!empty($model_id)){
            if($flag_set){
                $sql.="AND Cars.ModelId=? ";
            }
            else{
                $sql.="WHERE Cars.ModelId=? ";
            }
            $param_types.="i";
            $params[]=$model_id;
        }
        if(!empty($colour)){
            if($flag_set){
                $sql.="AND Cars.colour=? ";
            }
            else{
                $sql.="WHERE Cars.colour=? ";
            }
            $param_types.="s";
            $params[]=$colour;
        }
        if(!empty($is_available)){
            if($flag_set){
                $sql.="AND Cars.is_available=? ";
            }
            else{
                $sql.="WHERE Cars.is_available=? ";
            }
            $param_types.="i";
            $params[]=$is_available;
        }

        if(!empty($year)){
            if($flag_set){
                $sql.="AND CarModels.year=? ";
            }
            else{
                $sql.="WHERE CarModels.year=? ";
            }
            $param_types.="i";
            $params[]=$year;
        }
        if(!empty($number_of_seats)){
            if($flag_set){
                $sql.="AND CarModels.num_seats=? ";
            }
            else{
                $sql.="WHERE CarModels.num_seats=? ";
            }
            $param_types.="i";
            $params[]=$number_of_seats;
        }
        if(!empty($tow_capacity)){
            if($flag_set){
                $sql.="AND CarModels.tow_capacity_kg=? ";
            }
            else{
                $sql.="WHERE CarModels.tow_capacity_kg=? ";
            }
            $param_types.="d";
            $params[]=$tow_capacity;
        }
        $data = $this->execute_select_query($sql, $param_types, $params);
        $array_length = count($data);
        $cars = [];
        for ($i = 0; $i < $array_length; $i++){
            //Model
            $model_id = $data[$i]["Car_model_id"];
            $manufacturer_id = $data[$i]["car_man_id"];
            $year = $data[$i]["year"];
            $model_name = $data[$i]["model_name"];
            $number_of_seats = $data[$i]["num_seats"];
            $tow_capacicty = $data[$i]["tow_capacity_kg"];
            $img_dir = $data[$i]["car_image"];
            $model = new Model($model_id, $manufacturer_id, $year, $model_name, $number_of_seats, $tow_capacicty, $img_dir);

            //manufacturer
            $manufacturer_name = $data[$i]["manufacturer_name"];
            $manufacturer = new Manufacturer($manufacturer_id, $manufacturer_name);

            //Car Type
            $type_id = $data[$i]["car_type_id"];
            $type_name = $data[$i]["type_name"];
            $type_description = $data[$i]["car_type_description"];
            $car_type = new Cartype($type_id, $type_name, $type_description);

            //car info
            $number_plate = $data[$i]["plate_number"];
            $VIN = $data[$i]["VIN"];
            $colour = $data[$i]["colour"];
            $rental_rate = $data[$i]["daily_rate"];

            $car = new Car($number_plate, $VIN, $manufacturer, $model, $car_type, $colour, $rental_rate);
            $cars[] = $car;
        }

        return ["operation_success"=> true, "cars" => $cars];
    }

    //will return the stuff we need to put in the search boxes
    function getSearchParameters(){
        //getting the manufactures
        $sql_query = "SELECT * FROM manufacturers";

        $data = $this->execute_select_query($sql_query);
        $arr_len = count($data);
        $manufacturers=[];//we'll return as aprt of an assiative array
        for($i=0; $i<$arr_len; $i++){
            $manufacturers[]= new Manufacturer($data[$i]["manId"], $data[$i]["name"]);
        }

        //getting the models
        $sql_query = "SELECT * FROM CarModels";
        $data = $this->execute_select_query($sql_query);
        $arr_len = count($data);
        $models = [];
        for($i=0; $i<$arr_len; $i++){
            $models[] = new Model($data[$i]["modelId"], $data[$i]["manufacturerId"], $data[$i]["year"], $data[$i]["model_name"], $data[$i]["num_seats"], $data[$i]["tow_capacity_kg"]);
        }

        //getting the years
        $sql_query = "SELECT DISTINCT(year) AS vehicle_year FROM CarModels";
        $data = $this->execute_select_query($sql_query);
        $years = [];
        for($i=0; $i<count($data); $i++){
            $years[] = $data[$i]["vehicle_year"];
        }

        //getting colours
        $sql_query = "SELECT DISTINCT(colour) AS vehicle_colour FROM Cars";
        $data = $this->execute_select_query($sql_query);
        $colours = [];
        for($i=0; $i<count($data); $i++){
            $colours[] = $data[$i]["vehicle_colour"];
        }

        //getting vehicle types
         $sql_query = "SELECT * FROM CarTypes";
        $data = $this->execute_select_query($sql_query);
        $car_types = [];
        for($i=0; $i<count($data); $i++){
            $car_types[] = new CarType($data[$i]["typeId"], $data[$i]["type_name"], null);
        }

        //number of seats
        $sql_query = "SELECT DISTINCT(num_seats) AS num_of_seats FROM CarModels";
        $data = $this->execute_select_query($sql_query);
        $num_seats = [];
        for($i=0; $i<count($data); $i++){
            $num_seats[] = $data[$i]["num_of_seats"];
        }

        $available_search_parameters = ["manufacturers"=> $manufacturers, "car_models"=> $models, "years"=>$years, "colours"=>$colours, "vehicle_types"=>$car_types, "number_of_seats"=>$num_seats];
        return $available_search_parameters;
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
        $number_of_seats=$data[0]["num_seats"];
        $tow_capacicty = $data[0]["tow_capacity_kg"];
        $model = new Model($model_id, $manufacturer_id, $year, $model_name, $number_of_seats, $tow_capacicty);
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
    //run the create user function first in the calling function to get the user id needed here
    function add_new_customer(Customer $new_customer){
        $sql= "INSERT INTO Customer_Details(userId, physical_address, id_document_hash, next_of_kin_contact) VALUES(?, ?, ?, ?)";
        $param_types = "isss";;
        $params=[$new_customer->getUserDetails()->getUserId(), $new_customer->getPhysicalAddress(), $new_customer->getIdHash(), $new_customer->getNextOfKinContact()];
        $operation_success = $this->execute_data_manipulation_query($sql, $param_types, $params);
        return $operation_success;
    }

    //assumes that the user id wont change (they wont see it anyway)
    function update_customer_details(Customer $new_customer_details,?string $username, ?string $new_password){
        $sql="UPDATE Users(email, phone_number) VALUES(?, ?)";
        $param_types="ss";
        $params=[$new_customer_details->getUserDetails()->getEmail(), $new_customer_details->getUserDetails()->getPhoneNumber()];

        if(isset($new_password) && isset($username)){
            $sql = "UPDATE Users(username, email, phone_number, password) VALUES(?, ?, ?, ?)";
            $param_types="ssss";
            $params=[$username, $new_customer_details->getUserDetails()->getEmail(), $new_customer_details->getUserDetails()->getPhoneNumber(), password_hash($new_password, PASSWORD_DEFAULT)];
        }
        if(!isset($new_password) && isset($username)){
            $sql = "UPDATE Users(username, email, phone_number) VALUES(?, ?, ?)";
            $param_types="ssss";
            $params=[$username, $new_customer_details->getUserDetails()->getEmail(), $new_customer_details->getUserDetails()->getPhoneNumber()];
        }
        if(isset($new_password) && !isset($username)){
            $sql = "UPDATE Users(email, phone_number, password) VALUES(?, ?, ?)";
            $param_types="sss";
            $params=[$new_customer_details->getUserDetails()->getEmail(), $new_customer_details->getUserDetails()->getPhoneNumber(), password_hash($new_password, PASSWORD_DEFAULT)];
        }

        $sql.= " WHERE userId=?";
        $param_types.="i";
        $params[]=$new_customer_details->getUserDetails()->getUserId();

        $operation_success = $this->execute_data_manipulation_query($sql, $param_types, $params);
        return $operation_success;        
    }
}
?>