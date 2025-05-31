<?php
require(__DIR__ . '/../config_session.php');

function addUserDj(){
    global $mysqli, $data;
    // Securely retrieve and sanitize input
    $target = escape($_POST['add_dj']);
    $user = userNameDetails($target);
    if(empty($user)){
        return boomCode(3,['msg'=> $lang['user_not_found']]);
    }
    if(userDj($user)){
        return boomCode(4);
    }
    // Ensure user has permissions
    if(canEditUser($user, $data['can_dj'], 1) || (canManageDj() && mySelf($user['user_id']))){
        $user_id = (int) $user['user_id'];
        // Secure update using prepared statement
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_dj = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $user['user_dj'] = 1;
        return boomCode(1, array('data' => boomTemplate('element/admin_dj', $user)));
    }
    else {
        return boomCode(2);
    }
}

function removeUserDj(){
    global $mysqli, $data;
    // Securely retrieve and sanitize input
    $target = escape($_POST['remove_dj'], true);
    $user = userDetails($target);
    if(empty($user)){
        return 3;
    }
    // Ensure user has permissions
    if(canEditUser($user, $data['can_dj'], 1) || (canManageDj() && mySelf($user['user_id']))){
        $user_id = (int) $user['user_id'];
        // Secure update using prepared statement
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_dj = 0, user_onair = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        return 1;
    }
    else {
        return 2;
    }
}
function setUserOnAir(){
    global $mysqli, $data;
    // Securely retrieve and sanitize input
    $id = escape($_POST['admin_onair'], true);
    $user = userDetails($id);
    if(empty($user)){
        return 3;
    }
    // Ensure user has permission to manage DJs
    if(canManageDj() && canEditUser($user, $data['can_dj']) && userDj($user)){
        $user_id = (int) $user['user_id'];
        $new_status = isOnAir($user) ? 0 : 1; // Toggle between 0 and 1
        // Secure update using prepared statement
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_onair = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $new_status, $user_id);
        $stmt->execute();
        $stmt->close();
		//redisUpdateUser($user['user_id']);	
        return $new_status;
    }
    else {
		//redisUpdateUser($user['user_id']);
        return 2;
    }
}

function Admin_setOnAir(){
    global $mysqli, $data;
    // Ensure user is a DJ
    if (!userDj($data)) {
        return 0;
    }
    // Securely retrieve and validate input
    $onair = isset($_POST['user_onair']) ? (int) $_POST['user_onair'] : 0;
    $user_id = (int) $data['user_id'];
    // Use a prepared statement to prevent SQL injection
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_onair = ? WHERE user_id = ?");
    $stmt->bind_param("ii", $onair, $user_id);
    $stmt->execute();
    $stmt->close();
	//redisUpdateUser($data['user_id']);

    return 1;
}


// end of functions

if(isset($_POST["admin_onair"])){
	echo setUserOnAir();
	die();
}
if(isset($_POST["user_onair"])){
	echo Admin_setOnAir();
	die();
}
if(isset($_POST["add_dj"])){
	echo addUserDj();
	die();
}
if(isset($_POST['remove_dj'])){
	echo removeUserDj();
	die();
}
die();
?>