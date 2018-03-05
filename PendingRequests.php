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

// Query to get the user's pending requests from the DB
$query = "SELECT * FROM requests WHERE senderID='$userID' OR revceiverID='$userID'";
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
    $response['debug'] = "No pending requests found";
    echo json_encode($response);
    die();
} else {
    // Pending requests found
    $pending_requests = array();
    while($row = mysqli_fetch_assoc($result)) {
        if($row['revceiverID'] == $userID) {
            // We can approve this request
            $ApproveRequest = TRUE;

            // Query to get the other user's username
            $OtherUserID = $row['senderID'];
            $UserNameQuery = "SELECT username FROM users WHERE userid='$OtherUserID'";
            $UserNameQueryResult = mysqli_query($conn, $UserNameQuery);

            if (!$UserNameQueryResult) {
                // MYSQL Error
                $response['success'] = FALSE;
                $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
                echo json_encode($response);
                die();
            } else if (mysqli_num_rows($UserNameQueryResult) == 0) {
                // Username not found in the DB
                $response['success'] = TRUE;
                $response['connections'] = array();
                $response['debug'] = "Failed to get username for other user";
                echo json_encode($response);
                die();
            }
            $UserNameRow = mysqli_fetch_assoc($UserNameQueryResult);
            $SenderUsername = $UserNameRow['username'];
            $ReceiverUserName = $username;

        } else {
            // We cant approve the request
            $ApproveRequest = FALSE;

            // Query to get the other user's username
            $OtherUserID = $row['revceiverID'];
            $UserNameQuery = "SELECT username FROM users WHERE userid='$OtherUserID'";
            $UserNameQueryResult = mysqli_query($conn, $UserNameQuery);

            if (!$UserNameQueryResult) {
                // MYSQL Error
                $response['success'] = FALSE;
                $response['debug'] = "MYSQL query failed: ".mysqli_error($conn);
                echo json_encode($response);
                die();
            } else if (mysqli_num_rows($UserNameQueryResult) == 0) {
                // Username not found in the DB
                $response['success'] = TRUE;
                $response['connections'] = array();
                $response['debug'] = "Failed to get username for other user";
                echo json_encode($response);
                die();
            }
            $UserNameRow = mysqli_fetch_assoc($UserNameQueryResult);
            $ReceiverUserName = $UserNameRow['username'];
            $SenderUsername = $username;
        }

        $pending_requests[] = array("sender_username"=> $SenderUsername, "receiver_username"=> $ReceiverUserName, "can_we_approve"=> $ApproveRequest, "delivery_message"=>$row['packageMessage'], "request_date"=>$row['requestTime']);
    }
    $response['success'] = TRUE;
    $response['pending_requests'] = $pending_requests;
}
echo json_encode($response);


?>
