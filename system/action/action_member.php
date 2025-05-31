<?php
$res = array(); 
function like_profile($target){
    global $db, $data;
    $me = intval($data['user_id']);
    $him = intval(escape($target['user_id']));
    $db->where('hunter', $me);
    $db->where('target ', $him);
    $check_like = $db->getOne('pro_like');
    if (empty($check_like)) {
        //return 'No likes yet';
        $likedata = Array ("hunter" => $me, "target" => $him, "like_date" => time() );
        $query = $db->insert ('pro_like', $likedata);
        	boomNotify("prolike", array("hunter" => $me, "target" => $him, "source" => 'system',"icon" => 'plike'));
 		    // Check if the user is inactive
		    $last_active = $target['last_active'];
		    $current_time = time();
		    $inactive_time = 60; // 1 minute
            if(($current_time - $last_active) > $inactive_time){
				if($data['allow_onesignal']==1){
					// User is inactive, send a notification
					$notification_msg = $data['user_name'].' like Your Profile 💖';
					sendNotification($target['push_id'], $notification_msg);					
				}
		    }      	
    }else{
        $db->where('hunter', $me);
        $db->where('target', $him);
       $delete_like= $db->delete('pro_like');
        if($delete_like===true){
            $db->where('notifier', $me);
            $db->where('notified', $him);
            $delete_notfication = $db->delete('notification');
        }
    }
    $output = getProfileLikes($target);
    return $output;

}
function my_likes($target){
    global $db, $data,$lang;
    $likes = array();
   $list = "";
    $cols = Array ("id", "hunter", "target","like_date");
    $db->where('target ', $data['user_id']);
    $users = $db->get("pro_like", null, $cols);
    if ($db->count > 0){
        foreach ($users as $user) { 
            $user_info = userDetails($user['hunter']);
           $list .= extra_users_list($user_info);
        }
    return $list;    
    }else {
        return emptyZone($lang["nothing_found"]);
    }
    
}
function getRandomNumber() {
    return rand(0, 11199); // Generates a random number between 0 and 99
}
function after_recovery_pass() {
	 global $mysqli,$data;
    // Sanitize inputs
    $pass = escape($_POST["user_new_password"]);
    $target = escape($_POST["target_id"]);
    // Fetch user details
    $user = userDetails($target);
    if (!$user) {
        return -1; // User not found
    }
    // Permission check
    if (!canModifyPassword($user)) {
        return 0; // No permission
    }
    // Validate password
    if (!boomValidPassword($pass)) {
        return 2; // Invalid password
    }
    // Hash the password using PASSWORD_BCRYPT
    $new_pass = password_hash($pass, PASSWORD_BCRYPT);
    // Update database using prepared statement
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_password = ? WHERE user_id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        return -1; // Database error
    }
    $stmt->bind_param("si", $new_pass, $user["user_id"]);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return -1; // Database error
    }
    // Log the action
    boomConsole("pass_user", ["target" => $user["user_id"], "custom" => $user["user_name"]]);
    return 1; // Success
}
if ($f == "action_member") {
	if 	($s == "like_profile") {
         // Send response as JSON
        $target = userDetails(escape($_POST['like_profile']));
        $result = like_profile($target);
        echo ($result);
        exit();       
    } 
    if 	($s == "my_likes") {
         $list =  my_likes(intval($data['user_id']));
         header("Content-type: application/json");
         echo json_encode($list);
        exit();       
    }
    if	($s =="share_gold"){
        if(checkFlood()){ 
        	echo 100;
            exit();
        }
        if(muted() || isRoomMuted($data)){ 
        	exit();       
        }
    	if(mySelf($_POST['share_gold'])){ 
    		exit();
    	}            
        if (isset($_POST['share_gold'], $_POST['shared_gold'])) {
            $targetUserId = intval($_POST['share_gold']);
            $goldAmount = intval($_POST['shared_gold']);
            
            $data_array['target'] = $targetUserId;
            $data_array['gold_amount'] = $goldAmount;
            
            $receiver = userDetails($targetUserId);
            $hunter = userDetails($data['user_id']);
            $myPoints = $hunter['user_gold'];
            
            if (empty($receiver)) {
                $data_array['status'] = 400; // Bad Request: Receiver not found
                header("Content-type: application/json");
                echo json_encode($data_array);
                exit();
            }
            
            $data_array['status'] = 100; // Initial status
            
            if ($myPoints >= $goldAmount) {
                // Deduct gold amount from the sender's gold
                $sumGold = removeGold($hunter, $goldAmount);
                // Add gold amount to the receiver's gold
                $addGold = addGold($receiver, $goldAmount);
                
                // Prepare chat content
                $content = htmlspecialchars($hunter['user_name'], ENT_QUOTES, 'UTF-8') . ' shared ' . intval($goldAmount) . ' ' . htmlspecialchars($lang['gold'], ENT_QUOTES, 'UTF-8') . ' with <font color="green">' . htmlspecialchars($receiver['user_name'], ENT_QUOTES, 'UTF-8') . '</font>';
                systemPostChat($receiver['user_roomid'], $content);
                boomNotify("gold_share", array("hunter" => $hunter['user_id'], "target" => $receiver['user_id'], "source" => 'gold' ,"custom" => $goldAmount,"icon" => 'gold'));
                
     		    // Check if the user is inactive
    		    $last_active = $receiver['last_active'];
    		    $current_time = time();
    		    $inactive_time = 60; // 1 minute
                if(($current_time - $last_active) > $inactive_time){
					if($data['allow_onesignal']==1){
						// User is inactive, send a notification
						$notification_msg = $data['user_name'].' '.$lang['gold_share'].intval($goldAmount);
						sendNotification($receiver['push_id'], $notification_msg);
					}
    		    }                 
                $data_array['status'] = 200; // Success
            } else {
                $data_array['status'] = 402; // Payment Required: Insufficient gold
            }
            
            echo fu_json_results($data_array);
            exit();			
        }
          
    }
    if	($s == "acceptWarn") {
        $res['status'] = 100;
        $query = cl_update_user_data($data['user_id'],array(
            "warn_msg" =>'',
            ));
        if($query){
           $res['status'] = 200;
        }
		echo fu_json_results($res);
		exit(); 
    }  
	if ($s == "start_dj") {
    $media_type = escape($_POST['media_type']);
    $media_url = escape($_POST['media_url']);
    // Initialize response array
    $res = array('status' => 400, 'msg' => 'Something went wrong');
    // Handle different media types
    if ($media_type == "youtube") {
        $res['mtype'] = 'youtube';
        $res['media_id'] = escape($media_url);
    } elseif ($media_type == "soundcloud") {
        $res['mtype'] = 'soundcloud';
        $res['media_id'] = escape($media_url);
    } elseif ($media_type == "mp3") {
        $res['mtype'] = 'mp3';
        $res['media_id'] = escape($media_url);
    } elseif ($media_type == "mp4") {
        $res['mtype'] = 'mp4';
        $res['media_id'] = escape($media_url);
    }elseif ($media_type == "live") {
        $res['mtype'] = 'live';
        $res['media_id'] = 'Broadcast_'.getRandomNumber();
    }
    // Check if media_id is valid
    if (empty($res['media_id'])) {
        $res['msg'] = 'Invalid media URL or media type';
		echo fu_json_results($res);
		exit(); 
    }
    // Directly fetch the DJ from the database
    $db->where("dj_id", $data['user_id']);
    $db->where("room_id", $data['user_roomid']);
    $existing_dj = $db->getOne("dj");
    if (empty($existing_dj)) {
        // Insert new DJ data into the database
        $broadcast_data = array(
            "dj_id" => $data['user_id'],
            "room_id" => $data['user_roomid'],
            "start_time" => time(),
            "status" => 'active',
            "mediatype" => $res['mtype'],
            "mediaurl" => $res['media_id'],
        );
        $insert_dj = $db->insert('dj', $broadcast_data);
        if ($insert_dj) {
            $res['status'] = 200;
            $res['msg'] = 'Broadcast started successfully';
            $res['query'] = $insert_dj;
        } else {
            $res['msg'] = 'Failed to start broadcast';
        }
    } else {
        // Update the start_time if the DJ already exists
        $update_data = array(
            "start_time" => time(),
            "mediatype" => $res['mtype'],
            "mediaurl" => $res['media_id'],
            "status" => 'active',
        );
        $db->where("dj_id", $data['user_id']);
        $db->where("room_id", $data['user_roomid']);
        if ($db->update('dj', $update_data)) {
            $res['status'] = 200;
            $res['msg'] = 'Broadcast updated successfully';
        } else {
            $res['msg'] = 'Failed to update broadcast';
        }
    }
    // Return the response as JSON
	echo fu_json_results($res);
    exit(); 
}
    if ($s == "end_dj") {
		// Retrieve the rise_id from the POST data, default to 0 if not provided
		$rise_id = !empty($_POST['with_rise_id']) ? escape($_POST['with_rise_id']) : 0; 

		// If rise_id is empty or invalid, set it to 0 (though this is redundant in this case)
		if (empty($rise_id)) {
			$rise_id = 0;
		}

		$res['rise_id'] = $rise_id;
		if($res['rise_id']>0){
			$rise_info =fuse_user_data($res['rise_id']);
			$r_info = $rise_info;
			$res['content'] = 'Please start Your broadcast '.$r_info['user_name'];
			systemPostChat($r_info['user_roomid'], $res['content'], array('type'=> 'system__action'));
			//Give dj acsses to this user
			if(!makeDj($r_info['user_id'])){
				echo makeDj($r_info['user_id']);
			}
			//set other user OnAir acsses status
			echo addOnair($r_info['user_id']);
			//disable dj for current dj
			echo setOffair($data);
		}
        // Prepare the data for updating the end_time
        $update_data = array(
            "end_time" => time(),
            "status" => 'ended', // Optionally update the status to reflect the end of the broadcast
            "raised_hand_user_ids" => 'null',
            "mediaurl" => '',
            "start_time" => 0,
        );
        // Target the current DJ by user ID and room ID
        $db->where("dj_id", $data['user_id']);
        $db->where("room_id", $data['user_roomid']);
        // Attempt to update the end_time
        if ($db->update('dj', $update_data)) {
            $res['status'] = 200;
            $res['msg'] = 'Broadcast ended successfully';
        } else {
            // Provide a more detailed error message if the update fails
            $res['status'] = 500;
            $res['msg'] = 'Failed to end the broadcast. Please try again.';
        }
    
        // Return the response as JSON
       	 echo fu_json_results($res);
        exit(); 
    }
    if ($s == "risehand_dj") {
        $rise_handId = $data['user_id'];  // User requesting to raise hand
        $broadcast_id = escape($_POST['b_id']);  // Broadcast ID from the request
        $user_roomid = $data['user_roomid'];  // User's room ID
    
        // Get the current raised_hand_user_ids from the database
        $db->where("id", $broadcast_id);
        $db->where("room_id", $user_roomid);
        $broadcast = $db->getOne("dj", "raised_hand_user_ids");
    
        // Initialize an empty array if no raised hands exist yet
        $raised_hand_ids = json_decode($broadcast['raised_hand_user_ids'], true) ?? [];
    
        // Check if the user has already raised their hand
        if (!in_array($rise_handId, $raised_hand_ids)) {
            // Add the new user_id to the raised hand list
            $raised_hand_ids[] = $rise_handId;
    
            // Convert the array back to JSON
            $update_data = array(
                "raised_hand_user_ids" => json_encode($raised_hand_ids),
            );
    
            // Update the broadcast with the new raised hand list
            $db->where("id", $broadcast_id);
            $db->where("room_id", $user_roomid);
            if ($db->update('dj', $update_data)) {
                $res['status'] = 200;
                $res['msg'] = 'Risehand requested successfully';
            } else {
                $res['status'] = 500;
                $res['msg'] = 'Failed to update Risehand request. Please try again.';
            }
        } else {
            // If the user has already raised their hand
            $res['status'] = 400;
            $res['msg'] = 'You have already raised your hand.';
        }
    
        // Send the response
		 echo fu_json_results($res);
		 exit();
    }
	if ($s == "user_onair") {
     	$onair = escape($_POST['user_onair'], true);
    	if(!userDj($data)){
    		$res['status'] = 99;
    		$res['msg'] = 'You need to be a DJ first to be on air.';
    	}
    if(isOnAir($data)){
		$c_time = time();
        $mysqli->query("UPDATE boom_users SET user_onair = '0' WHERE user_id = '{$data['user_id']}'");
        // Prepare the data for updating the end_time
        $update_data = array(
            "end_time" => time(),
            "status" => 'ended', // Optionally update the status to reflect the end of the broadcast
            "raised_hand_user_ids" => 'null',
            "mediaurl" => '',
            "start_time" => 0,
        );
        // Target the current DJ by user ID and room ID
        $db->where("dj_id", $data['user_id']);
        $db->where("room_id", $data['user_roomid']);
        // Attempt to update the end_time
        if ($db->update('dj', $update_data)) {
			//redisUpdateUser($user['user_id']);
			$res['status'] = 100;
			$res['msg'] = 'User off Air';
		}
    }else {
    	$mysqli->query("UPDATE boom_users SET user_onair = '1' WHERE user_id = '{$data['user_id']}'");
    	//redisUpdateUser($user['user_id']);
    	$res['status'] = 200;
    	$res['msg'] = 'User OnAir';
    }       
       echo fu_json_results($res);
    }
	if ($s == "accept_hand_raise") {
		$res['user_id'] = escape($_POST['user_id']);
		$res['current_dj_id'] = $data['user_id'];
		// Shutdown current air first
		$res['setOffair'] = setOffair($data);
		if ($res['setOffair'] !== true && $res['setOffair'] !== 1) {
			$res['error'] = 'Failed to set off air.';
			echo fu_json_results($res);
			exit();
		}
		// Check if the user who raised hand is DJ
		$res['makeDj'] = makeDj($res['user_id']);
		if ($res['makeDj'] !== true && $res['makeDj'] !== 1) {
			$res['error'] = 'Failed to make user DJ.';
			echo fu_json_results($res);
			exit();
		}
		// Set raised user on air
		$res['addOnair'] = addOnair($res['user_id']);
		if ($res['addOnair'] !== true && $res['addOnair'] !== 1) {
			$res['error'] = 'Failed to add user on air.';
			echo fu_json_results($res);
			exit();
		}
		// If all operations succeeded
		echo fu_json_results($res);
		exit();
	}
    if($s == "sharebox") {
        $html  = '';
         $html .= boomTemplate('box/share', $data);
          $res['html']   = $html;
        echo fu_json_results($res);
        exit();

    } 
	if ($s == "after_recovery_pass") {
		// Initialize response
		$res = ['success' => false, 'message' => 'Unknown error', 'code' => 0];
		// Sanitize inputs
		$pass = escape($_POST["user_new_password"]);
		$target_id = escape($_POST["target_id"]);
		// Fetch user details
		$user = userDetails($target_id);
		if (!$user) {
			$res['message'] = 'User not found';
			$res['code'] = 1; // User not found
			echo fu_json_results($res);
			$_SESSION["force_password_change"] = false;
			exit;
		}
		// Validate password
		if (!boomValidPassword($pass)) {
			$res['message'] = 'Invalid password';
			$res['code'] = 2; // Invalid password
			$_SESSION["force_password_change"] = true;
			echo fu_json_results($res);
			exit;
		}
		// Hash the password using PASSWORD_BCRYPT
		$new_pass = password_hash($pass, PASSWORD_BCRYPT);
		// Update database using prepared statement
		$stmt = $mysqli->prepare("UPDATE boom_users SET user_password = ? WHERE user_id = ?");
		if (!$stmt) {
			error_log("Prepare failed: " . $mysqli->error);
			$res['message'] = 'Database error';
			$res['code'] = 3; // Database prepare error
			echo fu_json_results($res);
			exit;
		}
		$stmt->bind_param("si", $new_pass, $user["user_id"]);
		if (!$stmt->execute()) {
			error_log("Execute failed: " . $stmt->error);
			$res['message'] = 'Database error';
			$res['code'] = 4; // Database execute error
			echo fu_json_results($res);
			exit;
		}
		$stmt->close();
		// Log the action
		boomConsole("pass_user", ["target" => $user["user_id"], "custom" => $user["user_name"]]);
		// Return success response
		$res['success'] = true;
		$res['code'] = 5; // Success
		$res['message'] = 'Password updated successfully';
		$_SESSION["force_password_change"] = false;
		// Set session flag to disable temp password alert
		// Update session or authentication cookie
		setBoomCookie($data["user_id"], $new_pass);
		echo fu_json_results($res);
		exit;
	}
}	
?> 