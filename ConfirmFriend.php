<?php
require ('databasevaribles.php');
// This Script Will Create a Traditional User Account.

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
    $response['debug'] = "Unable to find friend's username";
    echo json_encode($response);
    die();
}

$row = mysqli_fetch_assoc($result);
$FriendsUserID = $row['userid'];

// Update the record to confirm the friendship
$friend_status = 'friend';
date_default_timezone_set('UTC');
$date = date("F j, Y, g:i a");

// The only user that can confirm the friendship is the user listed in the user_1 column.
// The user in the user_2 column is the one that sent the request.
$result = mysqli_query($conn, "UPDATE friend_connection SET friendship_status='$friend_status', friendship_date='$date' WHERE user_1 = '$userID' AND user_2 = '$FriendsUserID' AND NOT friendship_status = '$friend_status'");

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
