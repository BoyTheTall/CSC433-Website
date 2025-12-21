<?php 
  class DBOperations{  
    private $connection;
    public function __construct()
    {
        $connection = $this->connect_to_db();
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
    function execute_data_manipulation_query(string $sql_query, string $parameter_types='', array $parameters){
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
            $prepared_statement->close();
    }

    function add_car_to_database(Car $car){
        $sql_query="INSERT INTO cars";
    }
}
?>