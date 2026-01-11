<?php
require_once "config.php";
$db_conn = DBOperations::getInstance();
// Set headers to indicate the response content type
header('Content-Type: application/json');

// 1. Read the raw JSON data from the request body
$json_data = file_get_contents('php://input');

// 2. Decode the JSON string into a PHP associative array
$data = json_decode($json_data, true);

// Check if decoding was successful and data exists
if ($data === null || !isset($data['car_id'])) {
    // Send back a failure message
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing data.']);
    exit;
}

// --- 3. Extract variables for processing ---
$car_id = $data['car_id'];           // The plate number
$user_id = $data['user_id'];         // User ID from cookie
$start_date = $data['start_date'];   // YYYY-MM-DD
$end_date = $data['end_date'];       // YYYY-MM-DD
$total_cost = $data['total_cost'];   // Cost calculated by JS
$daily_rate = $data['daily_rate'];
$car_vin = $data['VIN'];


//this is the rental function
$results = $db_conn->rent_car($user_id, $VIN, $start_date, $end_date, $daily_rate, $total_cost);


// --- 6. Send the Response Back to JavaScript ---
// Assuming the database save was successful:
$response = [
    'status' => 'success',
    'message' => 'Car booked successfully.',
    'booking_id' => $results["rental_id"] 
    ];
http_response_code(200); // OK
echo json_encode($response);
exit;
?>