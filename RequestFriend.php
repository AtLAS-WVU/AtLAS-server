<?php
require ('databasevaribles.php');

$username = $_POST['username'];
$friends_username = $_POST['friend_username'];
$token = $_POST['token'];

if (empty($username)) {
    $response['success'] = FALSE;
    $response['debug'] = "A username must be entered!";
    echo json_encode($response);
    die();
}

if (empty($friends_username)) {
    $response['success'] = FALSE;
    $response['debug'] = "A friends username must be entered!";
    echo json_encode($response);
    die();
}

if (empty($token)) {
    $response['success'] = FALSE;
    $response['debug'] = "An token must be entered!";
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
$friends_username = mysqli_real_escape_string($conn, $friends_username);
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

// Get the friend's userID
$result = mysqli_query($conn, "SELECT userid FROM users WHERE username = '$friends_username'");

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) == 0) {
    // User's token doesn't match
    $response['success'] = FALSE;
    $response['debug'] = "Unable to find friend's ID";
    echo json_encode($response);
    die();
}

$row = mysqli_fetch_assoc($result);
$FriendsUserID = $row['userid'];

// Check to make sure that this connection is not already logged.
$checkQuery = "SELECT * FROM friend_connection WHERE user_1='$userID' AND user_2='$FriendsUserID'";
$result = mysqli_query($conn, $checkQuery);

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) > 0) {
    $response['success'] = FALSE;
    $response['debug'] = "Friend has already been sent a request";
    echo json_encode($response);
    die();
}

// Generate a date/time stamp
$friend_status = 'pending';
date_default_timezone_set('UTC');
$date = date("F j, Y, g:i a");

// Add the pending request to the DB. The requster's user ID will be placed in user_1 field
$add_query = "INSERT INTO friend_connection (user_1, user_2, friendship_status, friendship_date) VALUES ('$userID', '$FriendsUserID', '$friend_status', '$date')";
$result = mysqli_query($conn, $add_query);

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_affected_rows($conn) == 0) {
    // No rows were updated. This is a catch all for a lot of errors where we dont want to update the table.
    $response['success'] = FALSE;
    $response['debug'] = "No Rows Affected";
    echo json_encode($response);
    die();
}

$response['success'] = TRUE;
echo json_encode($response);
die();

?>
