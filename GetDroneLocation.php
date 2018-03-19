<?php
// This endpoint gets a location of the a drone that is assigned to a current delivery

require ('databasevaribles.php');

$username = $_POST['username'];
$token = $_POST['token'];
$delivery_id = $_POST['delivery_id'];

if (empty($username)) {
    $response['success'] = FALSE;
    $response['debug'] = "A username must be entered!";
    echo json_encode($response);
    die();
}

if (empty($token)) {
    $response['success'] = FALSE;
    $response['debug'] = "A token must be entered!";
    echo json_encode($response);
    die();
}

if (empty($delivery_id)) {
    $response['success'] = FALSE;
    $response['debug'] = "A delivery ID must be entered!";
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
$delivery_id = mysqli_real_escape_string($conn, $delivery_id);
$token = mysqli_real_escape_string($conn, $token);

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

// Select the delivery and get the current location of the drone
$GetLocationQuery = "SELECT * FROM deliveries INNER JOIN drone_status ON (deliveries.drone_id = drone_status.drone_id) WHERE delivery_id = '$delivery_id'";
$LocationQueryResult = mysqli_query($conn, $GetLocationQuery);

if (!$LocationQueryResult) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($LocationQueryResult) == 0) {
    // Username not found in the DB
    $response['success'] = TRUE;
    $response['connections'] = array();
    $response['debug'] = "No deliveries found";
    echo json_encode($response);
    die();
} else {
    $data_array = array();
    while($row = mysqli_fetch_assoc($LocationQueryResult)) {
        $data_array[]  = array(
            'current_battery_life' => $row['current_battery_life'],
            'delivery_status' => $row['delivery_status'],
            'latitude' => $row['latitude'],
            'longitude' => $row['altitude'],
            'altitude' => $row['altitude'],
            'speed' => $row['speed'],
            'last_updated' => $row['last_update']
        );
    }
    $response['success'] = TRUE;
    $response['delivery_data'] = $data_array;
    echo json_encode($response);
    die();
}

?>