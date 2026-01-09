<?php 
    require_once __DIR__."/backend/config.php";
    require_once __DIR__."/backend/classes.php";
    require_once __DIR__."/backend/database_operation.php";

    // NEW HELPER FUNCTION TO PRE-SELECT DROPDOWNS BASED ON URL PARAMETERS ($_GET)
    function is_selected($param_name, $option_value) {
        // We cast to string for safe comparison, as $_GET values are always strings.
        if (isset($_GET[$param_name]) && (string)$_GET[$param_name] === (string)$option_value) {
            return 'selected="selected"';
        }
        return '';
    }

    $db_conn = DBOperations::getInstance();
    $available_search_parameters = $db_conn->getSearchParameters();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=<device-width>, initial-scale=1.0">
    <title>Vehicle Search & Rental</title>
    <link rel="stylesheet" href="search_page_style.css"> 
</head>
<body>
    <nav id="main_navigation">
        <a href="vehicle_search_page.php" class="nav-title">🚗 Car Rental App</a>
        
        <div id="auth_controls">
            <a href="frontend/login.html" class="nav-link">Log In</a>
            <a href="frontend/register_customer.html" class="nav-link">Create Account</a>
            <a href="frontend/update_customer.php" class="nav-link">Update Details</a>
        </div>
    </nav>
    
    <main class="container">
        <section id="search_panel">
            <h2>Find Your Next Ride</h2>
            <form id="vehicle_search" action="./backend/search_cars.php" method="get" class="search-form">
                
                <label for="manufacturer">Manufacturer</label>
                <select name="manufacturer" id="manufacturer">
                    <option value="" disable selected>--Choose a manufacturer--</option>
                    <?php 
                        $manufacturers = $available_search_parameters["manufacturers"];
                        foreach($manufacturers as $manufacturer){
                            $id = htmlspecialchars($manufacturer->getManufacturerId());
                            //Integration: is_selected('manufacturer', $id)
                            echo '<option value="'.$id.'" '.is_selected('manufacturer', $id).'>';
                            echo htmlspecialchars($manufacturer->getName());
                            echo '</option>';
                        }
                    ?>
                </select><br>
                
                <label for="model">Model</label>
                <select name="model" id="model">
                    <option value="" disable selected>--Choose a car model--</option>
                    <?php 
                        $car_models = $available_search_parameters["car_models"];
                        foreach($car_models as $car_model){
                            $id = htmlspecialchars($car_model->getModelId());
                            // Integration: is_selected('model', $id)
                            echo '<option value="'.$id.'" '.is_selected('model', $id).'>';
                            echo htmlspecialchars($car_model->getName().' ('.$car_model->getYear().')');
                            echo '</option>';
                        }
                    ?>
                </select><br>
                
                <label for="vehicle_type">Vehicle Type</label>
                <select name="vehicle_type" id="vehicle_type">
                    <option value="" disable selected>--Choose a vehicle type--</option>
                    <?php 
                        $vehicle_types = $available_search_parameters["vehicle_types"];
                        foreach($vehicle_types as $vehicle_type){
                            $id = htmlspecialchars($vehicle_type->getTypeId());
                            // Integration: is_selected('vehicle_type', $id)
                            echo '<option value="'.$id.'" '.is_selected('vehicle_type', $id).'>';
                            echo htmlspecialchars($vehicle_type->getTypeName());
                            echo '</option>';
                        }
                    ?>
                </select><br>
                
                <label for="vehicle_colour">Vehicle Colour</label>
                <select name="vehicle_colour" id="vehicle_colour">
                    <option value="" disable selected>--Choose a vehicle colour--</option>
                    <?php 
                        $vehicle_colours = $available_search_parameters["colours"];
                        foreach($vehicle_colours as $colour){
                            $safe_colour = htmlspecialchars($colour);
                            // Integration: is_selected('vehicle_colour', $safe_colour)
                            echo '<option value="'.$safe_colour.'" '.is_selected('vehicle_colour', $safe_colour).'>';
                            echo $safe_colour;
                            echo '</option>';
                        }
                    ?>
                </select><br>
                
                <label for="vehicle_year">Vehicle Year</label>
                <select name="vehicle_year" id="vehicle_year">
                    <option value="" disable selected>--Choose a vehicle year--</option>
                    <?php 
                        $vehicle_years = $available_search_parameters["years"];
                        foreach($vehicle_years as $year){
                            $safe_year = htmlspecialchars($year);
                            // Integration: is_selected('vehicle_year', $safe_year)
                            echo '<option value="'.$safe_year.'" '.is_selected('vehicle_year', $safe_year).'>';
                            echo $safe_year;
                            echo '</option>';
                        }
                    ?>
                </select><br>
                
                <label for="number_of_seats">Number Of Seats</label>
                <select name="number_of_seats" id="number_of_seats">
                    <option value="" disable selected>--Choose the number of seats--</option>
                    <?php 
                        $number_of_seats_list = $available_search_parameters["number_of_seats"];
                        foreach($number_of_seats_list as $num_of_seats){
                            $safe_seats = htmlspecialchars($num_of_seats);
                            //Integration: is_selected('number_of_seats', $safe_seats)
                            echo '<option value="'.$safe_seats.'\" '.is_selected('number_of_seats', $safe_seats).'>';
                            echo $safe_seats;
                            echo '</option>';
                        }
                    ?>
                </select><br>
                
                <input type="submit" name ="search" value="Search for Car" class="btn btn-primary">
            </form>
        </section>

        <section id="results_panel">
            <h3>Search Results</h3>
            <div id="search_results" name="search_results" class="car-results-grid">
                <p>Start your search above!</p>
            </div>
            
            <div id="detailed_car_view" name = "detailed_car_view">
            </div>
        </section>
    </main>

    <script src="search_results_gen.js"></script>
</body>
</html>