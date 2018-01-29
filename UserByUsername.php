<?php
require ('databasevaribles.php');

$username = $_GET['username'];

if (empty($username)) {
    $response['success'] = FALSE;
    $response['debug'] = "Username is not set";
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

// Query to get the user information from the DB
$result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
if (!$result) {
    // MYSQL Error
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_num_rows($result) == 0) {
    // Username not found in the DB
    $response['success'] = FALSE;
    $response['debug'] = "Username not found";
    echo json_encode($response);
    die();
} else {
    // Username found. Return the user inforamtion
    $row = mysqli_fetch_assoc($result);
    $response['success'] = TRUE;
    $response['first_name'] = $row['first_name'];
    $response['last_name'] = $row['last_name'];
    $response['friend_status'] = 'stranger';
}
echo json_encode($response);
?>
