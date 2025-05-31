<?php


require __DIR__ . "./../config_session.php";
if (isset($_POST["unset_report"]) && isset($_POST["type"])) {
    echo unsetreport();
    exit;
}
if (isset($_POST["remove_report"]) && isset($_POST["type"]) && isset($_POST["report"])) {
    $type = escape($_POST["type"]);
    if ($type == 1) {
        echo removechatreport();
        exit;
    }
    if ($type == 2) {
        echo removewallreport();
        exit;
    }
    if ($type == 3) {
        echo removeprivatereport();
        exit;
    }
    exit;
}
if (isset($_POST["send_report"]) && isset($_POST["type"]) && isset($_POST["report"]) && isset($_POST["reason"])) {
    $type = escape($_POST["type"]);
    if ($type == 1) {
        echo makechatreport();
        exit;
    }
    if ($type == 2) {
        echo makewallreport();
        exit;
    }
    if ($type == 3) {
        echo makeprivatereport();
        exit;
    }
    if ($type == 4) {
        echo makeprofilereport();
        exit;
    }
    exit;
}

// Unset a report
function unsetReport() {
    global $mysqli,$data;
    $report = escape($_POST["unset_report"]);
    $type = escape($_POST["type"]);
    if (!canManageReport()) {
        return 0;
    }
    $query = "SELECT * FROM boom_report WHERE report_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $report);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $rep = $result->fetch_assoc();
        if (!selfManageReport($rep["report_target"])) {
            return 0;
        }
    }
    $query = "DELETE FROM boom_report WHERE report_id = ? AND report_type = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $report, $type);
    $stmt->execute();
    updateStaffNotify();
    return 1;
}

// Remove a chat report
function removeChatReport() {
    global $mysqli,$data;
    $report = escape($_POST["report"]);
    if (!canManageReport()) {
        return 0;
    }
    $query = "SELECT boom_report.*, boom_rooms.* FROM boom_report 
              JOIN boom_rooms ON boom_rooms.room_id = boom_report.report_room
              WHERE report_id = ? AND report_type = 1 LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $report);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $rep = $result->fetch_assoc();
        if (!selfManageReport($rep["report_target"])) {
            return 0;
        }
        // Delete the chat post
        $query = "DELETE FROM boom_chat WHERE post_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $rep["report_post"]);
        $stmt->execute();
        // Update room deletion info
		$current_time = time();
        if (!delExpired($rep["rltime"])) {
            $query = "UPDATE boom_rooms SET rldelete = CONCAT(rldelete, ?, rltime = ?) WHERE room_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sii", $rep["report_post"], $current_time, $rep["report_room"]);
        } else {
            $query = "UPDATE boom_rooms SET rldelete = ?, rltime = ? WHERE room_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sii", $rep["report_post"], $current_time, $rep["report_room"]);
        }
        $stmt->execute();
    }
    $query = "DELETE FROM boom_report WHERE report_id = ? AND report_type = 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $report);
    $stmt->execute();

    updateStaffNotify();
    return 1;
}

// Remove a wall report
function removeWallReport() {
     global $mysqli,$data;
    $report = escape($_POST["report"]);
    if (!canManageReport()) {
        return 0;
    }
    $query = "SELECT * FROM boom_report WHERE report_id = ? AND report_type = 2 LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $report);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $rep = $result->fetch_assoc();
        if (!selfManageReport($rep["report_target"])) {
            return 0;
        }
        $query = "DELETE FROM boom_post WHERE post_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $rep["report_post"]);
        $stmt->execute();

        $query = "DELETE FROM boom_post_reply WHERE parent_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $rep["report_post"]);
        $stmt->execute();

        $query = "DELETE FROM boom_notification WHERE notify_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $rep["report_post"]);
        $stmt->execute();

        $query = "DELETE FROM boom_post_like WHERE like_post = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $rep["report_post"]);
        $stmt->execute();
    }

    $query = "DELETE FROM boom_report WHERE report_id = ? AND report_type = 2";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $report);
    $stmt->execute();

    updateStaffNotify();
    return 1;
}

// Remove a private report
function removePrivateReport() {
    $report = escape($_POST["report"]);
    if (!canManageReport()) {
        return 0;
    }
    $query = "SELECT * FROM boom_report WHERE report_id = ? AND report_type = 3 LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $report);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $rep = $result->fetch_assoc();
        if (!selfManageReport($rep["report_target"])) {
            return 0;
        }
        $query = "DELETE FROM boom_private WHERE hunter = ? AND target = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $rep["report_user"], $rep["report_target"]);
        $stmt->execute();

        $query = "DELETE FROM boom_private WHERE hunter = ? AND target = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $rep["report_target"], $rep["report_user"]);
        $stmt->execute();
    }

    $query = "DELETE FROM boom_report WHERE report_id = ? AND report_type = 3";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $report);
    $stmt->execute();

    updateStaffNotify();
    return 1;
}


