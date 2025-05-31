<?php
require __DIR__ . "./../config_session.php";
require BOOM_PATH . "/system/language/" . $data["user_language"] . "/history.php";

// Check if the get_history POST parameter is set and sanitize it
if (isset($_POST["get_history"]) && is_numeric($_POST["get_history"])) {
    echo userHistory();
    exit;
}

// Check if the remove_history POST parameter is set and sanitize it
if (isset($_POST["remove_history"]) && is_numeric($_POST["remove_history"]) && isset($_POST["target"]) && is_numeric($_POST["target"])) {
    echo removeHistory();
    exit;
}

function renderHistoryText(array $history, array $hlang = []) {
    // Input validation
    if (empty($history['htype'])) return '❌ Invalid entry';
    
    // Get template or default
    $actionType = $history['htype'];
    $ctext = $hlang[$actionType] ?? '⚡ %hunter% %action% %target%';
    
    // Prepare replacements
    $replacements = [
        '%hunter%' => $history['user_name'] ?? $history['hunter_name'] ?? '🤖 System',
        '%target%' => $history['target_name'] ?? 'User#' . ($history['target'] ?? '?'),
        '%delay%' => !empty($history['delay']) ? boomRenderMinutes($history['delay']) : '',
        '%reason%' => !empty($history['reason']) ? $history['reason'] : 'No reason given',
        '%content%' => $history['content'] ?? '',
        '%action%' => str_replace('_', ' ', $actionType)
    ];
    
    // Clean empty segments
    $result = str_replace(array_keys($replacements), array_values($replacements), $ctext);
    $result = preg_replace('/\s*\|\s*$/', '', $result); // Remove trailing pipe
    $result = preg_replace('/\s*\|\s*(No reason given)/', '', $result); // Remove default reason
    
    return $result;
}
function userHistory() {
    global $mysqli, $data, $lang, $hlang, $cody;
    // Sanitize the 'get_history' parameter and ensure it is a valid integer
    $id = (int)$_POST["get_history"];
    // Fetch user details (ensure they are valid)
    $user = userDetails($id);
    // Check if user has permission to view history
    if (!canUserHistory($user)) {
        return json_encode(['error' => 'You do not have permission to view history']);
    }
    // Use prepared statement for the database query to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT boom_history.*, boom_users.user_name, boom_users.user_tumb, boom_users.user_color FROM boom_history LEFT JOIN boom_users ON boom_history.hunter = boom_users.user_id WHERE boom_history.target = ? ORDER BY boom_history.history_date DESC LIMIT 200");
    $stmt->bind_param("i", $user["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $history_list = "";
    if ($result->num_rows > 0) {
        while ($history = $result->fetch_assoc()) {
            $history_list .= boomTemplate("element/history_log", $history);
        }
    } else {
        $history_list .= emptyZone($lang["no_data"]);
    }
    $stmt->close(); // Close statement
    return $history_list;
}

function removeHistory() {
    global $mysqli, $data, $lang, $hlang, $cody;
    // 1. Strict input validation
    if (!isset($_POST['remove_history'], $_POST['target']) || 
        !is_numeric($_POST['remove_history']) || 
        !is_numeric($_POST['target'])) {
        return fu_json_results(['error' => $lang['invalid_input'] ?? 'Invalid input parameters']);
    }
    $id = (int)$_POST['remove_history'];
    $target = (int)$_POST['target'];
    // 2. Verify target user exists with cached result
    static $userCache = [];
    if (!isset($userCache[$target])) {
        $userCache[$target] = userDetails($target);
    }
    $user = $userCache[$target];
    if (empty($user)) {
        return fu_json_results(['error' => $lang['user_not_exist'] ?? 'Target user not found']);
    }
    // 3. Permission check with explicit error logging
    if (!boomAllow($cody['can_manage_history'])) {
        error_log("History removal unauthorized attempt by user: " . $data['user_id']);
        return fu_json_results(['error' => $lang['no_permission'] ?? 'Permission denied']);
    }
    // 4. Database operation with transaction
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("DELETE FROM boom_history WHERE id = ? AND target = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        $stmt->bind_param("ii", $id, $user['user_id']);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        $affected = $stmt->affected_rows;
        $stmt->close();
        $mysqli->commit();
        // 5. Return standardized result
        if ($affected > 0) {
            return fu_json_results([
                'status' => 1,
                'message' => $lang['history_deleted'] ?? 'Record deleted'
            ]);
        } else {
            return fu_json_results([
                'status' => 0,
                'error' => $lang['history_not_found'] ?? 'Record not found'
            ]);
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("History removal error: " . $e->getMessage());
        return fu_json_results([
            'status' => 0,
            'error' => $lang['system_error'] ?? 'Operation failed'
        ]);
    }
}
?>
