<?php
$res =[];
if ($f == 'action_room') {
function get_in_room(){
    global $mysqli, $data;
    // Ensure all required parameters are set
    $get_in_room = escape($_POST['get_in_room']);
    $room = escape($_POST['room']);
    if (!isset($room, $get_in_room)) {
        return;
    }
    // Get room details
    $target = escape($room);
    $password = $_POST['pass'] ?? null;
    $userId = $data['user_id'];
    // Fetch room details
    $room = myRoomDetails($target);
    if ($room === false) {
        echo boomCode(1);
        exit;
    }
    // Check if the room is blocked or requires verification
    if ($room['room_blocked'] > time() || mustVerify()) {
        echo boomCode(99);
        exit;
    }
    // Get room mute and role details
    $muted = $room['room_muted'] ?? 0;
    $role = $room['room_status'] ?? 0;
    // Handle muting if applicable
    if ($muted > 0) {
        $stmt = $mysqli->prepare("UPDATE boom_users SET room_mute = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $muted, $userId); // Both are integers
        $stmt->execute();
        $stmt->close();
    }
    // Set user role
    $data['user_role'] = $role;
    // Check room access
    if (!boomAllow($room['access'])) {
        echo boomCode(2);
        exit;
    }
    // Handle password protected rooms
    if (!empty($room['password'])) {
        if ($password === null || ($password !== $room['password'] && !canRoomPassword())) {
            echo $password === null ? boomCode(4) : boomCode(5);
            exit;
        }
    }
    // Update user data for the room
    $stmt = $mysqli->prepare("
        UPDATE boom_users 
        SET join_msg = 0, user_roomid = ?, last_action = ?, user_role = ?, room_mute = ? 
        WHERE user_id = ?
    ");
    $time_now = time();
    $stmt->bind_param("siiii", $target, $time_now, $role, $muted, $userId);
    $stmt->execute();
    $stmt->close();
    // Update room action timestamp
    $stmt = $mysqli->prepare("
        UPDATE boom_rooms 
        SET room_action = ? 
        WHERE room_id = ?
    ");
    $stmt->bind_param("is", $time_now, $target);
    $stmt->execute();
    $stmt->close();
    // Call leaveRoom function (assuming it handles cleanup for users leaving)
    leaveRoom();
    // Send response with room name and ID
    echo boomCode(10, ['name' => $room['room_name'], 'id' => $room['room_id']]);
    exit;
}
	if($s == 'switchRoom' && boomLogged() === true) {
			$res = get_in_room();
			header("Content-type: application/json");
			echo json_encode($res);
			exit();
	} 
		if ($s == 'addRoom' && boomLogged() === true) {
			// Escape and validate incoming POST data
			$set_pass = escape($_POST["set_pass"]);
			$set_type = escape($_POST["set_type"]);
			$set_name = escape($_POST['set_name']);
			$set_description = escape($_POST['set_description']);
			// Check if user has permission and if room type is valid
			if (!canRoom() || !roomType($set_type)) {
				echo json_encode(['code' => 0, 'msg' => 'You do not have permission to add a room or your level is not allowed']);
				exit();
			}
			// Default room system setting
			$room_system = boomAllow(100) ? 1 : 0;
			// Validate room name
			if (!validRoomName($set_name)) {
				echo json_encode(['code' => 0, 'msg' => 'Room Name is not valid']);
				exit();
			}
			// Validate room description length
			if (isToolong($set_description, $cody['max_description'])) {
				echo json_encode(['code' => 1, 'msg' => 'Description is too long']);
				exit();
			}
			// Validate password length
			if (mb_strlen($set_pass) > 20) {
				echo json_encode(['code' => 1, 'msg' => 'The password is more than 20 characters']);
				exit();
			}
			// Check if user has reached the max number of rooms
			$stmt = $mysqli->prepare("SELECT COUNT(room_id) FROM boom_rooms WHERE room_creator = ?");
			$stmt->bind_param("i", $data['user_id']);
			$stmt->execute();
			$stmt->bind_result($room_count);
			$stmt->fetch();
			$stmt->close();
			if ($room_count >= $cody['max_room'] && !boomAllow(70)) {
				echo json_encode(['code' => 5, 'msg' => 'Reached Max Rooms']);
				exit();
			}
			// Check for duplicate room name
			$stmt = $mysqli->prepare("SELECT room_id FROM boom_rooms WHERE room_name = ?");
			$stmt->bind_param("s", $set_name);
			$stmt->execute();
			$stmt->store_result();
			if ($stmt->num_rows > 0) {
				echo json_encode(['code' => 6, 'msg' => 'Error: Duplicate room name']);
				$stmt->close();
				exit();
			}
			$stmt->close();
			// Insert new room
			$stmt = $mysqli->prepare("
				INSERT INTO boom_rooms (room_name, access, description, password, room_system, room_creator, room_action)
				VALUES (?, ?, ?, ?, ?, ?, ?)
			");
			$room_action_time = time();
			$stmt->bind_param("ssssiii", $set_name, $set_type, $set_description, $set_pass, $room_system, $data['user_id'], $room_action_time);
			$stmt->execute();
			$last_id = $stmt->insert_id;
			$stmt->close();
			// Assign the user to the room
			if (!boomAllow(90)) { // If not a system user
				$stmt = $mysqli->prepare("
					UPDATE boom_users 
					SET user_roomid = ?, last_action = ?, user_role = '6' 
					WHERE user_id = ?
				");
				$stmt->bind_param("iii", $last_id, $room_action_time, $data['user_id']);
				$stmt->execute();
				$stmt->close();
				// Add user as staff with role 6 (admin)
				$stmt = $mysqli->prepare("INSERT INTO boom_room_staff (room_id, room_staff, room_rank) VALUES (?, ?, '6')");
				$stmt->bind_param("ii", $last_id, $data['user_id']);
				$stmt->execute();
				$stmt->close();
			} else { // For system users
				$stmt = $mysqli->prepare("
					UPDATE boom_users 
					SET user_roomid = ?, last_action = ? 
					WHERE user_id = ?
				");
				$stmt->bind_param("iii", $last_id, $room_action_time, $data['user_id']);
				$stmt->execute();
				$stmt->close();
			}
			// Get room info to return
			$groom = roomInfo($last_id);
			// Log room creation
			boomConsole('create_room', ['room' => $groom['room_id']]);
			// Send success response
			echo json_encode([
				'code' => 7,
				'msg' => 'The room has been added successfully',
				'r' => ['name' => $groom['room_name'], 'id' => $groom['room_id']]
			]);
			exit();
		}

	if($s == 'leave_room') {
		// Ensure the user is logged in
		if (!boomLogged()) {
			echo json_encode(['code' => 0, 'msg' => 'User is not logged in']);
			die();
		}
		// Update the user_roomid to 0 to indicate the user has left the room
		$stmt = $mysqli->prepare("UPDATE boom_users SET user_roomid = '0' WHERE user_id = ?");
		$stmt->bind_param("i", $data['user_id']); // Bind the user_id parameter safely
		if ($stmt->execute()) {
			echo json_encode(['code' => 1, 'msg' => 'Successfully left the room']);
		} else {
			echo json_encode(['code' => 0, 'msg' => 'Failed to leave the room']);
		}
		$stmt->close(); // Close the prepared statement
		die(); // Terminate the script after the action is completed
	}
    if($s == 'access_room') {
            $res = get_in_room();
            header("Content-type: application/json");
            echo json_encode($res);
            exit();
    }
	if ($s == 'admin_addroom') {
    $set_pass = escape($_POST["admin_set_pass"]);
    $set_type = escape($_POST["admin_set_type"]);
    $set_name = escape($_POST['admin_set_name']);
    $set_description = escape($_POST['admin_set_description']);
    // Validate Room Name Length
    if (isTooLong($set_name, $cody['max_room_name']) || strlen($set_name) < 4) {
        $res['code'] = 2;
        $res['msg'] = 'Room Name is too long or too short';
        echo json_encode($res);
        exit();
    }
    // Check for duplicate room name
    $stmt = $mysqli->prepare("SELECT room_name FROM boom_rooms WHERE room_name = ?");
    $stmt->bind_param("s", $set_name); // Prevent SQL injection by using prepared statements
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $res['code'] = 6;
        $res['msg'] = 'Room Name already exists';
        echo json_encode($res);
        exit();
    }
    $stmt->close();
    // Validate Room Description Length
    if (isToolong($set_description, $cody['max_description'])) {
        $res['code'] = 0;
        $res['msg'] = 'Description is too long';
        echo json_encode($res);
        exit();
    }
    // Validate Password Length
    if (mb_strlen($set_pass) > 20) {
        $res['code'] = 1;
        $res['msg'] = 'The password is more than 20 characters';
        echo json_encode($res);
        exit();
    }
    // Insert the new room into the database
    $stmt = $mysqli->prepare("
        INSERT INTO boom_rooms (room_name, access, description, password, room_system, room_creator, room_action)
        VALUES (?, ?, ?, ?, 1, ?, ?)
    ");
    $current_time = time();
    $stmt->bind_param("ssssii", $set_name, $set_type, $set_description, $set_pass, $data['user_id'], $current_time);
    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        $stmt->close();
        // Remove any previous staff assignments for the new room
        $mysqli->query("DELETE FROM boom_room_staff WHERE room_id = '$last_id'");
        // Fetch the room information
        $room = roomInfo($last_id);
        if (empty($room)) {
            $res['code'] = 1;
            $res['msg'] = 'Failed to retrieve room details';
            echo json_encode($res);
            exit();
        } else {
            boomConsole('create_room', array('room' => $room['room_id']));
            $res['html'] = boomTemplate('element/admin_room', $room);
            $res['msg'] = 'The room has been added successfully';
        }
    } else {
        $res['code'] = 0;
        $res['msg'] = 'Failed to create the room';
    }
    // Return the response as JSON
    header("Content-type: application/json");
    echo json_encode($res);
    exit();
}
	if ($s == 'admin_update_tabs') {
		$room_tabs = escape($_POST['room_tabs']);
    	if(isset($room_tabs)){
			$update_tab = fu_update_dashboard(array(
					"use_room_tabs" => $room_tabs,
			));
			if($update_tab){
				$res['code'] = 1;
			}
			
		}
		 header("Content-type: application/json");
        echo json_encode($res);
        exit();		
	}
	if($s == 'admin_update_room') {
		$player_id = 0;
		$target = escape($_POST['admin_set_room_id']);
		$name = escape($_POST['admin_set_room_name']);
		$description = escape($_POST['admin_set_room_description']);
		$password = escape($_POST['admin_set_room_password']);
		$room_keywords = escape($_POST['admin_set_room_keywords']);
		// Check if player is set and valid
		if (isset($_POST['admin_set_room_player'])) {
			$player = escape($_POST['admin_set_room_player']);
			$stmt = $mysqli->prepare("SELECT * FROM boom_radio_stream WHERE id = ?");
			$stmt->bind_param("i", $player);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$play = $result->fetch_assoc();
				$player_id = $play['id'];
			} else {
				$player_id = 0;
			}
			$stmt->close();
		}
		$room_access = escape($_POST['admin_set_room_access']);
		// Get the room details
		$stmt = $mysqli->prepare("SELECT * FROM boom_rooms WHERE room_id = ?");
		$stmt->bind_param("i", $data['user_roomid']);
		$stmt->execute();
		$get_room = $stmt->get_result();
		$room = $get_room->fetch_assoc();
		$stmt->close();
		// Check if room already exists with the same name
		if (roomExist($name, $target)) {
			$res['code'] = 2;
			$res['msg'] = 'Room with the same name already exists';
			echo json_encode($res);
			exit();
		}
		// Validate description length
		if (isToolong($description, $cody['max_description'])) {
			$res['code'] = 0;
			$res['msg'] = 'Description is too long';
			echo json_encode($res);
			exit();
		}
		// Validate room name
		if ($name == '' || isTooLong($name, $cody['max_room_name'])) {
			$res['code'] = 4;
			$res['msg'] = 'Room name is either empty or too long';
			echo json_encode($res);
			exit();
		}
		// If room access is 1, reset password
		if ($target == 1) {
			$password = '';
			$room_access = 0;
		}
		// Update the room details
		$stmt = $mysqli->prepare("
			UPDATE boom_rooms 
			SET room_name = ?, description = ?, password = ?, room_player_id = ?, access = ?, room_keywords = ?
			WHERE room_id = ?
		");
		$stmt->bind_param("sssiiis", $name, $description, $password, $player_id, $room_access, $room_keywords, $target);
		if ($stmt->execute()) {
			$res['code'] = 1;
			$res['msg'] = 'Room updated successfully';
		} else {
			$res['code'] = 0;
			$res['msg'] = 'Failed to update room';
		}
		$stmt->close();
		// Send the response as JSON
		header("Content-type: application/json");
		echo json_encode($res);
		exit();
	}

	if($s == 'changeRoomRank') {
		// Check if the user has permission to edit the room
		if (!canEditRoom()) {
			exit();
		}
		// Escape and validate input
		$target = escape($_POST['target']);
		$rank = escape($_POST['room_staff_rank']);
		$user = userRoomDetails($target);
		// Ensure the target user is valid
		if (empty($target)) {
			$res['code'] = 2;
			$res['msg'] = 'Target user is not specified';
			echo json_encode($res);
			exit();
		}
		// Ensure the user has permission to perform the action
		if (!canRoomAction($user, 6)) {
			$res['code'] = 0;
			$res['msg'] = 'You do not have permission to perform this action';
			echo json_encode($res);
			exit();
		}
		// If rank is greater than 0, update the room staff rank
		if ($rank > 0) {
			if (checkMod($user['user_id'])) {
				// Insert new rank for staff member
				$stmt = $mysqli->prepare("INSERT INTO boom_room_staff (room_id, room_staff, room_rank) VALUES (?, ?, ?)");
				$stmt->bind_param("iii", $data['user_roomid'], $user['user_id'], $rank);
				$stmt->execute();
				$stmt->close();
			} else {
				// Update the existing rank of the staff member
				$stmt = $mysqli->prepare("UPDATE boom_room_staff SET room_rank = ? WHERE room_id = ? AND room_staff = ?");
				$stmt->bind_param("iii", $rank, $data['user_roomid'], $user['user_id']);
				$stmt->execute();
				$stmt->close();
			}
			// Remove previous actions for the user
			$stmt = $mysqli->prepare("DELETE FROM boom_room_action WHERE action_user = ? AND action_room = ?");
			$stmt->bind_param("ii", $user['user_id'], $data['user_roomid']);
			$stmt->execute();
			$stmt->close();
			// Update user role and mute status
			$stmt = $mysqli->prepare("UPDATE boom_users SET user_role = ?, room_mute = 0 WHERE user_id = ? AND user_roomid = ?");
			$stmt->bind_param("iii", $rank, $user['user_id'], $data['user_roomid']);
			$stmt->execute();
			$stmt->close();
		} else {
			// If rank is 0, remove the user from the staff
			$stmt = $mysqli->prepare("DELETE FROM boom_room_staff WHERE room_staff = ? AND room_id = ?");
			$stmt->bind_param("ii", $user['user_id'], $data['user_roomid']);
			$stmt->execute();
			$stmt->close();
			// Set the user's role back to 0
			$stmt = $mysqli->prepare("UPDATE boom_users SET user_role = 0 WHERE user_id = ? AND user_roomid = ?");
			$stmt->bind_param("ii", $user['user_id'], $data['user_roomid']);
			$stmt->execute();
			$stmt->close();
		}
		// Log the action
		boomConsole('change_room_rank', array('target' => $user['user_id'], 'rank' => $rank));
		// Send the success response
		$res['code'] = 1;
		$res['msg'] = 'Room rank has been updated successfully';
		header("Content-type: application/json");
		echo json_encode($res);
		exit();
	}
	if($s == 'saveRoom') {
		// Check if the user has permission to edit the room
		if (!canEditRoom()){
			$res['msg'] = 'You do not have permission to add a room or your level is not allowed';
			echo json_encode($res);
			exit();
		}
		// Escape and validate the input data
		$name = escape($_POST['set_room_name']);
		$description = escape($_POST['set_room_description']);
		$password = escape($_POST['set_room_password']);
		$player_check = isset($_POST['set_room_player']) ? 1 : 0;
		$player_id = 0;
		// Ensure the room name is valid
		if (roomExist($name, $data['user_roomid'])) {
			$res['code'] = 2;
			$res['msg'] = 'Room name already exists';
			echo json_encode($res);
			exit();
		}
		if (isToolong($description, $cody['max_description'])) {
			$res['code'] = 0;
			$res['msg'] = 'Description is too long';
			echo json_encode($res);
			exit();
		}
		if ($name == '' || checkName($name) || strlen($name) > $cody['max_room_name']) {
			$res['code'] = 4;
			$res['msg'] = 'Invalid room name';
			echo json_encode($res);
			exit();
		}
		// If the room is a special room, clear the password
		if ($data['user_roomid'] == 1) {
			$password = '';
		}
		// Handle player selection
		if ($player_check == 1) {
			$player = escape($_POST['set_room_player']);
			if ($player != 0) {
				if ($player != $room['room_player_id']) {
					$stmt = $mysqli->prepare("SELECT * FROM boom_radio_stream WHERE id = ?");
					$stmt->bind_param("i", $player);
					$stmt->execute();
					$result = $stmt->get_result();
					if ($result->num_rows > 0) {
						$setplay = $result->fetch_assoc();
						$player_id = $setplay['id'];
					}
					$stmt->close();
				} else {
					$player_id = $room['room_player_id'];
				}
			} else {
				$player_id = 0;
			}
		}
		// Update the room details
		$stmt = $mysqli->prepare("UPDATE boom_rooms SET room_name = ?, description = ?, password = ?, room_player_id = ? WHERE room_id = ?");
		$stmt->bind_param("sssii", $name, $description, $password, $player_id, $data['user_roomid']);
		$update = $stmt->execute();
		$stmt->close();
		if ($update) {
			$res['code'] = 1;
			$res['msg'] = 'The room has been updated successfully';
		} else {
			$res['code'] = 0;
			$res['msg'] = 'Failed to update room';
		}
		header("Content-type: application/json");
		echo json_encode($res);
		exit();
	}

}

?>