function makeWallReport() {
    global $mysqli, $data;
    // Check if the user can send a report
    if (!canSendReport()) {
        return 3;
    }
    // Escape user inputs
    $post = escape($_POST["report"]);
    $reason = escape($_POST["reason"]);
    // Validate the reason for the report
    if (!validReport($reason)) {
        return 5;
    }
    // Check if the user can perform the action on the post
    if (!canPostAction($post)) {
        return 0;
    }
    // Check if the post is already reported
    $query = "SELECT * FROM boom_report WHERE report_post = ? AND report_type = 2";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $post);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return 1; // Post is already reported
    }

    // Verify if the post exists
    $query = "SELECT post_id, post_user FROM boom_post WHERE post_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $post);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $wall = $result->fetch_assoc();
        // Insert the new report
        $query = "INSERT INTO boom_report (report_type, report_user, report_target, report_post, report_reason, report_date, report_room) 
                  VALUES (2, ?, ?, ?, ?, ?, 0)";
        $stmt = $mysqli->prepare($query);
		$current_time = time();
        $stmt->bind_param("iiisi", $data["user_id"], $wall["post_user"], $post, $reason, $current_time);
        $stmt->execute();
        // Notify staff
        updateStaffNotify();
        return 1;
    }

    return 0; // Post does not exist
}


function makeChatReport() {
    global $mysqli, $data;

    // Check if the user can send a report
    if (!canSendReport()) {
        return 3;
    }

    // Escape user inputs
    $post = escape($_POST["report"]);
    $reason = escape($_POST["reason"]);

    // Validate the reason for the report
    if (!validReport($reason)) {
        return 5;
    }

    // Check if the post is already reported
    $query = "SELECT * FROM boom_report WHERE report_post = ? AND report_type = 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $post);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return 1; // Post is already reported
    }

    // Log information for the post
    $log = logInfo($post);
	if (!empty($log)) {
		// Get current timestamp and store it in a variable
		$current_time = time();		
		// Prepare the SQL query (ensure it's safe)
		$query = "INSERT INTO boom_report (report_type, report_user, report_target, report_post, report_reason, report_date, report_room) 
				  VALUES (1, ?, ?, ?, ?, ?, ?)";
		// Prepare the statement
		$stmt = $mysqli->prepare($query);
		// Check if the statement was prepared successfully
		if (!$stmt) {
			die("MySQL prepare error: " . $mysqli->error);
		}

		// Bind the parameters, ensuring that all variables are passed by reference
		$stmt->bind_param(
			"iiiiii", 
			$data["user_id"],       // Integer
			$log["user_id"],        // Integer
			$post,                  // Integer (should be the ID of the post, assumed to be a number)
			$reason,                // Integer (or string, depending on DB design)
			$current_time,          // Integer (timestamp)
			$data["user_roomid"]    // Integer
		);
		// Execute the statement
		$stmt->execute();
		// Notify staff
		updateStaffNotify();
		return 1;
	}

    return 0; // Post does not exist
}
function makeProfileReport() {
    global $mysqli, $data;
    // Check if the user can send a report
    if (!canSendReport()) {
        return 3;
    }
    // Escape the inputs for safety
    $id = escape($_POST["report"]);
    $reason = escape($_POST["reason"]);
    // Prevent the user from reporting themselves
    if (mySelf($id)) {
        return 3;
    }
    // Validate the reason for the report
    if (!validReport($reason)) {
        return 5;
    }
    // Check if a report for this user already exists
    $query = "SELECT * FROM boom_report WHERE report_target = ? AND report_type = 4";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);  // Only bind $id since it's an integer
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return 1; // Report already exists for this user
    }
    // Get the user details for the target user
    $user = userDetails($id);
    if (empty($user)) {
        return 0; // User doesn't exist
    }
    // Check if the user is a bot
    if (isBot($user)) {
        return 3; // Prevent reporting bots
    }
    // Get the current time
    $current_time = time();
    // Insert the new profile report into the database
    $query = "INSERT INTO boom_report (report_type, report_user, report_target, report_reason, report_date) 
              VALUES (4, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    // Bind the parameters correctly: user_id, user_id (target), reason (string), and current_time (integer)
    $stmt->bind_param("iiis", $data["user_id"], $user["user_id"], $reason, $current_time);
    $stmt->execute();
    // Notify staff about the new report
    updateStaffNotify();
    return 1; // Successfully made the report
}



function makePrivateReport() {
    global $mysqli, $data, $cody;
    // Escape input data
    $target = escape($_POST["report"]);
    $reason = escape($_POST["reason"]);
    // Check if the user can send a report
    if (!canSendReport()) {
        return 3;
    }
    // Get the details of the target user
    $user = userDetails($target);
    if (empty($user)) {
        return 0; // User does not exist
    }
    // Validate the reason for the report
    if (!validReport($reason)) {
        return 5; // Invalid report reason
    }
    // Check if the user has a private conversation with the target
    $query = "SELECT hunter FROM boom_private WHERE (hunter = ? AND target = ?) OR (hunter = ? AND target = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiii", $data["user_id"], $target, $target, $data["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows < 1) {
        return 76; // No private conversation with the target
    }
    // Check if a report for the target already exists
    $query = "SELECT * FROM boom_report WHERE report_user = ? AND report_target = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $data["user_id"], $target);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return 1; // A report already exists
    }
    // Get the current time
    $current_time = time();
    // Insert the new private report
    $query = "INSERT INTO boom_report (report_type, report_user, report_target, report_reason, report_date, report_room) 
              VALUES (3, ?, ?, ?, ?, 0)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiisi", $data["user_id"], $target, $reason, $current_time);
    $stmt->execute();
    // Notify staff about the new report
    updateStaffNotify();
    return 1; // Successfully made the report
}


?>