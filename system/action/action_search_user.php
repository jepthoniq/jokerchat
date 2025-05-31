<?php
require(__DIR__ . '/../config_session.php');

function staffSearchMember() {
    global $mysqli, $data, $lang;
    $target = cleanSearch(escape($_POST['search_member']));
    $list_members = '';

    if (!canManageUser()) {
        return '';
    }

    if (filter_var($target, FILTER_VALIDATE_EMAIL)) {
        $stmt = $mysqli->prepare("SELECT * FROM boom_users WHERE user_email = ? ORDER BY user_name ASC LIMIT 200");
        $stmt->bind_param("s", $target);
    } 
    else if (filter_var($target, FILTER_VALIDATE_IP)) {
        $stmt = $mysqli->prepare("SELECT * FROM boom_users WHERE user_ip = ? ORDER BY user_name ASC LIMIT 200");
        $stmt->bind_param("s", $target);
    } 
    else {
        $search_target = "$target%"; // Adding wildcard safely
        $stmt = $mysqli->prepare("SELECT * FROM boom_users WHERE user_name LIKE ? OR user_ip LIKE ? ORDER BY user_name ASC LIMIT 200");
        $stmt->bind_param("ss", $search_target, $search_target);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($members = $result->fetch_assoc()) {
            $list_members .= boomTemplate('element/admin_user', $members);
        }
    } else {
        $list_members .= emptyZone($lang['empty']);
    }

    $stmt->close();
    
    return '<div class="page_element">' . $list_members . '</div>';    
}


function staffMoreCritera(){
    global $mysqli, $data;
    if(!canManageUser()){
        return '';
    }
    $target = isset($_POST['more_search_critera']) ? (int)$_POST['more_search_critera'] : 0;
    $last = isset($_POST['last_critera']) ? (int)$_POST['last_critera'] : 0;
    if($target == 11 && !canViewInvisible()){
        return '';
    }
    $list_members = '';
    $criteria = getCritera($target); // Make sure getCritera() returns safe SQL
    // Use prepared statement
    $stmt = $mysqli->prepare("SELECT * FROM boom_users WHERE {$criteria} AND user_id > ? ORDER BY user_id ASC LIMIT 50");
    if ($stmt) {
        $stmt->bind_param('i', $last);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            while($members = $result->fetch_assoc()){
                $list_members .= boomTemplate('element/admin_user', $members);
            }
            $stmt->close();
            return $list_members;
        }
        $stmt->close();
    }
    return 0;
}

function staffSearchCritera() {
    global $mysqli, $data, $lang;
    // Validate permissions first
    if (!canManageUser()) {
        return '';
    }
    // Secure input - force integer type
    $target = isset($_POST['search_critera']) ? (int)$_POST['search_critera'] : 0;
    // Additional permission check
    if ($target == 11 && !canViewInvisible()) {
        return '';
    }
    $list_members = '';
    $count = 0;
    // Get criteria safely - ensure getCritera() returns trusted SQL
    $criteria = getCritera($target);
    // First query with prepared statement
    $query = "SELECT * FROM boom_users WHERE {$criteria} ORDER BY user_id ASC LIMIT 50";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($members = $result->fetch_assoc()) {
                // Escape HTML output for each member data
                $members = array_map('htmlspecialchars', $members);
                $list_members .= boomTemplate('element/admin_user', $members);
            }
            // Count query with prepared statement
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
        
        $stmt->close();
    }

    // Build output with escaped data
    $list = '<div id="search_admin_list" class="page_element">' . $list_members . '</div>';
    if ($count > 50) {
        $list .= '<div id="search_for_more" class="page_element">' .
                 '<button onclick="moreAdminSearch(' . (int)$target . ');" ' .
                 'class="default_btn full_button reg_button">' .
                 htmlspecialchars($lang['load_more'], ENT_QUOTES, 'UTF-8') .
                 '</button></div>';
    }
    
    return $list;
}
function staffSearchAction(){
	global $mysqli, $data, $lang;
	$action = escape($_POST['search_action']);
	if($action == 'muted' && canMute()){
		$list = getActionList('muted');
	}
	else if($action == 'mmuted' && canMute()){
		$list = getActionList('mmuted');
	}
	else if($action == 'pmuted' && canMute()){
		$list = getActionList('pmuted');
	}
	else if($action == 'kicked' && canKick()){
		$list = getActionList('kicked');
	}
	else if($action == 'ghosted' && canGhost()){
		$list = getActionList('ghosted');
	}
	else if($action == 'banned' && canBan()){
		$list = getActionList('banned');
	}
	else {
		$list = emptyZone($lang['empty']);
	}
	return $list;
}

function searchUser() {
    global $mysqli, $data, $lang;
    // Validate and sanitize inputs
    $username = isset($_POST['query']) ? trim($_POST['query']) : '';
    $type = isset($_POST['search_type']) ? (int)$_POST['search_type'] : 1;
    $order = isset($_POST['search_order']) ? (int)$_POST['search_order'] : 0;
    $online_delay = getDelay();
    $list = '';
    // Validate search type
    switch($type) {
        case 1:
            $search_type = "user_id > 0 AND user_bot = 0";
            break;
        case 2:
            $search_type = "user_sex = 2 AND sshare = 1 AND user_bot = 0";
            break;
        case 3:
            $search_type = "user_sex = 1 AND sshare = 1 AND user_bot = 0";
            break;
        case 4:
            $search_type = "user_rank >= 70 AND user_bot = 0";
            break;
        default:
            return emptyZone(htmlspecialchars($lang['invalid_search_type'], ENT_QUOTES, 'UTF-8'));
    }
    // Validate order
    switch($order) {
        case 0:
            $order_sql = "ORDER BY rand()";
            break;
        case 1:
            $order_sql = "ORDER BY user_join DESC";
            break;
        case 2:
            $order_sql = "ORDER BY last_action DESC";
            break;
        case 3:
            $order_sql = "ORDER BY user_name ASC";
            break;
        case 4:
            $order_sql = "ORDER BY user_rank DESC";
            break;
        default:
            return emptyZone(htmlspecialchars($lang['invalid_order_type'], ENT_QUOTES, 'UTF-8'));
    }
    // Prepare base query
    $query = "SELECT * FROM boom_users WHERE user_rank >= 0 AND $search_type";
    $params = [];
    $types = '';
    // Add username filter if provided
    if (!empty($username)) {
        $query .= " AND user_name LIKE ?";
        $params[] = '%' . $username . '%';
        $types .= 's';
    }
    // Add ordering
    $query .= " $order_sql LIMIT 100";
    // Prepare and execute statement
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return emptyZone(htmlspecialchars($lang['query_failed'], ENT_QUOTES, 'UTF-8'));
    }
    // Bind parameters if needed
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            // Sanitize user data before passing to template
            $user = array_map(function($value) {
                return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
            }, $user);
            $list .= createUserlist($user, true);
        }
        $stmt->close();
        return $list;
    } else {
        $stmt->close();
        return emptyZone(htmlspecialchars($lang['nothing_found'], ENT_QUOTES, 'UTF-8'));
    }
}

// end of functions

if(isset($_POST['query'], $_POST['search_type'], $_POST['search_order'])){
	echo searchUser();
}
if(isset($_POST['search_member'])){
	echo staffSearchMember();
	die();
}
if(isset($_POST['search_critera'])){
	echo staffSearchCritera();
	die();
}
if(isset($_POST['search_action'])){
	echo staffSearchAction();
	die();
}
if(isset($_POST['more_search_critera'], $_POST['last_critera'])){
	echo staffMoreCritera();
}
die();
?>