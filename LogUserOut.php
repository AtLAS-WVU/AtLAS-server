<?php
require ('databasevaribles.php');

$username = $_POST['username'];
$key = $_POST['key'];

if (empty($username) || empty($key)) {
    $response['success'] = FALSE;
    $response['debug'] = "Username or key is not set";
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
$key = mysqli_real_escape_string($conn, $key);

// Check the info with a query to check to see if the user is register
$result = mysqli_query($conn, "UPDATE users SET private_key = '' WHERE username = '$username' AND private_key = '$key'");
if (!$result) {
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
} else if (mysqli_affected_rows($conn) == 0) {
    $response['success'] = FALSE;
    $response['debug'] = "Zero rows changed. User not logged out";
    echo json_encode($response);
    die();
} else {
    $response['success'] = TRUE;
}
echo json_encode($response);
?>
