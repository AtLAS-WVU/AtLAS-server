<?php
require ('databasevaribles.php');
// This Script Will Create a Traditional User Account.

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];

if (empty($username)) {
    $response['success'] = FALSE;
    $response['debug'] = "A username must be entered!";
    echo json_encode($response);
    die();
}

if (empty($password)) {
    $response['success'] = FALSE;
    $response['debug'] = "A password must be entered!";
    echo json_encode($response);
    die();
}

if (empty($email)) {
    $response['success'] = FALSE;
    $response['debug'] = "An email must be entered!";
    echo json_encode($response);
    die();
}

if (empty($first_name) || empty($last_name)) {
    $response['success'] = FALSE;
    $response['debug'] = "A name must be entered!";
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
$password = mysqli_real_escape_string($conn, $password);
$email = mysqli_real_escape_string($conn, $email);
$first_name = mysqli_real_escape_string($conn, $first_name);
$last_name = mysqli_real_escape_string($conn, $last_name);

// Check To Make Sure The Username Is Unique
$UniqueUsernameQuery = mysqli_query($conn,"SELECT * FROM users WHERE username = '$username'");
$numrows = mysqli_num_rows($UniqueUsernameQuery);
if ($numrows != 0) {
    $response['success'] = FALSE;
    $response['debug'] = "Username is not unique";
    $response['uniqueUser'] = FALSE;
    echo json_encode($response);
    die();
}

$UniqueEmailQuery = mysqli_query($conn,"SELECT * FROM users WHERE email = '$email'");
$numrows = mysqli_num_rows($UniqueEmailQuery);
if ($numrows != 0) {
    $response['success'] = FALSE;
    $response['debug'] = "Email is not unique";
    $response['uniqueUser'] = TRUE;
    $response['uniqueEmail'] = FALSE;
    echo json_encode($response);
    die();
}

// Generate a UserID For The User.
$UserID ="";
$numrows =1;
while ($numrows != 0) {
    // Keep Generating a Random Number Until We Can Find One That Hasnt Been Used Yet.
    $MatchID ="";
    for ($i = 0; $i<8; $i++) {
        $UserID  .= mt_rand(0,9);
    }

    // Select Any Data From The Match Database To See If We Have Already Used The Match ID.
    $result = mysqli_query($conn,"SELECT userid FROM users WHERE userid = '$UserID '");
    if (!$result) {
        $response['success'] = FALSE;
        $response['uniqueUser'] = TRUE;
        $response['uniqueEmail'] = TRUE;
        $response['debug'] = "UserID generation failed. ".mysqli_error($conn);
        echo json_encode($response);
        die();
    }
    $numrows = mysqli_num_rows($result);
}
// Hash The Users Password
$password = password_hash($password,PASSWORD_BCRYPT, array(
    'cost' => 12
));

// Insert The Data Into The Server
$InsertDataQuery = mysqli_query($conn,"INSERT INTO users (userid, username, first_name, last_name, password, email, private_key, key_creation_time) VALUES ('$UserID', '$username', '$first_name', '$last_name', '$password', '$email', '', '')");
if (!$InsertDataQuery) {

    $response['success'] = FALSE;
    $response['uniqueUser'] = TRUE;
    $response['uniqueEmail'] = TRUE;
    $response['debug'] = "Unable to create user account. ".mysqli_error($conn);
    echo json_encode($response);
    die();
}

// TODO: Maybe send an Email Comformation To Activate The Account

$response['success'] = TRUE;
$response['uniqueUser'] = TRUE;
$response['uniqueEmail'] = TRUE;
echo json_encode($response);
?>
