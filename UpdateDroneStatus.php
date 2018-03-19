<?php
require ('databasevaribles.php');

$drone_id = $_POST['drone_id'];
$drone_private_key = $_POST['drone_private_key'];
$current_battery_life = $_POST['current_battery_life'];
$current_stage_of_delivery = $_POST['current_stage_of_delivery'];
$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];
$altitude = $_POST['altitude'];
$speed = $_POST['speed'];

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

if (empty($longitude) || empty($latitude) || empty($altitude) || empty($speed)) {
    $response['success'] = FALSE;
    $response['debug'] = "A longitude, latitude, altitude and speed must be entered!";
    echo json_encode($response);
    die();
}
if (empty($current_stage_of_delivery)) {
    $response['success'] = FALSE;
    $response['debug'] = "A current stage of delivery must be entered!";
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
$current_battery_life = mysqli_real_escape_string($conn, $current_battery_life);
$current_stage_of_delivery = mysqli_real_escape_string($conn, $current_stage_of_delivery);
$longitude = mysqli_real_escape_string($conn, $longitude);
$latitude = mysqli_real_escape_string($conn, $latitude);
$altitude = mysqli_real_escape_string($conn, $altitude);
$speed = mysqli_real_escape_string($conn, $speed);

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

// Generate a date/time stamp
date_default_timezone_set('UTC');
$date = date("F j, Y, g:i:s a");

$update_query = "UPDATE drone_status
    SET current_battery_life = '$current_battery_life',
        current_stage_of_delivery = '$current_stage_of_delivery',
        longitude = '$longitude',
        latitude= '$latitude',
        altitude= '$altitude',
        speed='$speed',
        last_update='$date'
    WHERE drone_id='$drone_id'";
$result = mysqli_query($conn, $update_query);

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
}


$response['success'] = TRUE;
echo json_encode($response);
die();


?>
