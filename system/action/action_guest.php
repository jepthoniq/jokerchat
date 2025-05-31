<?php

require __DIR__ . "./../config_session.php";

if (isset($_POST["new_guest_name"]) && isset($_POST["new_guest_password"]) && isset($_POST["new_guest_email"])) {
    echo guestRegistration();
    exit;
}
echo 99;

require __DIR__ . "./../config_session.php";

if (isset($_POST["new_guest_name"]) && isset($_POST["new_guest_password"]) && isset($_POST["new_guest_email"])) {
    echo guestRegistration();
    exit;
}
echo 99;

function guestRegistration(){
    global $mysqli, $data, $cody;

    // Check if required fields exist
    if (!isset($_POST["new_guest_name"], $_POST["new_guest_password"], $_POST["new_guest_email"])) {
        return 99;
    }

    // Sanitize and validate inputs
    $user_name = sanitizeChatInput($_POST["new_guest_name"]);
    $user_password = $_POST["new_guest_password"]; // Keep password raw for hashing
    $user_email = filter_var($_POST["new_guest_email"], FILTER_SANITIZE_EMAIL);
    $user_ip = getIp();
    // Validate user input
    if (!guestCanRegister()) {
        return 0;
    }
    if (!validName($user_name)) {
        return 4;
    }
    if (!validEmail($user_email)) {
        return 6;
    }
    if (!checkEmail($user_email) || !checkSmail($user_email)) {
        return 10;
    }
    if (!boomValidPassword($user_password)) {
        return 17;
    }
    if (!boomOkRegister($user_ip)) {
        return 16;
    }
    if (!boomUsername($user_name) && !boomSame($user_name, $data["user_name"])) {
        return 5;
    }
    // Securely hash the password with BCRYPT
    $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);
    // Assign default values if needed
    $ask = 0;
    if (defaultAvatar($data["user_tumb"])) {
        $data["user_rank"] = 1;
        resetAvatar($data);
    }
    if (strictGuest()) {
        $ask = $data["activation"];
    }
    // Process email storage
    $smail = smailProcess($user_email);
    // Use prepared statements to prevent SQL injection
    $stmt = $mysqli->prepare("UPDATE boom_users SET user_name = ?, user_password = ?, user_email = ?, user_smail = ?, user_rank = ?, user_verify = ? WHERE user_id = ?");
    if (!$stmt) {
        return 99;
    }
    $rank = 1;  // Default user rank for guests
    $stmt->bind_param("ssssiii", $user_name, $hashed_password, $user_email, $smail, $rank, $ask, $data["user_id"]);
    if ($stmt->execute()) {
        setBoomCookie($data["user_id"], $hashed_password);
        $stmt->close();
        return 1;
    }   
    $stmt->close();
    return 99;
}


?>