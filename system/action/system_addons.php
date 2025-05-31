<?php

require __DIR__ . "./../config_session.php";

// Check if the user has permission to manage addons
if (isset($_POST["activate_addons"]) && isset($_POST["addons"]) && boomAllow($cody["can_manage_addons"])) {
    $this_addons = escape($_POST["addons"]);
    echo boomActivateAddons($this_addons);
    exit;
}

if (isset($_POST["remove_addons"]) && isset($_POST["addons"]) && boomAllow($cody["can_manage_addons"])) {
    $this_addons = escape($_POST["addons"]);
    echo boomRemoveAddons($this_addons);
    exit;
}

exit;

function boomActivateAddons($this_addons){
    global $mysqli, $data, $cody, $lang;
    // Sanitize and validate the addon name
    $this_addons = trim($this_addons);
    if (empty($this_addons) || !preg_match('/^[a-zA-Z0-9-_]+$/', $this_addons)) {
        return boomCode(0, ["error" => "Invalid addon name"]);
    }
    // Use prepared statements for SQL queries to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM boom_addons WHERE addons = ?");
    $stmt->bind_param("s", $this_addons);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return boomCode(0, ["error" => "This addon is already installed in your system"]);
    }
    // Safe file inclusion by checking the existence of the addon directory first
    $addonPath = BOOM_PATH . "/addons/" . $this_addons . "/system/install.php";
    if (file_exists($addonPath)) {
        require $addonPath;
    } else {
        return boomCode(0, ["error" => "Addon installation file not found"]);
    }
    if (!isset($ad["name"])) {
        return boomCode(0, ["error" => "Addon configuration is missing"]);
    }
	// Default values for addon configuration
	$def = [
		"name" => "", "access" => 0, "max" => 100, "bot_id" => 0,
		"custom1" => "", "custom2" => "", "custom3" => "", "custom4" => "",
		"custom5" => "", "custom6" => "", "custom7" => "", "custom8" => "",
		"custom9" => "", "custom10" => ""
	];

	$a = array_merge($def, $ad);
    // Insert the addon into the database using prepared statements
	// Prepare SQL query with correct number of placeholders
	$stmt = $mysqli->prepare("
		INSERT INTO boom_addons 
		(addons, addons_access, addons_max, bot_id, custom1, custom2, custom3, custom4, custom5, custom6, custom7, custom8, custom9, custom10) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	");
	// Bind parameters correctly
	$stmt->bind_param(
		"siiissssssssss",
		$a["name"], $a["access"], $a["max"], $a["bot_id"],
		$a["custom1"], $a["custom2"], $a["custom3"], $a["custom4"],
		$a["custom5"], $a["custom6"], $a["custom7"], $a["custom8"],
		$a["custom9"], $a["custom10"]
	);	
    $stmt->execute();
    $last_addons = $stmt->insert_id;
    // Securely generate the addon key
    $addons_key = sha1(str_rot13($a["name"] . $last_addons));
    $stmt = $mysqli->prepare("UPDATE boom_addons SET addons_key = ? WHERE addons = ?");
    $stmt->bind_param("ss", $addons_key, $a["name"]);
    $stmt->execute();
    // Add bot if necessary
	if (isset($a["bot_name"]) && isset($a["bot_type"])) {
		usleep(500000);
		$c_time = time();
		// Create bot user
		$stmt = $mysqli->prepare("
			INSERT INTO boom_users (user_name, user_rank, user_password, user_email, user_join, user_ip, verified, user_bot, user_tumb) 
			VALUES (?, 1, ?, '', ?, '0.0.0.0', 1, ?, 'default_bot.png')
		");
		$password = randomPass(); // Ensure this function generates a secure password
		if (!$stmt) {
			die(json_encode(["status" => "error", "message" => "Database error: " . $mysqli->error]));
		}
		$stmt->bind_param("ssis", $a["bot_name"], $password, $c_time, $a["bot_type"]);
		if (!$stmt->execute()) {
			die(json_encode(["status" => "error", "message" => "Insert failed: " . $stmt->error]));
		}
		$last_id = $stmt->insert_id;
		$stmt->close();
		// Update bot info in the addon
		$stmt = $mysqli->prepare("UPDATE boom_addons SET bot_name = ?, bot_id = ? WHERE addons = ?");
		if (!$stmt) {
			die(json_encode(["status" => "error", "message" => "Database error: " . $mysqli->error]));
		}
		$stmt->bind_param("sis", $a["bot_name"], $last_id, $a["name"]);
		if (!$stmt->execute()) {
			die(json_encode(["status" => "error", "message" => "Update failed: " . $stmt->error]));
		}
		$stmt->close();
	}
    boomConsole("addons_install", ["custom" => $this_addons]);
    return boomCode(1);
}

function boomRemoveAddons($this_addons){
    global $mysqli, $data, $cody, $lang;
    // Sanitize and validate the addon name
    $this_addons = trim($this_addons);
    if (empty($this_addons) || !preg_match('/^[a-zA-Z0-9-_]+$/', $this_addons)) {
        return boomCode(0, ["error" => "Invalid addon name"]);
    }
    // Retrieve addon data
    $addons = addonsData($this_addons);
    // Safe file inclusion
    $addonPath = BOOM_PATH . "/addons/" . $this_addons . "/system/uninstall.php";
    if (file_exists($addonPath)) {
        require $addonPath;
    } else {
        return boomCode(0, ["error" => "Addon uninstallation file not found"]);
    }
    // Clear bot user data if needed
    if ($addons["bot_id"] > 0) {
        $user = userDetails($addons["bot_id"]);
        clearUserData($user);
    }
    // Delete the addon from the database using prepared statements
    $stmt = $mysqli->prepare("DELETE FROM boom_addons WHERE addons = ?");
    $stmt->bind_param("s", $this_addons);
    $stmt->execute();

    boomConsole("addons_uninstall", ["custom" => $this_addons]);
    return 1;
}
?>
