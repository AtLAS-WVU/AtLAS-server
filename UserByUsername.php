<?php
require ('databasevaribles.php');

$username = $_POST['username'];
$friends_username = $_POST['friend_username'];
$token = $_POST['token'];

if (empty($username)) {
    $response['success'] = FALSE;
    $response['debug'] = "Username is not set";
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

// Try Connect To MYSQL
$conn = mysqli_connect($dbhostname, $dbusername, $dbpassword);

// If we can't connect to the database exit.
if (!isset($conn)) {
    $response['success'] = FALSE;
    $reponse['debug'] = "Unable to connect to MYSQL";
    echo json_encode($response);
    die();
}

if (!mysqli_select_db($conn,$dbname)) {
    $response['success'] = FALSE;
    $response['debug'] = "Unable to connect to database";
    echo json_encode($response);
    die();
}

// Escape the strings that are being passed to make sure they are clean
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
$row = mysqli_fetch_assoc($result);
$userID = $row['userid'];

// Get the friend's userID
$result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$friends_username'");

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
} else {
    $row = mysqli_fetch_assoc($result);
    $FriendsUserID = $row['userid'];

    // Username found. Return the user inforamtion

    // Get the user's friendship status with the other user
    $FriendCheckResult = mysqli_query($conn, "SELECT friendship_status, friendship_date FROM friend_connection WHERE (user_1 = '$userID' AND user_2 = '$FriendsUserID') OR (user_1 = '$FriendsUserID' AND user_2 = '$userID')");
    $FriendCheckRow = mysqli_fetch_assoc($FriendCheckResult);

    if (!$FriendCheckResult) {
        // MYSQL Error
        $response['success'] = FALSE;
        $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
        echo json_encode($response);
        die();
    }

    $response['success'] = TRUE;
    $response['first_name'] = $row['first_name'];
    $response['last_name'] = $row['last_name'];

    if (is_null($FriendCheckRow['friendship_status'])){
        $response['friend_status'] = "stranger";
    } else {
        $response['friend_status'] = $FriendCheckRow['friendship_status'];
    }
    $response['friend_date'] = $FriendCheckRow['friendship_date'];
}
echo json_encode($response);
?>
