<?php


require __DIR__ . "./../config_session.php";
if (isset($_POST["search_member"])) {
    echo staffsearchmember();
    exit;
}
if (isset($_POST["search_critera"])) {
    echo staffsearchcritera();
    exit;
}
if (isset($_POST["more_search_critera"]) && isset($_POST["last_critera"])) {
    echo staffmorecritera();
}

function staffSearchMember() {
    global $mysqli, $data, $lang;
    // Check permissions first
    if (!boomAllow(70)) {
        return "";
    }
    // Get and sanitize search input
    $target = isset($_POST['search_member']) ? trim($_POST['search_member']) : '';
    $list_members = "";
    // Prepare different query types with prepared statements
    if (filter_var($target, FILTER_VALIDATE_EMAIL)) {
        // Search by email
        $query = "SELECT * FROM boom_users WHERE user_email = ? ORDER BY user_name ASC LIMIT 500";
        $param_type = 's';
    } 
    elseif (filter_var($target, FILTER_VALIDATE_IP)) {
        // Search by IP
        $query = "SELECT * FROM boom_users WHERE user_ip = ? ORDER BY user_name ASC LIMIT 500";
        $param_type = 's';
    } 
    else {
        // Search by name or IP (wildcard)
        $query = "SELECT * FROM boom_users WHERE user_name LIKE CONCAT(?, '%') OR user_ip LIKE CONCAT(?, '%') ORDER BY user_name ASC LIMIT 500";
        $param_type = 'ss';
    }
    // Prepare and execute the statement
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return "<div class=\"page_element\">" . emptyZone(htmlspecialchars($lang['query_error'], ENT_QUOTES, 'UTF-8')) . "</div>";
    }
    // Bind parameters based on query type
    if (strpos($query, 'CONCAT') !== false) {
        // For wildcard search
        $stmt->bind_param($param_type, $target, $target);
    } else {
        // For exact match
        $stmt->bind_param($param_type, $target);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($members = $result->fetch_assoc()) {
            // Sanitize user data before passing to template
            $members = array_map(function($value) {
                return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
            }, $members);
            $list_members .= boomTemplate("element/admin_user", $members);
        }
    } else {
        $list_members .= emptyZone(htmlspecialchars($lang["empty"], ENT_QUOTES, 'UTF-8'));
    }

    $stmt->close();
    return "<div class=\"page_element\">" . $list_members . "</div>";
}

function staffMoreCritera() {
    global $mysqli, $data, $lang, $cody;
    // Check permissions first
    if (!boomAllow(70)) {
        return "";
    }
    // Validate and sanitize inputs
    $target = isset($_POST['more_search_critera']) ? (int)$_POST['more_search_critera'] : 0;
    $last = isset($_POST['last_critera']) ? (int)$_POST['last_critera'] : 0;
    // Additional permission check
    if ($target == 100 && !canViewInvisible()) {
        return "";
    }
    $list_members = "";
    // Get criteria - ensure getCritera() returns trusted SQL only
    $criteria = getCritera($target);
    // Prepare statement with parameterized query
    $query = "SELECT * FROM boom_users WHERE {$criteria} AND user_id > ? ORDER BY user_id ASC LIMIT 50";
    $stmt = $mysqli->prepare($query);    
    if (!$stmt) {
        // Log error securely in production
        error_log("Failed to prepare query: " . $mysqli->error);
        return 0;
    }
    // Bind parameters and execute
    $stmt->bind_param("i", $last);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($members = $result->fetch_assoc()) {
            // Sanitize user data before passing to template
            $members = array_map(function($value) {
                return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
            }, $members);
            $list_members .= boomTemplate("element/admin_user", $members);
        }
        $stmt->close();
        return $list_members;
    }
    $stmt->close();
    return 0;
}

function staffSearchCritera() {
    global $mysqli, $data, $lang, $cody;
    // Check permissions first
    if (!boomAllow(70)) {
        return '';
    }
    // Validate and sanitize input
    $target = isset($_POST['search_critera']) ? (int)$_POST['search_critera'] : 0;
    // Additional permission check
    if ($target == 100 && !canViewInvisible()) {
        return '';
    }
    $list_members = '';
    $count = 0;
    // Get criteria - ensure getCritera() returns trusted SQL only
    $criteria = getCritera($target);
    // Main query with prepared statement
    $query = "SELECT * FROM boom_users WHERE {$criteria} ORDER BY user_id ASC LIMIT 50";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Query preparation failed: " . $mysqli->error);
        return '<div class="page_element">' . htmlspecialchars($lang['query_error'], ENT_QUOTES, 'UTF-8') . '</div>';
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Process members
        while ($members = $result->fetch_assoc()) {
            // Sanitize all string values
            $members = array_map(function($value) {
                return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
            }, $members);
            $list_members .= boomTemplate('element/admin_user', $members);
        }
        $stmt->close();

        // Count query
        $count_query = "SELECT user_id FROM boom_users WHERE {$criteria}";
        $count_stmt = $mysqli->prepare($count_query);
        
        if ($count_stmt) {
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count = $count_result->num_rows;
            $count_stmt->close();
        }
    } else {
        $list_members .= emptyZone(htmlspecialchars($lang['empty'], ENT_QUOTES, 'UTF-8'));
    }
    // Build safe output
    $safe_target = (int)$target;
    $safe_load_more = htmlspecialchars($lang['load_more'], ENT_QUOTES, 'UTF-8');
    $list = '<div id="search_admin_list" class="page_element">' . $list_members . '</div>';
    if ($count > 50) {
        $list .= '<div id="search_for_more" class="page_element">'
               . '<button onclick="moreAdminSearch(' . $safe_target . ');" '
               . 'class="default_btn full_button reg_button">'
               . $safe_load_more 
               . '</button></div>';
    }
    
    return $list;
}

?>