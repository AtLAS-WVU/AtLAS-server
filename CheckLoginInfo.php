<?php
require ('databasevaribles.php');
require('UtilFunctions.php');

// This Script Will Authenticate a User Logging In From The Client.
// A Key Will Be Passed Back To The User. This Key Will Be Required When Proforming Actions With The Server.
// This Private key will be store on the users phone.
$username = $_POST['username'];
$password = $_POST['password'];

if (empty($username) || empty($password)) {
    $response['success'] = FALSE;
    $response['debug'] = "Username or password is not set";
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
$username = mysqli_real_escape_string($conn,$username);
$password = mysqli_real_escape_string($conn,$password);

// Check the info with a query to check to see if the user is register
$result = mysqli_query($conn,"SELECT * FROM users WHERE username = '$username'");
if (!$result) {
    $response['success'] = FALSE;
    $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
    echo json_encode($response);
    die();
}

$UserAccountFound = false;
while ($row = mysqli_fetch_assoc($result)) {
    // if(password_verify($password,$row['password'])){ // Use this once we hash the passwords
    if ($password == $row['password']) { //Use only while passwords are plain text
        $UserID = $row['userid'];
        $UserAccountFound = true;
        break;
    }
}

// If user wasn't found in the database
if (mysqli_num_rows($result) == 0 || !$UserAccountFound) {
    $response['success'] = FALSE;
    $response['debug'] = "Username or password wasn't found in the database";
    echo json_encode($response);
    die();
} else {
    $response['success'] = TRUE;
}

// Now Create a Key That Will Be Sent To The User.
$date = date("Y-m-d H:i:s");

//Check To See If The User is Already Logged In
/*
$result2 = mysqli_query($conn,"SELECT * FROM currentloggedonusers WHERE UserID = '$UserID'");
if(mysqli_num_rows($result2) != 0){
	//We Are Already Logged In
	$StringToReturn = "0 \r\n The User Is Already Logged In!";
	echo $StringToReturn;
	die();
}
*/

// Generate The Random String.
$key = generateRandomString(60);

$SQLLoginQuery = "UPDATE users SET private_key='$key', key_creation_time='$date' WHERE userid = '$UserID'";
$AddKeyQuertResult = mysqli_query($conn,$SQLLoginQuery);
if (!$AddKeyQuertResult) {
    $response['success'] = FALSE;
    $response['debug'] = "Unable to enter key for logged in user: ".mysqli_error($conn);
    echo json_encode($response);
    die();
}
$response['key'] = $key;
echo json_encode($response);
?>
