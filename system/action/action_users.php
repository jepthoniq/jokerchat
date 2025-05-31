<?php
require __DIR__ . "./../config_session.php";
// Handle VPN permission requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target'], $_POST['set_user_vpn'])) {
    global $mysqli;
    // 1. Validate inputs strictly
    $target = filter_input(INPUT_POST, 'target', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    $user_vpn = filter_input(INPUT_POST, 'set_user_vpn', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 1] // Only allows 0 or 1
    ]);
    // 2. Check for validation failures
    if ($target === false || $user_vpn === false) {
        http_response_code(400);
        exit(json_encode(['error' => 'Invalid input.']));
    }
    // 3. Use a prepared statement to prevent SQL injection
    $stmt = $mysqli->prepare("UPDATE boom_users SET uvpn = ? WHERE user_id = ?");
    if (!$stmt) {
        http_response_code(500);
        exit(json_encode(['error' => 'Database preparation error.']));
    }
    $stmt->bind_param("ii", $user_vpn, $target);
    $success = $stmt->execute();
    // 4. Check execution success and return proper response
    if ($success) {
        echo json_encode(['success' => true, 'uvpn' => $user_vpn]);
    } else {
        error_log("VPN update failed for user $target: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['error' => 'Database update failed.']);
    }
    $stmt->close();
    exit;
}
// Handle other requests
if (isset($_POST["change_rank"]) && isset($_POST["target"])) {
    echo boomChangeUserRank();
} else {
    // Change user color and font
    if (isset($_POST["user_color"]) && isset($_POST["user_font"]) && isset($_POST["user"])) {
        echo boomChangeColor();
        exit;
    }
    // Change account status
    if (isset($_POST["account_status"]) && isset($_POST["target"])) {
        echo boomChangeUserVerify();
    } else {
        // Delete user account
        if (isset($_POST["delete_user_account"])) {
            echo boomDeleteAccount();
            exit;
        }
        // Update user email
        if (isset($_POST["set_user_email"]) && isset($_POST["set_user_id"])) {
            echo staffUserEmail();
            exit;
        }
        // Update user about section
        if (isset($_POST["set_user_about"]) && isset($_POST["target_about"])) {
            echo staffUserAbout();
            exit;
        }
        // Change user password
        if (isset($_POST["target_id"]) && isset($_POST["user_new_password"])) {
            echo staffChangePassword();
            exit;
        }
        // Change user mood
        if (isset($_POST["target_id"]) && isset($_POST["user_new_mood"])) {
            echo staffChangeMood();
            exit;
        }
        // Change username
        if (isset($_POST["target_id"]) && isset($_POST["user_new_name"])) {
            echo staffChangeUsername();
            exit;
        }
        // Create new user
        if (isset($_POST["create_user"]) && isset($_POST["create_name"]) && isset($_POST["create_password"]) && isset($_POST["create_email"]) && isset($_POST["create_age"]) && isset($_POST["create_gender"])) {
            echo staffCreateUser();
            exit;
        }		
    }
}
// Update user location settings
if (isset($_POST["user_language"]) && isset($_POST["user_country"]) && isset($_POST["user_timezone"])) {
    echo setUserLocation();
    exit;
}
exit;
function staffChangePassword() {
	 global $mysqli,$data;
    // Sanitize inputs
    $pass = escape($_POST["user_new_password"]);
    $target = escape($_POST["target_id"]);
    // Fetch user details
    $user = userDetails($target);
    if (!$user) {
        return -1; // User not found
    }
    // Permission check
    if (!canModifyPassword($user)) {
        return 0; // No permission
    }
    // Validate password
    if (!boomValidPassword($pass)) {
        return 2; // Invalid password
    }
    // Hash the password using PASSWORD_BCRYPT
    $new_pass = password_hash($pass, PASSWORD_BCRYPT);
    // Update database using prepared statement
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_password = ? WHERE user_id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        return -1; // Database error
    }
    $stmt->bind_param("si", $new_pass, $user["user_id"]);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return -1; // Database error
    }
    // Log the action
    boomConsole("pass_user", ["target" => $user["user_id"], "custom" => $user["user_name"]]);
    return 1; // Success
}
/* function staffChangePassword(){
    global $mysqli,$data;
    $pass = escape($_POST["user_new_password"]);
    $target = escape($_POST["target_id"]);
    $user = userDetails($target);
    if (!canModifyPassword($user)) {
        return 0;
    }
    if (!boomValidPassword($pass)) {
        return 2;
    }
    $new_pass = encrypt($pass);
    $mysqli->query("UPDATE boom_users SET user_password = '" . $new_pass . "' WHERE user_id = '" . $user["user_id"] . "'");
    boomConsole("pass_user", ["target" => $user["user_id"], "custom" => $user["user_name"]]);
    return 1;
} */
function setUserLocation() {
    global $mysqli, $data, $cody;
    // 1. Validate and sanitize inputs
    $language = isset($_POST["user_language"]) ? boomSanitize($_POST["user_language"]) : '';
    $country = isset($_POST["user_country"]) ? escape($_POST["user_country"]) : '';
    $new_timezone = isset($_POST["user_timezone"]) ? escape($_POST["user_timezone"]) : '';
    require BOOM_PATH . "/system/element/timezone.php";
    $refresh = 0;
    // 2. Validate language selection
    if (!empty($language) && preg_match('/^[a-zA-Z0-9-_]+$/', $language)) { 
        if (file_exists(BOOM_PATH . "/system/language/" . $language . "/language.php")) {
            $stmt = $mysqli->prepare("UPDATE boom_users SET user_language = ? WHERE user_id = ?");
            $stmt->bind_param("si", $language, $data["user_id"]);
            $stmt->execute();
            $stmt->close();
            setBoomLang($language);
            if ($language !== $data["user_language"]) {
                $refresh++;
            }
        }
    }
    // 3. Validate timezone selection
    if (!empty($new_timezone) && in_array($new_timezone, $timezone)) {
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_timezone = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_timezone, $data["user_id"]);
        $stmt->execute();
        $stmt->close();
        if ($new_timezone !== $data["user_timezone"]) {
            $refresh++;
        }
    }
    // 4. Validate country selection
    if (!empty($country) && validCountry($country)) {
        $stmt = $mysqli->prepare("UPDATE boom_users SET country = ? WHERE user_id = ?");
        $stmt->bind_param("si", $country, $data["user_id"]);
        $stmt->execute();
        $stmt->close();
    }
    return ($refresh > 0) ? 1 : 0;
}
function boomChangeUserRank() {
    global $mysqli, $data;
    // 1. Input Validation
    $target = filter_input(INPUT_POST, 'target', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $rank = filter_input(INPUT_POST, 'change_rank', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]); // Adjust max rank as needed
    if ($target === false || $rank === false) {
        return 3; // Invalid input
    }
    // 2. Securely Fetch User Details
    $user = userDetails($target);
    if (!$user) {
        return 3; // User not found
    }
    // 3. Permission Check
    if (!canRankUser($user)) {
        return 0; // Permission denied
    }
    // 4. Check if rank is unchanged
    if ($user["user_rank"] === $rank) {
        return 2; // No change needed
    }
    // 5. Update User Rank Securely
    if ($stmt = $mysqli->prepare("UPDATE boom_users SET user_rank = ? WHERE user_id = ?")) {
        $stmt->bind_param("ii", $rank, $target);
        $stmt->execute();
        $stmt->close();
    }
    // 6. Reset User Permissions Based on Rank
    userReset($user, $rank);
    // 7. Send Notification
    boomNotify("rank_change", [
        "target" => $target,
        "source" => "rank_change",
        "rank" => $rank
    ]);
    // 8. Handle Staff Promotions (if applicable)
    if (isStaff($rank)) {
        $queries = [
            "UPDATE boom_users SET room_mute = 0, user_private = 1, user_mute = 0, user_regmute = 0 WHERE user_id = ?",
            "DELETE FROM boom_room_action WHERE action_user = ?",
            "DELETE FROM boom_ignore WHERE ignored = ?"
        ];
        foreach ($queries as $query) {
            if ($stmt = $mysqli->prepare($query)) {
                $stmt->bind_param("i", $target);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    // 9. Log the Action Securely
    boomConsole("change_rank", [
        "target" => $user["user_id"],
        "rank" => $rank
    ]);
    return 1; // Success
}
function boomChangeUserVerify() {
    global $mysqli, $data;
    // 1. Input Validation (Ensures target & status are valid integers)
    $target = filter_input(INPUT_POST, 'target', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $status = filter_input(INPUT_POST, 'account_status', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    if ($target === false || $status === false) {
        return 0; // Invalid input
    }
    // 2. Permission Check (Ensures user has sufficient rights)
    if (!boomAllow(80)) {
        return 0; // Permission denied
    }
    // 3. Fetch User Data Securely
    $user = userDetails($target);
    if (!$user || !canEditUser($user, 80)) {
        return empty($user) ? 3 : 0; // User not found OR no permission
    }
    // 4. Prepare Update Query
    if ($status === 0) {
        // If unverified, check email activation status
        $verify = userHaveEmail($user) ? (int)$data["activation"] : 0;
        $query = "UPDATE boom_users SET verified = 0, user_verify = ?" . ($verify === 1 ? ", user_action = user_action + 1" : "") . " WHERE user_id = ?";
        $params = [$verify, $user["user_id"]];
    } else {
        // If verified
        $query = "UPDATE boom_users SET verified = 1, user_verify = 0 WHERE user_id = ?";
        $params = [$user["user_id"]];
    }
    // 5. Execute Securely with Prepared Statements
    if ($stmt = $mysqli->prepare($query)) {
        $types = str_repeat('i', count($params));
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            boomConsole("change_verify", ["target" => $user["user_id"]]);
            $stmt->close();
            return 1; // Success
        }
        $stmt->close();
    }
    return 0; // Query failed
}
function boomChangeColor() {
    global $mysqli, $data, $cody;
    // Validate and sanitize input
    $color = escape($_POST["user_color"]);
    $font = escape($_POST["user_font"]);
    $id = escape($_POST["user"]);
    // Fetch user details
    $user = userDetails($id);
    // Check if the current user has permission to modify the color
    if (!canModifyColor($user)) {
        return 0; // No permission
    }
    // Validate the color and font
    if (!validNameColor($color)) {
        return 0; // Invalid color
    }
    if (!validNameFont($font)) {
        return 0; // Invalid font
    }
    // Prepare and execute the SQL query using prepared statements
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_color = ?, user_font = ? WHERE user_id = ?");
    if (!$stmt) {
        // Log the error if the statement preparation fails
        error_log("Database error: " . $mysqli->error);
        return 0; // Database error
    }
    $stmt->bind_param("ssi", $color, $font, $id);
    $result = $stmt->execute();
    // Check if the query execution was successful
    if (!$result) {
        error_log("Query execution failed: " . $stmt->error);
        $stmt->close();
        return 0; // Query failed
    }
    $stmt->close();

    // Log the action in the console
    boomConsole("change_color", ["target" => $id]);
    // Return success
    return 1;
}
function staffUserEmail() {
    global $mysqli, $data, $cody;
    // 1. Input Validation (Ensures valid user ID and email)
    $user_id = filter_input(INPUT_POST, 'set_user_id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    $user_email = isset($_POST['set_user_email']) ? trim($_POST['set_user_email']) : '';
    if ($user_id === false || empty($user_email)) {
        return 0; // Invalid input
    }
    // 2. Get User Details Securely
    $user = userDetails($user_id);
    if (empty($user)) {
        return 0; // User not found
    }
    // 3. Email Validation (Ensure proper email format)
    if (!isEmail($user_email)) {
        return 3; // Invalid email format
    }
    // 4. Check for Duplicate Email (if needed)
    if (!checkEmail($user_email) && !boomSame($user_email, $user["user_email"])) {
        return 2; // Email already exists
    }
    // 5. Permission Check (Ensure user has permission to modify email)
    if (!canModifyEmail($user)) {
        return 0; // Permission denied
    }
    // 6. Process and Secure Update
    $smail = smailProcess($user_email);
    // Securely update the user's email using prepared statements
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_email = ?, user_smail = ? WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ssi", $user_email, $smail, $user_id);
        if ($stmt->execute()) {
            // 7. Log the action securely
            boomConsole("edit_profile", [
                "target" => $user_id,
                "old_email" => $user["user_email"],
                "new_email" => $user_email
            ]);
            $stmt->close();
            return 1; // Success
        } else {
            // Error logging for failed email update
            error_log("Email update failed for user {$user_id}: " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Error logging if prepare statement fails
        error_log("Prepare failed: " . $mysqli->error);
    }
    return 0; // Error occurred
}
function staffUserAbout() {
    global $mysqli, $data, $cody;
    // 1. Input Validation (Ensures valid user ID)
    $user_id = filter_input(INPUT_POST, 'target_about', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if (!$user_id) {
        return 0; // Invalid user ID
    }
    // 2. Sanitize User About
    $user_about = isset($_POST['set_user_about']) ? htmlspecialchars(trim($_POST['set_user_about']), ENT_QUOTES, 'UTF-8') : '';
    // 3. Get User Details Securely
    $user = userDetails($user_id);
    if (empty($user)) {
        return 0; // User not found
    }
    // 4. Permission Check (Ensure user has permission to modify the about field)
    if (!canModifyAbout($user)) {
        return 0; // Permission denied
    }
    // 5. Content Validation (Ensure content is within length limits and doesn't contain bad words)
    if (mb_strlen($user_about) > 900) {
        return 0; // Content too long
    }
    if (isBadText($user_about)) {
        return 2; // Content contains bad words
    }
    // 6. Secure Database Update (Use prepared statements)
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_about = ? WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $user_about, $user_id);
        if ($stmt->execute()) {
            // 7. Enhanced Logging (Log user activity securely)
            boomConsole("edit_profile", [
                "target" => $user["user_id"],
                "action" => "about_update",
            ]);
            $stmt->close();
            return 1; // Success
        }
        // Error logging for failed about update
        error_log("About update failed for user {$user_id}: " . $stmt->error);
        $stmt->close();
    } else {
        // Error logging for prepare statement failure
        error_log("Prepare failed: " . $mysqli->error);
    }

    return 0; // Error occurred
}
function staffChangeUsername(){
    global $mysqli, $data, $cody;
    // 1. Input Validation (Ensures valid target user ID and non-empty new name)
    $target = filter_input(INPUT_POST, 'target_id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]); 
    $new_name = isset($_POST['user_new_name']) ? 
        trim(htmlspecialchars($_POST['user_new_name'], ENT_QUOTES, 'UTF-8')) : 
        '';
    if (!$target || empty($new_name)) {
        return 0; // Invalid input or missing name
    }
    // 2. Get User Details Securely
    $user = userDetails($target);
    if (empty($user)) {
        return 0; // User not found
    }
    // 3. Permission Check (Ensure current user has permission to change the target user's name)
    if (!canModifyName($user)) {
        return 0; // Permission denied
    }
    // 4. Check if New Name is Unchanged
    if ($new_name === $user["user_name"]) {
        return 1; // No change needed
    }
    // 5. Validate New Username Format
    if (!validName($new_name)) {
        return 2; // Invalid username format
    }
    // 6. Check if New Name is Available
    if (!boomSame($new_name, $user["user_name"]) && !boomUsername($new_name)) {
        return 3; // Username already taken
    }
    // 7. Secure Database Updates with Transactions
    try {
        $mysqli->begin_transaction();
        // Update main user table
        $stmt = $mysqli->prepare("UPDATE boom_users SET user_name = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_name, $user["user_id"]);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update username in boom_users table");
        }
        // Update bot table if applicable
        if (isBot($user)) {
            $stmt2 = $mysqli->prepare("UPDATE boom_addons SET bot_name = ? WHERE bot_id = ?");
            $stmt2->bind_param("si", $new_name, $user["user_id"]);
            if (!$stmt2->execute()) {
                throw new Exception("Failed to update bot username in boom_addons table");
            }
        }
        $mysqli->commit();
        // 8. Logging and Notifications
        boomConsole("rename_user", ["target" => $user["user_id"], "custom" => $user["user_name"]]);
        clearNotifyAction($user["user_id"], "name_change");
        boomNotify("name_change", ["target" => $user["user_id"], "source" => "name_change", "custom" => $new_name]);
        changeNameLog($user, $new_name);
        return 1; // Success
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("Username change failed for user {$user["user_id"]}: " . $e->getMessage());
        return 0; // Error
    }
}
function staffChangeMood() {
    global $mysqli, $data, $cody;
    // 1. Input Validation (Ensures valid target user ID)
    $target = filter_input(INPUT_POST, 'target_id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    $mood = isset($_POST['user_new_mood']) ? 
        htmlspecialchars(trim($_POST['user_new_mood']), ENT_QUOTES, 'UTF-8') : 
        '';
    if (!$target || empty($mood)) {
        return 0; // Invalid input or empty mood
    }
    // 2. Get User Details Securely
    $user = userDetails($target);
    if (empty($user)) {
        return 0; // User not found
    }
    // 3. Permission Check (Ensure current user has permission to change the target user's mood)
    if (!canModifyMood($user)) {
        return 0; // Permission denied
    }
    // 4. Check if Mood is Unchanged
    if ($mood === $user["user_mood"]) {
        return getMood($user); // Return the current mood if unchanged
    }
    // 5. Content Validation (Bad words check and length check)
    if (isBadText($mood)) {
        return 2; // Invalid mood (bad words)
    }
    if (mb_strlen($mood) > 40) {
        return 0; // Mood text too long
    }
    // 6. Secure Database Update with Prepared Statement
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_mood = ? WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $mood, $user["user_id"]);
        if ($stmt->execute()) {
            // 7. Simplified Logging
            boomConsole("mood_user", [
                "target" => $user["user_id"],
                "custom" => $user["user_name"]
            ]);

            // 8. Return updated mood
            $u = userDetails($user["user_id"]);
            return getMood($u);
        }
        error_log("Mood update failed: " . $stmt->error); // Log any statement error
        $stmt->close();
    } else {
        error_log("Prepare failed: " . $mysqli->error); // Log any prepare error
    }
    return 0; // Return 0 if any error occurs
}
function boomDeleteAccount(){
    global $mysqli, $data, $cody;
    // 1. Input Validation
    $id = filter_input(INPUT_POST, 'delete_user_account', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if (!$id) {
        return 0; // Invalid ID or not provided
    }
    // 2. Get User Details
    $user = userDetails($id);
    if (empty($user)) {
        return 3; // User not found
    }
    // 3. Permission Check
    if (!canDeleteUser($user)) {
        return 0; // Permission denied
    }
    // 4. Securely Clear User Data
    clearUserData($user);
    // 5. Log the Deletion Action
    boomConsole("delete_account", [
        "target" => $id,
        "custom" => $user["user_name"]
    ]);
    return 1; // Success
}
function staffCreateUser(){
    global $mysqli, $data, $cody;
    $name = escape($_POST["create_name"]);
    $pass = escape($_POST["create_password"]);
    $email = escape($_POST["create_email"]);
    $age = escape($_POST["create_age"]);
    $gender = escape($_POST["create_gender"]);

    if (!boomAllow(80)) {
        return 2;
    }
    if ($name == "" || $pass == "" || $email == "") {
        return 2;
    }
    if (!validName($name)) {
        return 3;
    }
    if (!boomUsername($name)) {
        return 4;
    }
    if (!isEmail($email)) {
        return 5;
    }
    if (!checkEmail($email)) {
        return 6;
    }
    if (!checkSmail($email)) {
        return 6;
    }
    if (!validAge($age)) {
        $age = 0;
    }
    if (!validGender($gender)) {
        $gender = 1;
    }
    $enpass = password_hash($pass, PASSWORD_BCRYPT);
    //$enpass = encrypt($pass);
    $system_user = ["name" => $name, "password" => $enpass, "email" => $email, "language" => $data["language"], "verified" => 1, "cookie" => 0, "gender" => $gender, "avatar" => genderAvatar($gender), "age" => $age];
    $user = boomInsertUser($system_user);
    boomConsole("create_user", ["target" => $user["user_id"]]);
    return 1;
}
?>