<?php
require __DIR__ . "./../function_admin.php";

$time = ceil(time());
$res = array(); 
if($f == "bot_speakers") {
	// Define the sendError function
	function sendError($message) {
		echo json_encode(['status' => 'error', 'message' => $message]);
		exit(); // Stop further script execution after sending an error response
	}	
    $res = array();
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $s === "del_bot" && boomLogged()) {
		header("Content-Type: application/json");
		// Validate and sanitize input
		$bot_id = filter_input(INPUT_POST, 'bot_id', FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1] // Ensures it's a positive integer
		]);
		if ($bot_id === false) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "error" => "Invalid bot ID."]);
			exit();
		}
		// Prepare delete operation
		$db->where('id', $bot_id);
		$del_query = $db->delete('bot_data');
		if ($del_query) {
			http_response_code(200);
			echo fu_json_results(["status" => 200, "message" => "Bot deleted successfully."]);
			exit();
		} else {
			// Log error for debugging (optional)
			error_log("Failed to delete bot with ID: $bot_id. DB error: " . $db->getLastError());
			http_response_code(500);
			echo fu_json_results(["status" => 500, "error" => "Failed to delete bot."]);
			exit();
		}
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $s === "admin_bot_byroom" && boomLogged()) {
		header("Content-Type: application/json");
		// Validate and sanitize input
		$group_id = filter_input(INPUT_POST, 'checkbot_room', FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1] // Ensures it's a positive integer
		]);
		if ($group_id === false) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "error" => "Invalid room ID."]);
			exit();
		}
		// Fetch bots assigned to the room
		$bots = bot_list_by_room($group_id);
		// Return the result
		echo fu_json_results(!empty($bots) ? $bots : emptyZone($lang['empty']));
		exit();
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $s === "admin_bot_info" && boomLogged()) {
		header("Content-Type: application/json");
		// Validate and sanitize input
		$bot_id = filter_input(INPUT_POST, 'bot_id', FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1] // Must be a positive integer
		]);
		$group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1] // Must be a positive integer
		]);
		// Check for invalid inputs
		if ($bot_id === false || $group_id === false) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "error" => "Invalid bot ID or group ID."]);
			exit();
		}
		// Fetch bot information
		$bot_info = bot_informatin($bot_id, $group_id);
		// Return response
		echo fu_json_results(!empty($bot_info) ? $bot_info : emptyZone($lang['empty']));
		exit();
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $s === "update_bot" && boomLogged()) {
		header("Content-Type: application/json");
			// Validate and sanitize required inputs
			$bot_id = filter_input(INPUT_POST, 'bot_id', FILTER_VALIDATE_INT, [
				'options' => ['min_range' => 1] // Must be a positive integer
			]);
			$bot_user_id = filter_input(INPUT_POST, 'bot_user_id', FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 1] // Must be a positive integer
		]);

		// Use FILTER_SANITIZE_FULL_SPECIAL_CHARS instead of FILTER_SANITIZE_STRING
		$fuse_bot_status = filter_input(INPUT_POST, 'fuse_bot_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$fuse_bot_type = filter_input(INPUT_POST, 'fuse_bot_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$fuse_bot_line = filter_input(INPUT_POST, 'fuse_bot_line', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$group_id = filter_input(INPUT_POST, 'group_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		// Check for missing or invalid inputs
		if ($bot_id === false || $bot_user_id === false || empty($fuse_bot_status) || empty($fuse_bot_type) || empty($fuse_bot_line)) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "error" => "Invalid or missing input data."]);
			exit();
		}
		// Set update time
		$time = time();
		// Data array for update query
		$data_bot_query = [
			"fuse_bot_status" => $fuse_bot_status,
			"fuse_bot_type" => $fuse_bot_type,
			"reply"          => $fuse_bot_line,
			"fuse_bot_time"  => $time,
			"group_id"  => $group_id,
		];
		// Update bot data
		$update_status = cl_update_bot_data($bot_id, $data_bot_query);
		// Construct response
		$response = [
			"bot_id"       => $bot_id,
			"bot_user_id"  => $bot_user_id,
			"bot_query"    => $update_status,
			"status"       => $update_status ? 200 : 150,
			"message"      => $update_status ? "Updated successfully" : "Something went wrong"
		];
		// Return response as JSON
		echo fu_json_results($response);
		exit();
	}
	if ($s === "add_bot_modal" && boomLogged()) {
		header("Content-Type: application/json");
		// Generate the modal content
		$content = boomTemplate('element/bots/add_bot');
		if ($content !== false && !empty($content)) {
			echo fu_json_results([
				"status"  => 200,
				"content" => $content
			]);
			exit();
		} else {
			http_response_code(500);
			echo fu_json_results([
				"status"  => 500,
				"content" => "Failed to load bot modal."
			]);
			exit();
		}
	}
	if ($s === "add_bot" && boomLogged()) {
		header("Content-Type: application/json");
		// Default response
		$res = ["status" => 400, "message" => "Invalid input data"];
		// Validate and sanitize input
		$post_data = filter_input_array(INPUT_POST, [
			"fuse_bot_id"     => FILTER_VALIDATE_INT,
			"group_id"        => FILTER_VALIDATE_INT,
			"fuse_bot_status" => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			"fuse_bot_type"   => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			"fuse_bot_line"   => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		]);
		// Ensure filter_input_array() returned valid data
		if (!$post_data || !$post_data["fuse_bot_id"] || !$post_data["group_id"]) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "message" => "Invalid bot or group ID."]);
			exit();
		}
		// Ensure non-empty values for bot status, type, and line
		if (empty($post_data["fuse_bot_status"]) || empty($post_data["fuse_bot_type"]) || empty($post_data["fuse_bot_line"])) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "message" => "Missing required fields."]);
			exit();
		}
		// Set bot creation time
		$time = time();
		// Prepare data for insertion
		$data_query = [
			"reply"           => $post_data["fuse_bot_line"],
			"fuse_bot_status" => $post_data["fuse_bot_status"],
			"fuse_bot_type"   => $post_data["fuse_bot_type"],
			"group_id"        => $post_data["group_id"],
			"user_id"         => $post_data["fuse_bot_id"],
			"fuse_bot_time"   => $time,
		];
		// Use parameterized queries to prevent SQL injection
		try {
			$bot_info = bot_data($post_data["fuse_bot_id"],$post_data["group_id"]);
			$insert_result = $db->insert("bot_data", $data_query);
			if ($insert_result) {
				http_response_code(201); // 201 Created
				echo fu_json_results([
					"status"  => 201,
					"message" => "Bot has been added successfully",
					"data"    => $data_query, // Optional: Return inserted data for debugging
				]);
			} else {
				http_response_code(500);
				echo fu_json_results([
					"status"  => 500,
					"message" => "Database error: Unable to add bot",
				]);
			}
		} catch (Exception $e) {
			// Log the exception for debugging
			error_log("Database error during bot addition: " . $e->getMessage());
			http_response_code(500);
			echo fu_json_results([
				"status"  => 500,
				"message" => "An unexpected error occurred while adding the bot.",
			]);
		}
		exit();
	}
	if ($s === "update_bot_set" && boomLogged()) {
		header("Content-Type: application/json");
		// Validate input
		if (!isset($_POST['bot_delay'], $_POST['allow_bot'])) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "message" => "Missing required parameters."]);
			exit();
		}
		// Ensure inputs are valid integers
		$bot_delay = filter_var($_POST['bot_delay'], FILTER_VALIDATE_INT);
		$allow_bot = filter_var($_POST['allow_bot'], FILTER_VALIDATE_INT);
		if ($bot_delay === false || $allow_bot === false) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "message" => "Invalid input data."]);
			exit();
		}
		// Prepare and execute the update query securely using a prepared statement
		$stmt = $mysqli->prepare("UPDATE boom_setting SET bot_delay = ?, allow_bot = ? WHERE id = 1");
		if (!$stmt) {
			http_response_code(500);
			echo fu_json_results(["status" => 500, "message" => "Database error: Failed to prepare statement."]);
			exit();
		}
		$stmt->bind_param("ii", $bot_delay, $allow_bot);
		$execute_success = $stmt->execute();
		if ($execute_success && $stmt->affected_rows > 0) {
			http_response_code(200);
			echo fu_json_results(["status" => 200, "message" => "Bot settings updated successfully."]);
		} else {
			http_response_code(500);
			echo fu_json_results(["status" => 500, "message" => "No changes made or update failed."]);
		}
		// Close the statement to free resources
		$stmt->close();
		exit();
	}
	if ($s === "allow_bot" && boomLogged()) {
		// Ensure Content-Type header for consistency
		header("Content-Type: application/json");
		// Validate input data
		if (!isset($_POST['allow_bot'])) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "message" => "Missing 'allow_bot' parameter."]);
			exit();
		}
		// Sanitize and validate input: ensure 'allow_bot' is either 0 or 1
		$allow_bot = filter_var($_POST['allow_bot'], FILTER_VALIDATE_INT, [
			"options" => ["min_range" => 0, "max_range" => 1]
		]);
		if ($allow_bot === false) {
			http_response_code(400);
			echo fu_json_results(["status" => 400, "message" => "'allow_bot' must be either 0 or 1."]);
			exit();
		}
		// Prepare the update query securely using a prepared statement
		$stmt = $mysqli->prepare("UPDATE boom_setting SET allow_bot = ? WHERE id = 1");
		if (!$stmt) {
			http_response_code(500);
			echo fu_json_results(["status" => 500, "message" => "Database error: Failed to prepare statement."]);
			exit();
		}
		// Bind parameter and execute the query
		$stmt->bind_param("i", $allow_bot);
		$execute_success = $stmt->execute();
		if ($execute_success && $stmt->affected_rows > 0) {
			http_response_code(200);
			echo fu_json_results(["status" => 200, "message" => "Bot setting updated successfully."]);
		} else {
			http_response_code(500);
			echo fu_json_results(["status" => 500, "message" => "Failed to update the bot setting."]);
		}
		// Close the statement
		$stmt->close();
		exit();
	}
	if($s === "speak" && $data['allow_bot'] == 1) {
			$post_time = date("H:i", $time);
			$bot_time = $data['bot_time'] + $data['bot_delay'];
			if (isset($data['user_roomid']) && is_numeric($data['user_roomid'])) {
				$group_id = $data['user_roomid'];
			} else {
				sendError('User room ID is not set or invalid');
			}
			if(boomLogged() && $data['allow_bot'] == 1) {
			if ($time > $bot_time) {
				$stmt = $mysqli->prepare("SELECT * FROM `boom_bot_data` WHERE group_id = ? AND `id` > 0");
				$stmt->bind_param("i", $group_id);
				$stmt->execute();
				$ckbdata = $stmt->get_result();
				if ($ckbdata->num_rows > 0) {
					$bot_row = $ckbdata->fetch_array(MYSQLI_ASSOC);
					if ($bot_row['fuse_bot_type'] == 1) {
						$stmt2 = $mysqli->prepare("SELECT * FROM `boom_bot_data` WHERE `view` != 1 AND group_id = ? ORDER BY `id` ASC LIMIT 1");
						$stmt2->bind_param("i", $group_id);
						$stmt2->execute();
						$result2 = $stmt2->get_result();
						if ($result2->num_rows > 0) {
							$prepare = $result2->fetch_array(MYSQLI_BOTH);
							$this_ads_bot = $prepare['id'];
							$bot_info2 = fuse_user_data($prepare['user_id']);
							$mysqli->query("UPDATE boom_bot_data SET view = 1 WHERE id = '$this_ads_bot' AND group_id = '$group_id'");
							$botsay = htmlspecialchars($prepare['reply'], ENT_QUOTES, 'UTF-8');
							$mysqli->query("UPDATE boom_setting SET bot_time = '$time' WHERE id = 1");
							$content = '<div class="' . $bot_info2['bccolor'] . ' ' . $bot_info2['bcbold'] . ' ' . $bot_info2['bcfont'] . '">' . $botsay . '</div>';
							botPostChat($prepare['user_id'], $prepare['group_id'], $content);
						} else {
							$mysqli->query("UPDATE boom_bot_data SET view = 0 WHERE group_id = '$group_id' AND `id` > 0");
							sendError('No available bot data');
						}
					} else {
						$stmt6 = $mysqli->prepare("SELECT reply, user_id FROM boom_bot_data WHERE group_id = ? AND `id` > 0 ORDER BY RAND() LIMIT 1");
						$stmt6->bind_param("i", $group_id);
						$stmt6->execute();
						$result6 = $stmt6->get_result();
						if ($result6->num_rows > 0) {
							$prepare_result = $result6->fetch_array(MYSQLI_BOTH);
							$botsay = htmlspecialchars($prepare_result['reply'], ENT_QUOTES, 'UTF-8');
							$mysqli->query("UPDATE boom_setting SET bot_time = '$time' WHERE id = 1");

							$bot_info3 = fuse_user_data($prepare_result['user_id']);
							$content = '<div class="' . $bot_info3['bccolor'] . ' ' . $bot_info3['bcbold'] . ' ' . $bot_info3['bcfont'] . '">' . $botsay . '</div>';
							botPostChat($prepare_result['user_id'], $prepare_result['group_id'], $content);
						} else {
							sendError('No random response found');
						}
					}
				} else {
					sendError('No data found');
				}
			} else {
				sendError('Current time is not greater than bot time');
			}
		} else {
			sendError('User access is not valid or bot status is off');
		}

		echo fu_json_results($res);
		exit();
	}

}	
?>    