<?php
require ('databasevaribles.php');

$username = $_GET['username'];
$token = $_GET['token'];

// Check to make sure that both the username and token is set
if (empty($username) || empty($token)) {
    $response['success'] = FALSE;
    $response['debug'] = "Username or token is not set";
    echo json_encode($response);
    die();
}

// Try Connect To MYSQL
$conn = mysqli_connect($dbhostname, $dbusername, $dbpassword);

// If we can't connect to MYSQL exit
if (!isset($conn)) {
    $response['success'] = FALSE;
    $reponse['debug'] = "Unable to connect to MYSQL";
    echo json_encode($response);
    die();
}

// If we can't connect to the the database exit
if (!mysqli_select_db($conn,$dbname)) {
    $response['success'] = FALSE;
    $response['debug'] = "Unable to connect to database";
    echo json_encode($response);
    die();
}

// Escape the strings that are being passed to make sure they are clean
$username = mysqli_real_escape_string($conn, $username);
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

//Get the user's ID
$row = mysqli_fetch_assoc($result);
$userID = $row['userid'];

// Query to get the pending friends from the DB
// The user who reeusts the firendship will be stored in the user_1 therefore,
// the pending friends are the ones where the pasted username is stored in user_2 field
$query = "SELECT username, first_name, last_name FROM friend_connection INNER JOIN users ON friend_connection.user_1 = users.userid WHERE friend_connection.user_2 = '$userID' AND friend_connection.friendship_status='pending'";
$result = mysqli_query($conn, $query);

if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) == 0) {
    // Username not found in the DB
    $response['success'] = TRUE;
    $response['connections'] = array();
    $response['debug'] = "Username was not found in the DB. 0 friends";
    echo json_encode($response);
    die();
} else {
    // Username found. Return the user inforamtion
    $friend_pending = array();
    while($row = mysqli_fetch_assoc($result)) {
        $friend_pending[] = array("username"=> $row['username'], "first_name"=>$row['first_name'], "last_name"=>$row['last_name']);
    }
    $response['success'] = TRUE;
    $response['pending_friends'] = $friend_pending;
}
echo json_encode($response);


?>
