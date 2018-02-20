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
    $response['debug'] = "Unable to find friend's username";
    echo json_encode($response);
    die();
}

$row = mysqli_fetch_assoc($result);
$FriendsUserID = $row['userid'];

// Delete the record to remove the recording from pending

// The only user that confirm this firendship is the one listed in user_2 field.
// The user listed in the user_1 field is the user to sent (requested) the friend connection.
// Since the confirming user will be performing the request, their ID will be listed in the userID field.
// Then the userID field needs to be checked againsted the user_2 field.
$result = mysqli_query($conn, "DELETE FROM friend_connection WHERE user_1 = '$FriendsUserID' AND user_2 = '$userID'");

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
