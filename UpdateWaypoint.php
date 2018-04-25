<?php
require ('databasevaribles.php');

$drone_id = $_POST['drone_id'];
$drone_private_key = $_POST['drone_private_key'];
$is_blocked = $_POST['is_blocked'];


if (empty($drone_id)) {
    $response['success'] = FALSE;
    $response['debug'] = "A drone ID must be entered!";
    echo json_encode($response);
    die();
}

if (empty($drone_private_key)) {
    $response['success'] = FALSE;
    $response['debug'] = "A drone private key must be entered!";
    echo json_encode($response);
    die();
}

if (empty($is_blocked)) {
    $response['success'] = FALSE;
    $response['debug'] = "A blocked status must be entered!";
    echo json_encode($response);
    die();
}

// Connect To MYSQL
$conn = mysqli_connect($dbhostname, $dbusername, $dbpassword);

// If we can't connect to the database exit.
if (!isset($conn)) {
    $response['success'] = FALSE;
    $reponse['debug'] = "Unable to connect to MYSQL";
    echo json_encode($response);
    die();
}

// Select Correct Database
if (!mysqli_select_db($conn,$dbname)) {
    $response['success'] = FALSE;
    $response['debug'] = "Unable to connect to database";
    echo json_encode($response);
    die();
}

// Escape the input passed in by the user
$drone_id = mysqli_real_escape_string($conn, $drone_id);
$drone_private_key = mysqli_real_escape_string($conn, $drone_private_key);
$is_blocked = mysqli_real_escape_string($conn, $is_blocked);

// Confirm that the private key is correct for the drone ID past
$result = mysqli_query($conn, "SELECT drone_id FROM drone_status WHERE drone_id = '$drone_id' AND drone_private_key = '$drone_private_key'");

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) == 0) {
    // User's token doesn't match
    $response['success'] = FALSE;
    $response['debug'] = "Private key doesn't match";
    echo json_encode($response);
    die();
}

// First check to make sure that the drone ID past is part of a current delivery
$CurrentDeliveryQuery = "SELECT * FROM deliveries INNER JOIN drone_status ON (deliveries.drone_id = drone_status.drone_id) WHERE NOT delivery_status = 'delivered'";
$result = mysqli_query($conn, $CurrentDeliveryQuery);

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) == 0) {
    // No current deliveries that the drone is part of
    $response['success'] = FALSE;
    $response['debug'] = "Drone is not part of current delivery";
    echo json_encode($response);
    die();
}
$row = mysqli_fetch_assoc($result);
$ReceiverId = $row["receiver_id"];

// Get the current package recivers position.
// TODO: At some point we will need to add code to know that we need to return the drone back to the reciever

$ReceiverIdQuery = "SELECT * FROM user_current_locations WHERE user_id ='$ReceiverId' ORDER BY time_stamp DESC LIMIT 1";
$ReceiverIdQueryResult = mysqli_query($conn, $ReceiverIdQuery);

if (!$ReceiverIdQueryResult) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($ReceiverIdQueryResult) == 0) {
    // No current deliveries that the drone is part of
    $response['success'] = FALSE;
    $response['debug'] = "No location data for the selected user";
    echo json_encode($response);
    die();
}
$row = mysqli_fetch_assoc($ReceiverIdQueryResult);

$response['success'] = TRUE;
$response['waypoint'] = array('longitude' => $row["longitude"], 'latitude'=> $row["latitude"], 'altitude'=> 0); // Altitude will be determine by the drone, this field is here so in the future can navigate the drone over things
echo json_encode($response);
die();


?>
