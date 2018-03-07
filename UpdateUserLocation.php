<?php
require ('databasevaribles.php');

$username = $_POST['username'];
$token = $_POST['token'];
$longitude = $_POST['longitude'];
$latitdue = $_POST['latitdue'];

if (empty($username)) {
    $response['success'] = FALSE;
    $response['debug'] = "A username must be entered!";
    echo json_encode($response);
    die();
}

if (empty($token)) {
    $response['success'] = FALSE;
    $response['debug'] = "An token must be entered!";
    echo json_encode($response);
    die();
}

if (empty($longitude) || empty($latitdue)) {
    $response['success'] = FALSE;
    $response['debug'] = "An longitude and latitude must be entered!";
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
$username = mysqli_real_escape_string($conn, $username);
$token = mysqli_real_escape_string($conn, $token);
$longitude = mysqli_real_escape_string($conn, $longitude);
$latitdue = mysqli_real_escape_string($conn, $latitdue);

// Confirm that the token requested is correct
$result = mysqli_query($conn, "SELECT userid FROM users WHERE username = '$username' AND private_key = '$token'");

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) == 0) {
    // User's token doesn't match
    $response['success'] = FALSE;
    $response['debug'] = "User token doesn't match";
    echo json_encode($response);
    die();
}

// Get the user's ID
$row = mysqli_fetch_assoc($result);
$userID = $row['userid'];

// Generate a date/time stamp
$friend_status = 'pending';
date_default_timezone_set('UTC');
$date = date("F j, Y, g:i:s a");

// Add a new entry to the database with the current location of the user
$add_query = "INSERT INTO user_current_locations (user_id, longitude, latitude, time_stamp) VALUES ('$userID', '$longitude', '$latitdue', '$date')";
$result = mysqli_query($conn, $add_query);

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
