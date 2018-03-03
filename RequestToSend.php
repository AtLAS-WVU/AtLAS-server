<?php
require ('databasevaribles.php');

$username = $_POST['username'];
$friends_username = $_POST['friend_username'];
$token = $_POST['token'];
$message = $_POST['delivery_message'];

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

if (empty($message)) {
    $response['success'] = FALSE;
    $response['debug'] = "A delivery message must be entered!";
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
$message = mysqli_real_escape_string($conn, $message);

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

// Check to make sure that these two people are friends
$FriendCheckQuery = "SELECT friendship_status FROM friend_connection WHERE (user_1 = '$userID' AND user_2 = '$FriendsUserID') OR (user_1 = '$FriendsUserID' AND user_2 = '$UserID')";
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
    $response['debug'] = "You are not friends with the person you want to deliver the message to";
    echo json_encode($response);
    die();
}

$row = mysqli_fetch_assoc($result);
if($row['friendship_status'] == 'pending') {
    // Friendship is pending
    $response['success'] = FALSE;
    $response['debug'] = "Friendship status pending with person you are trying to send package too";
    echo json_encode($response);
    die();
}

// Check to make sure there are not other open requests to this friend
$PckageCheckQuery = "SELECT * FROM requests WHERE senderID = '$username' AND revceiverID = '$friends_username'";
$result = mysqli_query($conn, $PckageCheckQuery);
if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) != 0) {
    // User's token doesn't match
    $response['success'] = FALSE;
    $response['debug'] = "You already have open requests to this person";
    echo json_encode($response);
    die();
}

// Insert the request into the DB

// Generate Unique ID for this request
// Generate a UserID For The User.
$RequestID ="";
$numrows =1;
while ($numrows != 0) {
    // Keep Generating a Random Number Until We Can Find One That Hasnt Been Used Yet.
    $RequestID ="";
    for ($i = 0; $i<8; $i++) {
        $RequestID  .= mt_rand(0,9);
    }

    // Select Any Data From The Match Database To See If We Have Already Used The Match ID.
    $result = mysqli_query($conn,"SELECT requestID FROM requests WHERE requestID = '$RequestID '");
    if (!$result) {
        $response['success'] = FALSE;
        $response['uniqueUser'] = TRUE;
        $response['uniqueEmail'] = TRUE;
        $response['debug'] = "$RequestID generation failed. ".mysqli_error($conn);
        echo json_encode($response);
        die();
    }
    $numrows = mysqli_num_rows($result);
}



// Generate a date/time stamp
date_default_timezone_set('UTC');
$date = date("F j, Y, g:i a");

$add_query = "INSERT INTO requests (requestID, senderID, revceiverID, packageMessage, requestTime) VALUES ('$RequestID', '$UserID', '$FriendsUserID', '$message', '$date')";
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
