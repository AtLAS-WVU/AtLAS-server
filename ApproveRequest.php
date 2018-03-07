<?php
require ('databasevaribles.php');

$username = $_POST['username'];
$token = $_POST['token'];
$requestID = $_POST['requestID'];

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

if (empty($requestID)) {
    $response['success'] = FALSE;
    $response['debug'] = "A request ID must be entered!";
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
$requestID = mysqli_real_escape_string($conn, $requestID);

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


// Check to make sure that the user can approve the request
$FriendCheckQuery = "SELECT * FROM requests WHERE requestID = '$requestID' AND revceiverID = '$userID'";
$result = mysqli_query($conn, $FriendCheckQuery);
if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) == 0) {
    // User's token doesn't match
    $response['success'] = FALSE;
    $response['debug'] = "You have no requests to approve with the given request ID";
    echo json_encode($response);
    die();
}
$RequestRow = mysqli_fetch_assoc($result);

// Move the request to the deliveries table

// Generate a delivery ID
// Generate Unique ID for this request
// Generate a UserID For The User.
$DeliveryID ="";
$numrows =1;
while ($numrows != 0) {
    // Keep Generating a Random Number Until We Can Find One That Hasnt Been Used Yet.
    $DeliveryID ="";
    for ($i = 0; $i<8; $i++) {
        $DeliveryID  .= mt_rand(0,9);
    }

    // Select Any Data From The Match Database To See If We Have Already Used The Match ID.
    $result = mysqli_query($conn,"SELECT delivery_id FROM deliveries WHERE delivery_id = '$DeliveryID'");
    if (!$result) {
        $response['success'] = FALSE;
        $response['debug'] = "Delivery ID generation failed. ".mysqli_error($conn);
        echo json_encode($response);
        die();
    }
    $numrows = mysqli_num_rows($result);
}

// Get the data we need from the request
$senderID = $RequestRow['senderID'];
$receiverID = $RequestRow['revceiverID'];
$message = $RequestRow['packageMessage'];

// Generate a date/time stamp
date_default_timezone_set('UTC');
$date = date("F j, Y, g:i:s a");

// Inser the data into the deliveries database
$MoveQuery = "INSERT INTO deliveries (delivery_id, drone_id, sender_id, receiver_id, package_message, delivery_status, time_stamp) VALUES ('$DeliveryID', '1234', '$senderID', '$receiverID', '$message', 'holding for takeoff', '$date')";
$result = mysqli_query($conn, $MoveQuery);

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
}

// Delete it from the request from the database
$DeleteQuery = "DELETE FROM requests where requestID='$requestID'";
$result = mysqli_query($conn, $DeleteQuery);
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
