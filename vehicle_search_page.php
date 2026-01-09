
<?php 
    require_once __DIR__."/backend/config.php";
    require_once __DIR__."/backend/classes.php";
    require_once __DIR__."/backend/database_operation.php";

    $db_conn = DBOperations::getInstance();
    $available_search_parameters = $db_conn->getSearchParameters();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=<device-width>, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form id="vehicle_search" action="./backend/search_cars.php" method="get">
        <label for="manufacturer">Manufacturer</label><select name="manufacturer" id="manufacturer">
            <option value="" disable selected>--Choose a manufacturer--</option>
            <?php 
                $manufacturers = $available_search_parameters["manufacturers"];
                foreach($manufacturers as $manufacturer){
                    echo '<option value = "'.htmlspecialchars($manufacturer->getManufacturerId()).'">';
                    echo htmlspecialchars($manufacturer->getName());
                    echo '</option>';
                }
            ?>
        </select><br>
        <label for="model">Model</label><select name="model" id="model">
            <option value="" disable selected>--Choose a car model--</option>
            <?php 
                $car_models = $available_search_parameters["car_models"];
                foreach($car_models as $car_model){
                    echo '<option value="'.htmlspecialchars($car_model->getModelId()).'">';
                    echo htmlspecialchars($car_model->getName().'('.$car_model->getYear().')');
                    echo '</option>';
                }
            ?>

        </select><br>
        <label for="vehicle_type">Vehicle Type</label><select name="vehicle_type" id="vehicle_type">
            <option value="" disable selected>--Choose a vehicle type--</option>
            <?php 
                $vehicle_types = $available_search_parameters["vehicle_types"];
                foreach($vehicle_types as $vehicle_type){
                    echo '<option value="'.htmlspecialchars($vehicle_type->getTypeId()).'">';
                    echo htmlspecialchars($vehicle_type->getTypeName());
                    echo '</option>';
                }
            ?>

        </select><br>
        <label for="vehicle_colour">Vehicle Colour</label><select name="vehicle_colour" id="vehicle_colour">
            <option value="" disable selected>--Choose a vehicle colour--</option>
            <?php 
                $vehicle_colours = $available_search_parameters["colours"];
                foreach($vehicle_colours as $colour){
                    echo '<option value="'.htmlspecialchars($colour).'">';
                    echo htmlspecialchars($colour);
                    echo '</option>';
                }
            ?>
        </select><br>
        <label for="vehicle_year">Vehicle Year</label><select name="vehicle_year" id="vehicle_year">
            <option value="" disable selected>--Choose a vehicle year--</option>
                <?php 
                    $vehicle_years = $available_search_parameters["years"];
                    foreach($vehicle_years as $year){
                        echo '<option value="'.htmlspecialchars($year).'">';
                        echo htmlspecialchars($year);
                        echo '</option>';
                    }
                ?>
        </select><br>
        <label for="number_of_seats">Number Of Seats</label><select name="number_of_seats" id="number_of_seats">
            <option value="" disable selected>--Choose the number of seats--</option>
                <?php 
                    $number_of_seats_list = $available_search_parameters["number_of_seats"];
                    foreach($number_of_seats_list as $num_of_seats){
                        echo '<option value="'.htmlspecialchars($num_of_seats).'">';
                        echo htmlspecialchars($num_of_seats);
                        echo '</option>';
                    }
                ?>
        </select><br>
        <input type="submit" name ="search" value="Search for Car">
    </form>
    <br><br>
    <label for="search_results">Search Results</label>
    <div id="search_results" name="search_results">

    </div>
    <script src="search_results_gen.js"></script>
</body>
</html>
