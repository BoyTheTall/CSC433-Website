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
    }).then(data => {displayResults(data)}).catch(error =>{

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
            fetchCarDetails(carId); 
            
            // Optionally, you might visually highlight the selected card here.
        }
    });
});

//we need to edit this
function fetchCarDetails(carId) {
    // 3. Make the AJAX Request (GET method again for retrieval)
    fetch(`api/get_car_details.php?id=${carId}`, {
        method: 'GET' 
    })
    .then(response => response.json())
    .then(details => {
        // 4. Update the Details Section
        const detailsSection = document.getElementById('details-section');
        
        detailsSection.innerHTML = `
            <h2>${details.make} ${details.model}</h2>
            <p>Full Description: ${details.description}</p>
            <form id="booking-form">...</form>
        `;

        // Show the details section (assuming it was hidden)
        detailsSection.style.display = 'block'; 
        // Hide the search/results section if needed
        document.getElementById('results-container').style.display = 'none';
    })
    .catch(error => console.error('Error fetching details:', error));
}