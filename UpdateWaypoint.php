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

// Return a dummy response
$response['success'] = TRUE;
$response['waypoint'] = array('longitude' => 39.6295, 'latitude'=> 79.9559, 'altitude'=> 15);
echo json_encode($response);
die();


?>
