<?php

require __DIR__ . "./../config_session.php";

if (isset($_POST["del_post"]) && isset($_POST["type"])) {
    echo chatdeletepost();
    exit;
}
if (isset($_POST["private_delete"])) {
    echo privatedeletion();
    exit;
}
if (isset($_POST["clear_private"])) {
    echo privateclearing();
    exit;
}
if (isset($_POST["del_private"]) && isset($_POST["target"])) {
    echo deleteprivatehistory();
    exit;
}
function deletePrivateHistory(){
    global $mysqli,$data,$cody;
    if (!canDeletePrivate()) {
        return 0;
    }
    $target = escape($_POST["target"]);
    $query = "SELECT * FROM boom_report WHERE report_user = ? AND report_target = ? OR report_user = ? AND report_target = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("iiii", $data["user_id"], $target, $target, $data["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $priv = $result->fetch_assoc();
            if (!selfManageReport($priv["report_target"])) {
                return 0;
            }
        }
        $stmt->close();
    } else {
        error_log("Error preparing query: " . $mysqli->error);
        return 0;
    }

    $deleteQuery = "DELETE FROM boom_report WHERE report_user = ? AND report_target = ? OR report_user = ? AND report_target = ?";
    if ($stmt = $mysqli->prepare($deleteQuery)) {
        $stmt->bind_param("iiii", $data["user_id"], $target, $target, $data["user_id"]);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing delete query: " . $mysqli->error);
    }

    updateStaffNotify();
    clearPrivate($data["user_id"], $target);
    return 1;
}

function chatDeletePost(){
    global $mysqli, $data;
    // Escape input
    $post = escape($_POST["del_post"]);
    $type = escape($_POST["type"]);
    // Get log information
    $log = logInfo($post);
    if ($log === null || empty($log)) { // Check if $log is null or empty
        return "";
    }
    // Permission check
    if (!canDeleteLog() && !canDeleteRoomLog() && !canDeleteSelfLog($log)) {
        return "";
    }
    // Get room information
    $room = roomInfo($data["user_roomid"]);
    if ($room === null || empty($room)) { // Check if $room is null or empty
        return "";
    }
    // Delete post query
    $deletePostQuery = "DELETE FROM boom_chat WHERE post_id = ? AND post_roomid = ?";
    if ($stmt = $mysqli->prepare($deletePostQuery)) {
        $stmt->bind_param("ii", $post, $data["user_roomid"]);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing delete post query: " . $mysqli->error);
        return "";
    }
    // Delete report query
    $deleteReportQuery = "DELETE FROM boom_report WHERE report_post = ? AND report_type = '1' AND report_room = ?";
    if ($stmt = $mysqli->prepare($deleteReportQuery)) {
        $stmt->bind_param("ii", $post, $data["user_roomid"]);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing delete report query: " . $mysqli->error);
    }
    // Update affected rows
    if ($mysqli->affected_rows > 0) {
        updateStaffNotify();
    }
	$current_time= time();
    // Update room query
    $updateRoomQuery = "UPDATE boom_rooms SET rldelete = IF(rldelete IS NULL, ?, CONCAT(rldelete, ',', ?)), rltime = ? WHERE room_id = ?";
    if ($stmt = $mysqli->prepare($updateRoomQuery)) {
        $stmt->bind_param("isii", $post, $post, $current_time, $data["user_roomid"]);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing update room query: " . $mysqli->error);
    }
    // Log deletion action
    boomConsole(
        "delete_log",
        [
            "target" => $log["user_id"],
            "room" => $data["user_roomid"],
            "reason" => strip_tags($log["post_message"])
        ]
    );
    // Remove related files
    removeRelatedFile($post, "chat");
}
function privateDeletion(){
	global $mysqli,$data;
    $item = escape($_POST["private_delete"]);
    $query = "UPDATE boom_private SET status = 3, view = 1 WHERE hunter = ? AND target = ? AND status < 3";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ii", $item, $data["user_id"]);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing private deletion query: " . $mysqli->error);
    }

    return 1;
}

function privateClearing(){
global $mysqli,$data;
    $query = "UPDATE boom_private SET status = 3, view = 1 WHERE target = ? AND status < 3";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $data["user_id"]);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing private clearing query: " . $mysqli->error);
    }

    return 1;
}


?>
