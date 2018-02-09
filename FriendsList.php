<?php
require ('databasevaribles.php');


$username = $_GET['username'];
$token = $_GET['token'];

if (empty($username) || empty($token)) {
    $response['success'] = FALSE;
    $response['debug'] = "Username or token is not set";
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

// Query to get the user's friends from the DB
$query = "SELECT IF(user_1 = '$userID', user_2, user_1) as username_of_friend, friendship_status, friendship_date, username FROM friend_connection INNER JOIN users ON userid = IF(user_1 = '$userID', user_2, user_1) WHERE user_1 = '$userID' OR user_2 = '$userID'";

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
    $friend_connections = array();
    while($row = mysqli_fetch_assoc($result)) {
        $friend_connections[] = array("username"=> $row['username'], "status"=>$row['friendship_status'], "date"=>$row['friendship_date']);
    }
    $response['success'] = TRUE;
    $response['connections'] = $friend_connections;
}
echo json_encode($response);


?>
