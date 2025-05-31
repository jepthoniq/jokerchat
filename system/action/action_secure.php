<?php


require __DIR__ . "./../config_session.php";

if (isset($_POST["actual_pass"]) && isset($_POST["new_pass"]) && isset($_POST["repeat_pass"]) && isset($_POST["change_password"])) {
    echo changeMyPassword();
    exit;
}
if (isset($_POST["save_email"]) && isset($_POST["email"]) && isset($_POST["password"])) {
    echo changeMyEmail();
    exit;
}
if (isset($_POST["delete_account_password"]) && isset($_POST["delete_my_account"])) {
    echo deleteMyAccount();
    exit;
}
if (isset($_POST["cancel_delete_account"])) {
    echo cancelDelete();
    exit;
}
if (isset($_POST["secure_name"]) && isset($_POST["secure_password"]) && isset($_POST["secure_email"])) {
    echo accountSecurity();
    exit;
}

function accountSecurity(){
    global $mysqli, $data, $cody;
    // Sanitize and validate user input
    $user_name = escape($_POST["secure_name"]);
    $user_password = $_POST["secure_password"]; // No need to escape since it's hashed
    $user_email = escape($_POST["secure_email"]);
    $user_ip = getIp();
    // Validation checks
    if (!validName($user_name)) {
        return 4; // Invalid username
    }
    if (!validEmail($user_email)) {
        return 6; // Invalid email format
    }
    if (!checkEmail($user_email) || !checkSmail($user_email)) {
        return 10; // Email already in use
    }
    if (!boomValidPassword($user_password)) {
        return 17; // Weak password
    }
    if (!boomOkRegister($user_ip)) {
        return 16; // Registration blocked for this IP
    }
    if (!boomUsername($user_name) && !boomSame($user_name, $data["user_name"])) {
        return 5; // Username already taken
    }
    // Hash the password securely
    $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);
    // Process smail (hashed version of email)
    $smail = smailProcess($user_email);
    // Update user information using a prepared statement
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_name = ?, user_password = ?, user_email = ?, user_smail = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $user_name, $hashed_password, $user_email, $smail, $data["user_id"]);
    $stmt->execute();
    $stmt->close();
    // Set session flag to disable temp password alert
    // Update session or authentication cookie
    setBoomCookie($data["user_id"], $hashed_password);
    return 1; // Success
}


function changeMyPassword() {
    global $mysqli, $data, $cody;
    // Retrieve input values
    $pass = $_POST["actual_pass"]; // No need to escape as it's hashed
    $new_pass = $_POST["new_pass"];
    $repeat_pass = $_POST["repeat_pass"];
    // Check if any field is empty
    if (empty($pass) || empty($new_pass) || empty($repeat_pass)) {
        return 2; // Missing fields
    }
    // Check if new passwords match
    if ($new_pass !== $repeat_pass) {
        return 3; // Passwords do not match
    }
    // Enforce password length requirements
    if (strlen($new_pass) < 4 || strlen($new_pass) > 30) {
        return 4; // Invalid password length
    }
    // Verify actual password using password_verify()
    if (!password_verify($pass, $data["user_password"]) && $pass !== $data["temp_pass"]) {
        return 5; // Incorrect current password
    }
    // Hash new password securely
    $new_hashed_pass = password_hash($new_pass, PASSWORD_BCRYPT);
    // Update password using a prepared statement
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_password = ?, temp_pass = '0' WHERE user_id = ?");
    $stmt->bind_param("si", $new_hashed_pass, $data["user_id"]);
    $stmt->execute();
    $stmt->close();
    // Update authentication cookie
    setBoomCookie($data["user_id"], $new_hashed_pass);
    return 1; // Password changed successfully
}
function changeMyEmail() {
    global $mysqli, $data, $cody;
    // Retrieve input values
    $email = trim($_POST["email"]); // Trim to remove unwanted spaces
    $password = $_POST["password"]; // No need to escape, it will be verified
    // Check if user must verify before making changes
    if (mustVerify()) {
        return "";
    }
    // Ensure user is a member
    if (!isMember($data)) {
        return "";
    }
    // Verify password using password_verify()
    if (!password_verify($password, $data["user_password"])) {
        return 3; // Incorrect password
    }
    // Validate new email format
    if (!validEmail($email)) {
        return 2; // Invalid email format
    }
    // Check if email is already in use
    if (!checkEmail($email) && !boomSame($email, $data["user_email"])) {
        return 4; // Email already exists
    }
    // Process secure email format (if needed)
    $smail = smailProcess($email);
    // Update email using a prepared statement
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_email = ?, user_smail = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $email, $smail, $data["user_id"]);
    $stmt->execute();
    $stmt->close();
    return 1; // Email updated successfully
}
function deleteMyAccount() {
    global $mysqli, $data, $cody;
    // Retrieve input password
    $pass = trim($_POST["delete_account_password"]);
    // Calculate the delay for account deletion (7 days)
    $delay = calDayUp(7);
    // Prevent deletion for system admins or bots
    if (boomAllow(100) || isBot($data)) {
        return "";
    }
    // Verify user's password
    if (!password_verify($pass, $data["user_password"])) {
        return 2; // Incorrect password
    }
    // Prepare statement to securely update account deletion status
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_delete = ? WHERE user_id = ? AND user_delete = 0");
    $stmt->bind_param("ii", $delay, $data["user_id"]);
    $stmt->execute();
    $stmt->close();
    return 1; // Account deletion scheduled
}
function cancelDelete() {
    global $mysqli, $data, $cody;
    // Prepare statement to securely update user_delete
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_delete = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $data["user_id"]);
    $stmt->execute();
    $stmt->close();
    return 1; // Successfully canceled account deletion
}


?>