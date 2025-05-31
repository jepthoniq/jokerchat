<?php
//require('./../config_session.php');

// Function to handle actions
function handleAction($action, $target) {
    global $mysqli; // Ensure access to $mysqli
    $action = escape($action);
    $target = escape($target);
    switch ($action) {
        case 'unban':
            return unbanAccount($target);
        case 'unmute':
            return unmuteAccount($target);
        case 'main_unmute':
            return unmuteAccountMain($target);  
        case 'private_unmute':
            return unmuteAccountPrivate($target);           
        case 'unghost':
            return unghostAccount($target);     
        case 'room_unmute':
            return unmuteRoom($target);
        case 'muted':
            return unmuteAccount($target);
        case 'banned':
            return unbanAccount($target);
        case 'room_unblock':
            return unblockRoom($target);
        case 'kicked':
        case 'unkick':
            return unkickAccount($target);
        default:
            return 0;
    }
}
function maintenanceStatus(){
	global $mysqli, $data;
	if($data['maint_mode'] == 0){
		return 1;
	}
	return 0;
}
function kickStatus(){
    global $mysqli, $data;
    if(!isKicked($data)){
        if($data['user_kick'] > 0){
            $stmt = $mysqli->prepare("UPDATE boom_users SET user_kick = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $data['user_id']);
            $stmt->execute();
			//redisUpdateUser($data['user_id']);
        }
        return 1;
    }
    return 0;
}

if ($f == 'action') {
	if($s == 'check_kick') {
		// Check if user is kicked
		if(isset($_POST['check_kick'])){
			echo kickStatus();
			die();
		}		
	}
	if($s == 'check_maintenance') {
		// Check if maintenance mode is enabled
		if (isset($_POST['check_maintenance'])) {
			echo maintenanceStatus();
			die();
		}
	}
	if($s == 'take_action') {
		// Handle action requests
		if (isset($_POST['take_action'], $_POST['target'])) {
				$action = escape($_POST['take_action']);
				$target = escape($_POST['target']);
				echo handleAction($action, $target);
				die();
		}
	}
	if ($s == 'kick') {
		// Validate required parameters
		if (!isset($_POST['kick'], $_POST['reason'], $_POST['delay'])) {
			echo fu_json_results(['status' => 0, 'error' => 'Missing parameters']);
			die();
		}
		// Process and validate inputs
		$target = (int)$_POST['kick'];
		$reason = escape($_POST['reason']);
		$delay = (int)$_POST['delay'];
		if (!validKick($delay)) {
			echo fu_json_results(['status' => 0, 'error' => 'Invalid kick duration']);
			die();
		}
		// Process kick
		$result = kickAccount($target, $delay, $reason);
		// Return JSON response
		echo is_array($result) ? fu_json_results($result) : $result;
		die();
	}
	if($s == 'room_mute') {
		// Handle room mute requests
		if (isset($_POST['room_mute'], $_POST['reason'], $_POST['delay'])) {
			$target = escape($_POST['room_mute']);
			$reason = escape($_POST['reason']);
			$delay = escape($_POST['delay']);
			echo muteRoom($target, $delay, $reason);
			die();
		}		
	}
	if($s == 'room_block') {
		// Handle room block requests
		if(isset($_POST['room_block'], $_POST['reason'], $_POST['delay'])){
			$target = escape($_POST['room_block'], true);
			$reason = escape($_POST['reason']);
			$delay = escape($_POST['delay'], true);
			echo blockRoom($target, $delay, $reason);
			die();
		}	
	}
	if ($s == 'mute') {
		// Validate required parameters
		if (!isset($_POST['mute'], $_POST['reason'], $_POST['delay'])) {
			echo fu_json_results(['status' => 0, 'error' => 'Missing parameters']);
			die();
		}
		// Sanitize inputs
		$target = (int)$_POST['mute']; // Force integer for user ID
		$reason = escape($_POST['reason']);
		$delay = (int)$_POST['delay']; // Ensure numeric delay
		// Validate delay range (example: 1 min to 1440 mins/24h)
		if ($delay < 1 || $delay > 1440) {
			echo fu_json_results(['status' => 0, 'error' => 'Invalid mute duration']);
			die();
		}
		$result = muteAccount($target, $delay, $reason);
		echo fu_json_results($result);
		die();
	}
	if ($s == 'main_mute') {
		// Validate required parameters
		$required = ['main_mute', 'reason', 'delay'];
		foreach ($required as $param) {
			if (!isset($_POST[$param])) {
				echo fu_json_results(['status' => 0, 'error' => "Missing $param"]);
				die();
			}
		}
		// Sanitize inputs
		$target = (int)$_POST['main_mute'];
		$reason = escape($_POST['reason']);
		$delay = (int)$_POST['delay'];
		// Validate delay range (1 min to 30 days)
		if ($delay < 1 || $delay > 43200) {
			echo fu_json_results(['status' => 0, 'error' => 'Invalid mute duration (1-43200 mins)']);
			die();
		}
		// Process mute
		$result = muteAccountMain($target, $delay, $reason);
		// Return JSON response
		echo is_array($result) ? fu_json_results($result) : $result;
		die();
	}
	if ($s == 'private_mute') {
		// Validate required parameters
		if (!isset($_POST['private_mute'], $_POST['reason'], $_POST['delay'])) {
			echo fu_json_results([
				'status' => 0,
				'code' => 400,
				'error' => $lang['missing_params'] ?? 'Missing required parameters'
			]);
			exit;
		}
		// Process inputs
		$target = (int)$_POST['private_mute'];
		$reason = escape($_POST['reason']);
		$delay = min((int)$_POST['delay'], 43200); // Max 30 days
		// Validate delay
		if ($delay < 1) {
			echo fu_json_results([
				'status' => 0,
				'code' => 400,
				'error' => $lang['invalid_duration'] ?? 'Duration must be at least 1 minute'
			]);
			exit;
		}
		// Process mute and return JSON response
		$result = muteAccountPrivate($target, $delay, $reason);
		echo fu_json_results($result);
		exit;
	}
	if ($s == 'ghost') {
		if (!isset($_POST['ghost'], $_POST['reason'], $_POST['delay'])) {
			echo fu_json_results(['status' => 0, 'error' => 'Missing parameters']);
			exit;
		}
		$result = ghostAccount(
			(int)$_POST['ghost'],
			(int)$_POST['delay'],
			escape($_POST['reason'])
		);
		
		echo is_array($result) ? fu_json_results($result) : $result;
		exit;
	}
	if($s == 'ban') {	
		// Handle ban requests
		if (isset($_POST['ban'], $_POST['reason'])) {
			$target = escape($_POST['ban']);
			$reason = escape($_POST['reason']);
			echo banAccount($target, $reason);
			die();
		}	
	}
	if($s == 'warn') {
		if(isset($_POST['warn'], $_POST['reason'])){
			$target = escape($_POST['warn'], true);
			$reason = escape($_POST['reason']);
			echo warnAccount($target, $reason);
			die();
		}		
	}
	if($s == 'remove_room_staff') {
		// Remove room staff
		if (isset($_POST['remove_room_staff'], $_POST['target'])) {
			$target = escape($_POST['target']);
			echo removeRoomStaff($target);
			die();
		}
	}	
}

?>
