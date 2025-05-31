<?php
require __DIR__ . "./../config.php";
if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"]) && isset($_POST["age"]) && isset($_POST["gender"])) {
    echo userRegistration();
    exit;
}
echo 2;

function userRegistration(){
    global $mysqli, $data, $cody;
    $user_ip = getIp();
    $user_name = escape($_POST["username"]);
    $user_password = escape($_POST["password"]);
    $dlang = getLanguage();
    $user_email = escape($_POST["email"]);
    $user_gender = escape($_POST["gender"]);
    $user_age = escape($_POST["age"]);

    // Check if referrer_id exists from the form
    $referrer_id = isset($_POST['referrer_id']) ? intval(escape($_POST['referrer_id'])) : NULL;

    // Validate registration
    if (!registration()) {
        return 0; // Registration not allowed
    }
    if (!boomCheckRecaptcha()) {
        return 7; // reCAPTCHA failed
    }
    if (!validName($user_name)) {
        return 4; // Invalid username
    }
    if (!validEmail($user_email)) {
        return 6; // Invalid email format
    }
    if (!checkEmail($user_email)) {
        return 10; // Email already exists
    }
    if (!checkSmail($user_email)) {
        return 10; // Invalid email domain
    }
    if (!boomValidPassword($user_password)) {
        return 17; // Password does not meet criteria
    }
    if (!validAge($user_age)) {
        return 13; // Invalid age
    }
    if (!validGender($user_gender)) {
        return 14; // Invalid gender
    }
    if (!boomOkRegister($user_ip)) {
        return 16; // Registration not allowed from this IP
    }
    if (!boomUsername($user_name)) {
        return 5; // Username already taken
    }

    // Hash the password using password_hash() for secure storage
    $user_password = password_hash($user_password, PASSWORD_BCRYPT);

    // Prepare user data for insertion
    $system_user = [
        "name" => $user_name,
        "password" => $user_password,
        "email" => $user_email,
        "language" => $dlang,
        "gender" => $user_gender,
        "avatar" => genderAvatar($user_gender),
        "age" => $user_age,
        "verify" => $data["activation"],
        "ip" => $user_ip
    ];

    // Insert new user into the database
    $user = boomInsertUser($system_user);

    if (empty($user)) {
        return 0; // Failed to insert user
    }
    return 1; // Registration successful
}


?>