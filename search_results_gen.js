let CURRENT_CAR_LIST=[];

function displayResults(carList){
    const results_container = document.getElementById('search_results');
    results_container.innerHTML = '';
    
    if(carList.length == 0){
        results_container.innerHTML = "<p>sorry we could not find a list of cars matching that criteria :(</p>";
        return;
    }

    carList.forEach(car => {
      const cardHTML =  `<div class="car_card" data_car_id="${car.number_plate}">
            <div class="car_header">
                <h3>${car.manufacturer.manufacturer_name} ${car.model.model_name}</h3>
            </div>
            <div class="car_body">
                <img src="${car.model.image_url}" alt="obama" width="300" height="300">
                <p>Car Type: ${car.car_type.type_name}</p>
                <p class="price-text">E${car.rental_rate}/day</p>
                <button class="view_details_btn" data_car_id="${car.number_plate}">View Details</button>
            </div>
        </div>`;
        results_container.insertAdjacentHTML('beforeend', cardHTML)
    });
}
function displayCarDetails(car_details){
    const carDeatilsContainer=document.getElementById("detailed_car_view");
    carDeatilsContainer.innerHTML="";
    const car_details_card = `<div id="car_details_modal" class="modal">
    <div class="modal-content">
        
        <span class="close-button">&times;</span>
        
        <header class="modal-header">
            <h2 id="modal_title">
                [DETAILS.MANUFACTURER_NAME] [DETAILS.MODEL_NAME]
            </h2>
            <p class="model-year" id="modal_year">[DETAILS.YEAR]</p>
        </header>

        <section class="modal-body">
            
            <div class="modal-image-container">
                <img id="modal_image" 
                     src="media/obama.png" 
                     alt="Car Image"
                     class="car-detail-image"
                     width="200" height="200">
            </div>

            <div class="modal-specs">
                <h3>Key Specifications</h3>
                
                <ul>
                    <li>
                        <strong>Type:</strong> 
                        <span id="modal_type">[DETAILS.CAR_TYPE_NAME]</span>
                    </li>
                    <li>
                        <strong>Seats:</strong> 
                        <span id="modal_seats">[DETAILS.NUM_SEATS]</span>
                    </li>
                    <li>
                        <strong>Colour:</strong> 
                        <span id="modal_colour">[DETAILS.COLOUR]</span>
                    </li>
                    <li>
                        <strong>Tow Capacity:</strong> 
                        <span id="modal_tow_capacity">[DETAILS.TOW_CAPACITY_KG]</span> kg
                    </li>
                </ul>

                <hr>

                <div class="modal-pricing">
                    <p class="rate-large">
                        **Rental Rate:** <span id="modal_rate">E[DETAILS.DAILY_RATE]</span> / day
                    </p>
                </div>
                
                <form id="booking_form">
                    <input type="hidden" id="booking_car_id" value="[DETAILS.NUMBER_PLATE]">
                    
                    <label for="rental_start">Start Date:</label>
                    <input type="date" id="rental_start" required>
                    
                    <label for="rental_end">End Date:</label>
                    <input type="date" id="rental_end" required>
                    
                    <button type="submit" id="book_now_button">Book Now</button>
                    <p id="total_cost_display" class="total-cost">Total Cost: E0.00</p>
                </form>

            </div>
        </section>
        
    </div>
        </div>`;

    carDeatilsContainer.insertAdjacentHTML('beforeend', car_details_card);
}

document.getElementById('vehicle_search').addEventListener('submit', function(event){
    //stopping the full page relaod
    event.preventDefault();

    //getting form data
    const form = event.target;
    const formData = new URLSearchParams(new FormData(form)).toString();

    //making AJAX request. me hate this :/
    fetch('./backend/search_cars.php?' + formData, {method:'GET'}).then(response => {
        if(!response.ok){
            throw new Error('Network response not okay');
        }
        return response.json();
    }).then(data => {displayResults(data); CURRENT_CAR_LIST=data;
        console.log("Global List Populated. Total cars:", CURRENT_CAR_LIST.length);}).catch(error =>{

        console.error("results fetching failed:", error);

        document.getElementById("search_results").innerHTML=`<p style="color:red;">Error loading results. Please try again.</p>`;
    });
    
});


document.addEventListener('DOMContentLoaded', function() {
    // 1. Attach the listener to the parent container
    const resultsContainer = document.getElementById('search_results');
    
    resultsContainer.addEventListener('click', function(event) {
        // 2. Check if the element clicked has the specific class/role
        const targetButton = event.target.closest('.view_details_btn');
        
        if (targetButton) {
            // 3. Prevent the default button behavior (if any)
            event.preventDefault(); 
            
            // 4. Get the ID from the button's data attribute
            const carId = targetButton.getAttribute('data_car_id');
            
            // 5. Trigger the next action!
            console.log('User clicked to view details for Car ID:', carId);
            
            // This is the function we discussed earlier:
            displayCarDetails(getCarDetails(carId)); 
            
            //details
           
        }
    });
});

//we need to edit this
function getCarDetails(carId) {
    const car_details = CURRENT_CAR_LIST.find(car => String(car.number_plate).trim()==String(carId).trim());
    if (!car_details) {
        console.warn(`Attempted to find car ID ${carId}, but it was not found in the current list.`);
    }
    
    return car_details;
}