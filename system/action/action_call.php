<?php
if ($f == "action_call") {
	require_once(__DIR__ . '/../webrtc/voice_call/function_call.php');
		function generateRoomIdentifier() {
			// Generate a random string for the room ID
			return bin2hex(random_bytes(8)); // Generates a 16-character hexadecimal string
		}
		// Function to generate a secure token
		function generateCallToken() {
			return bin2hex(random_bytes(16)); // Generates a 32-character hexadecimal string
		}
		$res = [];
		if($_POST['s'] == 'init_call') {
			global $data, $mysqli;
			// Sanitize input
			$hunter = escape($data['user_id'], true); // Current user
			$target = escape($_POST['init_call'], true);
			$type = escape($_POST['call_type'], true);
			// Generate room and token
			$room = generateRoomIdentifier(); // Generate a unique room ID
			$res['token'] =generateCallToken(); // Generate a secure token in case not agora
			// Validate permissions
			$canInitCall = canInitCall($type);
			if ($canInitCall['code'] != 200) {
				echo fu_json_results([
					'code' => $canInitCall['code'],
					'message' => $canInitCall['error'],
				]);
				die();
			}
			if($data['call_server_type']=="agora"){
				$room_script = $room;
				require __DIR__ . '/../../vendor/AgoraDynamicKey/src/RtcTokenBuilder.php';
				$appID = $data['call_appid'];
				$appCertificate = $data['call_secret'];
				$uid = 0;
				$uidStr = "0";
				$role = RtcTokenBuilder::RoleAttendee;
				$expireTimeInSeconds = 36000000;
				$currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
				$privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
				$res['token'] = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $room_script, $uid, $role, $privilegeExpiredTs);
			}			
			$current_time = time();	
			// Insert into database using prepared statements
			$stmt = $mysqli->prepare("
				INSERT INTO boom_call (call_hunter, call_target, call_type, call_time, call_room, call_token)
				VALUES (?, ?, ?, ?, ?, ?)
			");
			$stmt->bind_param("iiisss", $hunter, $target, $type, $current_time, $room, $res['token']);
			if (!$stmt->execute()) {
				error_log("Database error: " . $stmt->error);
				echo fu_json_results(['code' => 99, 'message' => 'Failed to initialize call']);
				die();
			}
			// Get the newly inserted call ID
			$call_id = $mysqli->insert_id;
			// Update target user's `ucall` count
			$t_info = fuse_user_data($target);
			cl_update_user_data($t_info['user_id'], [
				'ucall' => $t_info['ucall'] + 1,
			]);
			// Prepare response data
			$res = [
				'call_id' => $call_id,
				'user_name' => $t_info['user_name'],
				'user_tumb' => $t_info['user_tumb'],
			];
			// Return response
			echo fu_json_results([
				'code' => 200,
				'content' => [
					'call_id' => $call_id,
					'call_hunter' => $hunter,
					'call_target' => $target,
					'call_type' => $type,
					'call_time' => time(),
					'call_room' => $room,
					'call_token' => $token,
				],
				'data' => boomTemplate('element/call/call_init', $res),
			]);
		}
		if ($_POST['s'] == 'accept_call') {
			$call_id = escape($_POST['accept_call'], true);
			// Update call status to "accepted"
			// Fetch updated call details
			$call = callDetails($call_id);
			$RES['accept_call'] = acceptCall($call);
			echo fu_json_results([
				'code' => 1,
				'data' => boomTemplate('element/call/call_reciver', $call),
			]);
		}
		if ($_POST['s'] == 'decline_call') {
			if (isset($_POST['decline_call'])) {
				$call_id = escape($_POST['decline_call'], true);
				// End the call with a reason
				$call = callDetails($call_id);
				if (!$call) {
					// Call not found or expired
					echo fu_json_results(['code' => 99]);
					die();
				}
				endCall(callDetails($call_id), 99); // 99 = declined
				echo fu_json_results(['code' => 1]);// Success
			}
		}
		if ($_POST['s'] == 'cancel_call') {
			if (isset($_POST['cancel_call'])) {
				$call_id = escape($_POST['cancel_call'], true);
				// Fetch call details to ensure it exists and belongs to the current user
				$call = callDetails($call_id);
				if (!$call) {
					echo fu_json_results(['code' => 99,'error' => 'Call deleted']); // Call not found
					die();
				}
				// Update the call status to "ended" (2)
				global $mysqli;
				$mysqli->query("UPDATE boom_call SET call_status = '2', call_reason = '2' WHERE call_id = '$call_id'");
				// Return success response
				echo fu_json_results(['code' => 1]);
			} else {
				echo fu_json_results(['code' => 0]); // Invalid request
			}
		}
		if ($_POST['s'] == 'update_call') {
			global $mysqli;
			// Sanitize input
			$call_id = escape($_POST['update_call']);
			$call = callDetails($call_id);
			if (!$call) {
				echo fu_json_results(['code' => 99, 'error' => 'Call not found']);
				die();
			}
			// Log call details for debugging
			error_log("Update Call: Call ID: $call_id, Call Status: {$call['call_status']}, Call Active: {$call['call_active']}");
			// Check if the call is active
			if (!callActive($call)) {
				error_log("Call ID: $call_id is no longer active");
				echo fu_json_results(['code' => 99, 'error' => 'Call is inactive']);
				die();
			}
			// Check if the call has timed out
			if (callTimeout($call)) {
				error_log("Call ID: $call_id has timed out");
				echo fu_json_results(['code' => 99, 'error' => 'Call timed out']);
				die();
			}

			// Handle call status
			switch ($call['call_status']) {
				case 1: // Call accepted
					echo fu_json_results([
						'code' => 1,
						'call_id' => $call_id,
						'data' => boomTemplate('element/call/call_reciver', $call),
						'message' => 'Call accepted',
					]);
					break;
				case 2: // Call declined or ended
					echo fu_json_results(['code' => 99, 'error' => 'Call declined or ended']);
					break;
				default: // Call pending
					echo fu_json_results(['code' => 0, 'error' => 'Call is pending']);
					break;
			}
		}
		if ($_POST['s'] == 'update_incoming_call') {
			$call_id = escape($_POST['update_incoming_call'], true);
			// Fetch call details from the database
			$call = callDetails($call_id);
			  if (!$call) {
				// Call not found (expired or deleted  or timed out)
				echo fu_json_results(['code' => 99,'error' => 'Call deleted']); // Indicate that the call no longer exists
				die();
			}
			if (callTimeout($call)) {
				// Call not found (expired or deleted  or timed out)
				echo fu_json_results(['code' => 99,'error' => 'No Answer']); // Indicate that the call no longer exists
				die();
			}
			// Check if the call has expired
			$delay = time() - callDelay(); // Calculate the time delay (e.g., 20 seconds)
			if ($call['call_time'] < $delay) {
				// Call expired
				echo fu_json_results(['code' => 99,'error' => 'Call Expired']);
				die();
			}
			// Check the call status
			if ($call['call_status'] == 1) {
				// Call accepted
					echo fu_json_results([ 'code' => 1, 'data' => $call, ]);
			} else if ($call['call_status'] == 2) {
				// Call declined or ended
				echo fu_json_results(['code' => 99,'error' => 'Call declined or ended']);
			} else {
				// Call still pending
				echo fu_json_results(['code' => 0]);
			}
		}
		if($_POST['s'] == 'check_call') {
			$check_call =  escape($_POST['check_call']);
			$call = incomingCallDetails($data['user_id']);
			$res['code'] = 0;
			$income_temp ='';
			echo fu_json_results([
					'code' => 1,
					'call_id' => $call['call_id'],
					'data' => array(
					'call_id' => $call['call_id'],
					'call_avatar' => myAvatar($call['user_tumb']),
					'call_type' => callType($call['call_type']),
					'call_userid' => $call['user_id'],
					'call_username' => $call['user_name'],
					'template' => boomTemplate('element/call/call_incoming', $call),
					),
				]);				
		}
		if($_POST['s'] == 'call_user') {
			$call_user =  escape($_POST['call_user']);
			$user_info = fuse_user_data($call_user);
			echo fu_json_results(['user_name' => $user_info['user_name']]);
		}
		if ($_POST['s'] == 'upgrade_call') {
			$call_id = escape($_POST['upgrade_call']);
			// Fetch call details from the database
			$call = callDetails($call_id);
			if (!$call) {
				// Call not found
				echo fu_json_results(['code' => 99, 'message' => 'Call not found']);
				die();
			}
			// Check if the call has expired
			if (callExpired($call)) {
				echo fu_json_results(['code' => 99, 'message' => 'Call has expired']);
				die();
			}
			// Calculate the remaining time until the call ends
			global $data;
			// If the call is still valid, return success with remaining time
			$expiration_threshold = calMinutes($data['call_max']);
			$time_left = max(0, $call['call_time'] + ($data['call_max'] * 60) - time()); // Remaining time in seconds
			//update active call time and other actions inside this functions
			$update_active_time = update_active_call($call);
			echo fu_json_results([
				'code' => 1,
				//'call' => $call,
				'message' => 'Call is still valid',
				'data' => [
					'call_id' => $call_id,
					'time_left' => $time_left, // Remaining time in seconds
				],
			]);
		}
		if($_POST['s'] == 'reload_call') {
			require_once(__DIR__ . '/../webrtc/voice_call/function_call.php');
			$reload_call = escape($_POST['reload_call'], true);
			// Check if the call has been accepted or ended
			// Return response
			echo fu_json_results([
				'code' => 1,
				'content' => listAdminCall(),
			]);			
		}	
		if($_POST['s'] == 'admin_cancel') {
			$admin_cancel = escape($_POST['admin_cancel'], true);
			function adminCancelCall(){
				global $mysqli, $setting, $data;
				$id = escape($_POST['admin_cancel'], true);
				$call = callDetails($id);
				if(empty($call)){
					echo fu_json_results([
						'code' => 0,
					]);						
				}
				endCall($call, 8);
				$call['call_status'] = 2;
				return boomTemplate('element/admin_call', $call);
			}	
			// Check if the call has been accepted or ended
			echo fu_json_results([
				'code' => 1,
				'content' => adminCancelCall(),
			]);				
		}		
}
?>