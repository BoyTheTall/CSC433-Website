
/**
 * Retrieves the value of a specific cookie key.
 */
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

/**
 * Handles the logout process: deletes cookies and reloads the page.
 */
function handleLogout(event) {
    event.preventDefault();
    document.cookie = 'user_id=; Max-Age=-99999999; path=/'; 
    document.cookie = 'username=; Max-Age=-99999999; path=/'; 
    
    // Clear persisted search data
    localStorage.removeItem('last_search_results'); 
    
    window.location.reload(); 
}

// ==========================================================
// CORE APPLICATION LOGIC (MODIFIED FOR PERSISTENCE)
// ==========================================================

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
    // ... (Your displayCarDetails function content remains here, it looks correct)
    const tow_capacity = car_details.model?.tow_capacity ?? 'N/A'; // Safer Null-check
    
    const carDeatilsContainer=document.getElementById("detailed_car_view");
    carDeatilsContainer.innerHTML="";
    
    // NOTE: HTML is omitted for brevity, but your original structure is used...

    const car_details_card = `<div id="car_details_modal" class="modal">
    <div class="modal-content">
        
        <span class="close-button">&times;</span>
        
        <header class="modal-header">
            <h2 id="modal_title">
                ${car_details.manufacturer.manufacturer_name} ${car_details.model.model_name}
            </h2>
            <p class="model-year" id="modal_year">${car_details.model.year}</p>
        </header>

        <section class="modal-body">
            
            <div class="modal-image-container">
                <img id="modal_image" 
                     src="${car_details.model.image_url}" 
                     alt="Car Image"
                     class="car-detail-image"
                     width="200" height="200">
            </div>

            <div class="modal-specs">
                <h3>Key Specifications</h3>
                
                <ul>
                    <li>
                        <strong>Type:</strong> 
                        <span id="modal_type">${car_details.car_type.type_name}</span>
                    </li>
                    <li>
                        <strong>Seats:</strong> 
                        <span id="modal_seats">${car_details.model.num_of_seats}</span>
                    </li>
                    <li>
                        <strong>Colour:</strong> 
                        <span id="modal_colour">${car_details.colour}</span>
                    </li>
                    <li>
                        <strong>Tow Capacity:</strong> 
                        <span id="modal_tow_capacity">${tow_capacity}</span> kg
                    </li>
                </ul>

                <hr>

                <div class="modal-pricing">
                    <p class="rate-large">
                        **Rental Rate:** <span id="modal_rate">E${car_details.rental_rate}</span> / day
                    </p>
                </div>
                
                <form id="booking_form">
                    <input type="hidden" id="booking_car_id" value="${car_details.number_plate}">
                    
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

    // 1. Get the necessary DOM elements
    const startDateInput = document.getElementById('rental_start');
    const endDateInput = document.getElementById('rental_end');
    const totalDisplay = document.getElementById('total_cost_display');

    // 2. Get the daily rate (convert it to a float for calculation)
    const dailyRate = parseFloat(car_details.rental_rate); 

    // 3. Define the event handler
    const updateCost = () => {
        const start = startDateInput.value;
        const end = endDateInput.value;

        if (start && end) {
            // Run the core calculation
            const total = calculateTotalCost(dailyRate, start, end);
            
            // Update the display element
            totalDisplay.textContent = `Total Cost: E${total}`;
        } else {
            totalDisplay.textContent = `Total Cost: E0.00`;
        }
    };

    // 4. Attach listeners to calculate cost whenever a date changes
    startDateInput.addEventListener('change', updateCost);
    endDateInput.addEventListener('change', updateCost);

    // Optional: Run it once immediately if dates are pre-filled
    // updateCost(); 
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
    }).then(data => {
        CURRENT_CAR_LIST=data;
        // 🟢 NEW: Save results to Local Storage before display
        try {
            localStorage.setItem('last_search_results', JSON.stringify(data));
        } catch (e) {
            console.error("Local Storage save failed:", e);
        }
        displayResults(data); 
        console.log("Global List Populated. Total cars:", CURRENT_CAR_LIST.length);
    }).catch(error =>{

        console.error("results fetching failed:", error);

        document.getElementById("search_results").innerHTML=`<p style="color:red;">Error loading results. Please try again.</p>`;
    });
    
});


document.addEventListener('DOMContentLoaded', function() {
    
    const storedResults = localStorage.getItem('last_search_results');
    
    if (storedResults) {
        try {
            const carList = JSON.parse(storedResults);
            CURRENT_CAR_LIST = carList; 
            displayResults(carList);
            console.log("Restored previous search results from Local Storage.");
        } catch (e) {
            console.error("Error parsing stored JSON:", e);
            localStorage.removeItem('last_search_results'); 
        }
    }
    
    
    const userId = getCookie('user_id'); 
    const username = getCookie('username'); 

    const authControls = document.getElementById('auth_controls');
    if (authControls) {
        if (userId) {
            // Logged In State (Now includes Update Details link)
            authControls.innerHTML = `
                <span class="welcome-msg">Welcome, <b>${username || `User ${userId}`}</b>!</span>
                <a href="frontend/update_customer.php" class="nav-link">Update Details</a>
                <a href="#" id="nav_logout_link" class="nav-link">Log Out</a>
            `;
            // Attach the logout listener to the newly created link
            document.getElementById('nav_logout_link')?.addEventListener('click', handleLogout);
        } else {
            // Logged Out State (Default)
            authControls.innerHTML = `
                <a href="frontend/login.html" class="nav-link">Log In</a>
                <a href="frontend/register_customer.html" class="nav-link">Create Account</a>
            `;
        }
    }

    // ==========================================================
    // EXISTING: VIEW DETAILS LISTENER
    // ==========================================================
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

/**
 * Calculates the total cost based on the daily rate and rental dates.
 * It ensures the rental duration is at least 1 day (inclusive of start/end dates).
 * * @param {number} dailyRate - The car's daily rental rate (e.g., 72.53).
 * @param {string} startDateStr - The ISO string for the start date (YYYY-MM-DD).
 * @param {string} endDateStr - The ISO string for the end date (YYYY-MM-DD).
 * @returns {string} The total calculated cost, formatted to two decimal places.
 */
function calculateTotalCost(dailyRate, startDateStr, endDateStr) {
    // Standard time constant: 1 day in milliseconds
    const ONE_DAY_MS = 1000 * 60 * 60 * 24;

    // 1. Convert YYYY-MM-DD strings to Date objects
    // Using UTC date conversion prevents timezone issues that cause 1-day shifts
    const startDate = new Date(startDateStr + 'T00:00:00Z');
    const endDate = new Date(endDateStr + 'T00:00:00Z');

    // 2. Input Validation
    // If dates are invalid or end date is before start date, return 0.
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime()) || endDate < startDate) {
        return "0.00"; 
    }

    // 3. Calculate the difference in milliseconds
    const timeDifference = endDate.getTime() - startDate.getTime();

    // 4. Convert milliseconds to days and add 1 (to make it inclusive)
    // Example: Jan 1st to Jan 1st = 0ms difference. (0 / ONE_DAY_MS) + 1 = 1 day.
    const numberOfDays = Math.round(timeDifference / ONE_DAY_MS) + 1; 

    // 5. Calculate total cost and format
    const totalCost = numberOfDays * dailyRate;
    
    return totalCost.toFixed(2);
}

document.addEventListener('submit', function(event) {
    if (event.target && event.target.id === 'booking_form') {
        event.preventDefault(); // 1. Stop the browser's default action (reload)

        const userId = getCookie('user_id'); 
        
        if (!userId) {
            alert("You must be logged in to complete a booking.");
            return; 
        }

        // --- 2. GATHER DATA ---
        const form = event.target;
        const carId = form.querySelector('#booking_car_id').value;
        const startDate = form.querySelector('#rental_start').value;
        const endDate = form.querySelector('#rental_end').value;
        
        // You need the dailyRate again to calculate the final cost to send to the server
        const carDetails = CURRENT_CAR_LIST.find(car => 
            String(car.number_plate).trim() === String(carId).trim()
        );

        if (!carDetails) {
            alert("Error: Car details not found for booking verification.");
            return;
        }
        
        const dailyRate = parseFloat(carDetails.rental_rate);
        const totalCost = calculateTotalCost(dailyRate, startDate, endDate); // Use your existing function

        // --- 3. PREPARE PAYLOAD ---
        const bookingData = {
            car_id: carId,
            user_id: userId,
            start_date: startDate,
            end_date: endDate,
            total_cost: totalCost,
            daily_rate: carDetails.rental_rate,
            VIN: carDetails.VIN 
        };

        // --- 4. SEND FETCH REQUEST ---
        fetch('./backend/book_car.php', {
            method: 'POST', // Critical: Must be POST
            headers: {
                // This tells the server the data format is JSON
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify(bookingData) // Convert JavaScript object to JSON string
        })
        .then(response => {
            // Check if the response was successful (200-299 status code)
            if (!response.ok) {
                throw new Error('Network response not ok');
            }
            return response.json(); // Parse the JSON response from PHP
        })
        .then(result => {
            // 5. HANDLE SUCCESS
            if (result.status === 'success') {
                alert("Booking successful! Confirmation: " + result.booking_id);
                // Hide the modal or provide confirmation
                document.getElementById('car_details_modal').style.display = 'none';
            } else {
                // 6. HANDLE SERVER-SIDE FAILURE
                alert("Booking failed: " + result.message);
            }
        })
        .catch(error => {
            console.error('Submission Error:', error);
            alert("An unknown error occurred during booking. Check console.");
        });
    }
});