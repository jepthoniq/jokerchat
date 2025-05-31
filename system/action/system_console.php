<?php

require __DIR__ . "/../config_session.php";
require BOOM_PATH . "/system/language/" . $data["user_language"] . "/console.php";
if(!boomAllow($cody["can_view_console"])) {
    echo json_encode(['status' => 0, 'message' => 'Permission denied']);
    exit;
}
// Securely handle reload and search console requests
if(isset($_POST["reload_console"])) {
    $last = escape($_POST["reload_console"]);
    echo reloadSystemConsole($last);
    exit;
}
if(isset($_POST["more_console"])) {
    $last = escape($_POST["more_console"]);
    echo loadMoreSystemConsole($last);
    exit;
}
if(isset($_POST["search_console"])) {
	$find = escape($_POST["search_console"]);
    if (empty($find)) {
         echo reloadSystemConsole(0);
        exit;
    }
    $id = 0;
    $user = nameDetails($find);
    if (!empty($user)) {
        $id = $user["user_id"];
    }
	echo searchSystemConsole($id, $find);
    exit;
}
if(isset($_POST["clear_console"]) && boomAllow(100)) {
    echo clearSystemConsole();
    exit;
}
// Function for rendering console user
function consoleUser($console) {
    // Ensure that $console["chunter"] is not null before passing it to htmlspecialchars
    $chunter = isset($console["chunter"]) ? $console["chunter"] : ''; // Fallback to an empty string if null
    return "<span onclick=\"getProfile(" . intval($console["hunter"]) . ");\" class=\"bold console_user\">" . htmlspecialchars($chunter) . "</span>";
}

// Function for rendering console target
function consoleTarget($console) {
    // Ensure that $console["ctarget"] is not null before passing it to htmlspecialchars
    $ctarget = isset($console["ctarget"]) ? $console["ctarget"] : ''; // Fallback to an empty string if null
    return "<span onclick=\"getProfile(" . intval($console["target"]) . ");\" class=\"bold console_user\">" . htmlspecialchars($ctarget) . "</span>";
}

// Function for rendering console text
function consoleText($t) {
    // Ensure that $t is not null before passing it to htmlspecialchars
    $t = isset($t) ? $t : ''; // Fallback to an empty string if null
    return "<span class=\"bold console_text\">" . htmlspecialchars($t) . "</span>";
}

// Render console text with dynamic replacements
function renderConsoleText($console) {
    global $clang;
    $ctext = $clang[$console["ctype"]];
    $ctext = str_replace(
        ["%hunter%", "%target%", "%oldname%", "%room%", "%data%", "%data2%", "%rank%", "%roomrank%", "%delay%"],
        [
            consoleUser($console),
            consoleTarget($console),
            consoleText($console["custom"]),
            consoleText($console["croom"]),
            consoleText($console["custom"]),
            consoleText($console["custom2"]),
            consoleText(rankTitle($console["crank"])),
            consoleText(roomRankTitle($console["crank"])),
            consoleText(boomRenderMinutes($console["delay"]))
        ],
        $ctext
    );
    return $ctext;
}

// Reload system console with prepared statements
function reloadSystemConsole($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("
        SELECT *,
            (SELECT user_name FROM boom_users WHERE user_id = hunter) AS chunter,
            (SELECT user_name FROM boom_users WHERE user_id = target) AS ctarget,
            (SELECT room_name FROM boom_rooms WHERE room_id = room) AS croom
        FROM boom_console
        WHERE id > ?
        ORDER BY cdate DESC
        LIMIT 500
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $list = "";
        while ($console = $result->fetch_assoc()) {
            $list .= boomTemplate("element/console_log", $console);
        }
        return $list;
    }
    return 0;
}
// Load more system console with prepared statements
function loadMoreSystemConsole($id) {
    global $mysqli;
    $stmt = $mysqli->prepare("
        SELECT *,
            (SELECT user_name FROM boom_users WHERE user_id = hunter) AS chunter,
            (SELECT user_name FROM boom_users WHERE user_id = target) AS ctarget,
            (SELECT room_name FROM boom_rooms WHERE room_id = room) AS croom
        FROM boom_console
        WHERE id < ?
        ORDER BY cdate DESC
        LIMIT 500
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $list = "";
        while ($console = $result->fetch_assoc()) {
            $list .= boomTemplate("element/console_log", $console);
        }
        return $list;
    }
    return 0;
}
// Search system console with secure prepared statements
function searchSystemConsole($id, $find) {
    global $mysqli, $clang, $lang;
    $find_list = [];
    // Check for matching entries in language variables
    foreach ($clang as $key => $value) {
        if (stripos($value, $find) !== false) {
            $find_list[] = $key;
        }
    }
    // Sanitize the list of found words
    $find_list = listWordArray($find_list);
    $stmt = $mysqli->prepare("
        SELECT *,
            (SELECT user_name FROM boom_users WHERE user_id = hunter) AS chunter,
            (SELECT user_name FROM boom_users WHERE user_id = target) AS ctarget,
            (SELECT room_name FROM boom_rooms WHERE room_id = room) AS croom
        FROM boom_console
        WHERE hunter = ? OR target = ? OR ctype IN ($find_list)
        ORDER BY cdate DESC
        LIMIT 500
    ");
    $stmt->bind_param("ii", $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $list = "";
        while ($console = $result->fetch_assoc()) {
            $list .= boomTemplate("element/console_log", $console);
        }
        return $list;
    }
    return emptyZone($lang["no_data"]);
}
// Clear system console
function clearSystemConsole() {
    global $mysqli, $cody;
    if (!boomAllow($cody["can_clear_console"])) {
        return json_encode(['status' => 0, 'message' => 'Permission denied']);
    }
    $mysqli->query("TRUNCATE TABLE boom_console");
    boomConsole("clear_console");
    return 1;
}
?>
